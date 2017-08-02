<?php

namespace PeskyCMF\Config;

use Illuminate\Auth\Access\HandlesAuthorization;
use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyORM\ORM\RecordInterface;

class CmfAccessPolicy {

    use HandlesAuthorization;

    /**
     * Access to CMF resources
     * Format: [
            'resource_name' => [
                'ability1' => ['role1', 'role2', ...],
                'ability2' => ['role1'],
                'ability3' => true,
                'others' => ['role2']
            ],
            ...
        ]
     * 'others' means 'all not listed abilitites'.
     * To allow access for all roles use 'true' intead of roles array.
     * @var array
     */
    static protected $resources = [
        'cmf_profile' => [
            'others' => true
        ],
    ];

    /**
     * List of CMF resources that require user to be owner in order to access certain item of the resource
     * Format: [
            'resource_name' => [
                'details' => ['role1', 'role2', 'role3'],
                'create' => ['role1', 'role2', ...],
                'update' => true,
                'delete' => ['role2']
            ],
        ]
     * Abilities affected by this setting: 'details', 'create', 'update', 'delete'.
     * If any of this abilities is absent - access is alowed for any role.
     * To check ownersip for all roles use 'true' intead of roles array.
     * Note that if resource does not have "owner id column name" - access to its items will be allowed to any role.
     * @var array
     */
    static protected $resourcesWithOwnershipValidation = [

    ];

    /**
     * list of "owner id column names" for resources that store owner id not in default column name.
     * Default column name is configured via static::$defaultOwnerIdColumnName property.
     * Format: ['resource_name' => 'column_name']
     * Example: [
            'posts' => 'user_id'
        ]
     * @var array
     */
    static protected $ownerColumnForTable = [

    ];

    /**
     * Default "owner id column name"
     * @var string
     */
    static protected $defaultOwnerIdColumnName = 'admin_id';

    /**
     * Access to CMF pages: route('cmf_page', ['page' => 'page_name'])
     * Format: [
            'page_name' => ['role1', 'role2', ...]
            'page_name2' => true
        ]
     * To allow access for all roles use 'true' intead of roles array.
     * @var array
     */
    static protected $cmfPages = [

    ];

    /**
     * Default access rules for abilities. Used when there is no rules for resource or part of its abilities
     * and 'others' rule is not provided for resource
     * Format: [
            'ability1' => ['role1', 'role2', ...],
            'others' => true
        ]
     * To allow access for all roles use 'true' intead of roles array.
     * @var array
     */
    static protected $defaults = [
        'others' => true,
    ];

    /**
     * @param CmsAdmin $user
     * @param string $pageName
     * @return bool
     */
    public function cmf_page($user, $pageName) {
        if ($user->is_superadmin) {
            return true;
        }
        if (array_key_exists($pageName, static::$cmfPages)) {
            return static::$cmfPages[$pageName] === true || in_array($user->role, (array)static::$cmfPages[$pageName], true);
        }
        return $this->getAccessFromDefaults($user->role, 'cmf_page');
    }

    /**
     * @param CmsAdmin $user
     * @param string $ability
     * @param string $table
     * @param mixed|RecordInterface|null $recordOrItemIdOrFkValue
     * @param array $conditions
     * @return bool
     */
    protected function resource($user, $ability, $table, $recordOrItemIdOrFkValue = null, array $conditions = []) {
        if ($user->is_superadmin) {
            return true;
        }
        return (
            $this->roleHasAccessToResource($user->role, $table, $ability)
            && $this->userHasAccessToRecord($user, $table, $ability, $recordOrItemIdOrFkValue, $conditions)
        );
    }

    protected function roleHasAccessToResource($role, $table, $ability) {
        if (array_key_exists($table, static::$resources)) {
            if (array_key_exists($ability, static::$resources[$table])) {
                return static::$resources[$table][$ability] === true || in_array($role, (array)static::$resources[$table][$ability], true);
            } else if (array_key_exists('others', static::$resources[$table])) {
                return static::$resources[$table]['others'] === true || in_array($role, (array)static::$resources[$table]['others'], true);
            }
        }
        return $this->getAccessFromDefaults($role, $ability);
    }

    /**
     * @param CmsAdmin $user
     * @param string $tableName
     * @param string $ability
     * @param mixed|RecordInterface|null $recordOrItemIdOrFkValue
     * @param array $conditions
     * @return bool
     */
    protected function userHasAccessToRecord($user, $tableName, $ability, $recordOrItemIdOrFkValue = null, array $conditions = []) {
        if (
            array_key_exists($tableName, static::$resourcesWithOwnershipValidation)
            && array_key_exists($ability, static::$resourcesWithOwnershipValidation[$tableName])
            && (
                static::$resourcesWithOwnershipValidation[$tableName][$ability] === true
                || in_array($user->role, (array)static::$resourcesWithOwnershipValidation[$tableName][$ability], true)
            )
        ) {
            $table = CmfConfig::getDefault()->getTableByUnderscoredName($tableName);
            $ownerColumn = array_get(static::$ownerColumnForTable, $tableName, 'admin_id');
            if (!$table->getTableStructure()->hasColumn($ownerColumn)) {
                return true;
            }
            if ($recordOrItemIdOrFkValue === null) {
                // bulk action
                return $table::count($conditions) === $table::count(array_merge($conditions, [$ownerColumn => $user->id]));
            } else if ($recordOrItemIdOrFkValue instanceof RecordInterface || is_array($recordOrItemIdOrFkValue)) {
                return $recordOrItemIdOrFkValue[$ownerColumn] === $user->id;
            } else {
                if ($table instanceof KeyValueTableInterface) {
                    $fkColName = $table->getMainForeignKeyColumnName();
                    if ($fkColName === null) {
                        return true;
                    } else if (empty($recordOrItemIdOrFkValue)) {
                        return false;
                    } else {
                        return $table::count([$fkColName => $recordOrItemIdOrFkValue]) ===
                                    $table::count([$fkColName => $recordOrItemIdOrFkValue, $ownerColumn => $user->id]);

                    }
                } else {
                    return $table::count([$table::getPkColumnName() => $recordOrItemIdOrFkValue, $ownerColumn => $user->id]) === 1;
                }
            }
        }
        return true;
    }

    /**
     * @param string $role
     * @param string $ability
     * @return bool
     */
    protected function getAccessFromDefaults($role, $ability) {
        if (array_key_exists($ability, static::$defaults)) {
            return static::$defaults[$ability] === true || in_array($role, (array)static::$defaults[$ability], true);
        } else if (array_key_exists('others', static::$defaults)) {
            return static::$defaults['others'] === true || in_array($role, (array)static::$defaults['others'], true);
        } else {
            return false;
        }
    }

    public function view($user, $table) {
        return $this->resource($user, 'view', $table);
    }

    public function details($user, $table, $recordOrItemIdOrFkValue = null) {
        return $this->resource($user, 'details', $table, $recordOrItemIdOrFkValue);
    }

    public function create($user, $table) {
        return $this->resource($user, 'create', $table);
    }

    public function update($user, $table, $recordOrItemIdOrFkValue = null) {
        return $this->resource($user, 'update', $table, $recordOrItemIdOrFkValue);
    }

    public function edit($user, $table, $recordOrItemIdOrFkValue = null) {
        return $this->resource($user, 'update', $table, $recordOrItemIdOrFkValue);
    }

    public function delete($user, $table, $recordOrItemIdOrFkValue = null) {
        return $this->resource($user, 'delete', $table, $recordOrItemIdOrFkValue);
    }

    public function update_bulk($user, $table, array $conditions = []) {
        return $this->resource($user, 'update_bulk', $table, null, $conditions);
    }

    public function delete_bulk($user, $table, array $conditions = []) {
        return $this->resource($user, 'delete_bulk', $table, null, $conditions);
    }

    public function others($user, $table, $ability, $recordOrItemIdOrFkValue = null) {
        return $this->resource($user, $ability, $table, $recordOrItemIdOrFkValue);
    }
}