<?php

declare(strict_types=1);

namespace PeskyCMF\Auth;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\Record\RecordInterface;

class CmfAccessPolicy
{
    use HandlesAuthorization;

    /**
     * Access to CMF resources
     * Format: [
     *    'resource_name' => [
     *        'view' => true, //< allowed for everyone
     *        'details' => true, //< allowed for everyone
     *        'create' => ['role1', 'role2'],
     *        'update' => ['role1', 'role2'],
     *        'delete' => false, //< allowed only for user with is_superadmin = true
     *        'update_bulk' => ['role1', 'role2'],
     *        'delete_bulk' => ['role1', 'role2'],
     *        // Pages accesed by routeToCmfResourceCustomPage('resource_name', 'custom_page_name')
     *        'page:custom_page_name' => [...],
     *        // Pages accesed by routeToCmfResourceCustomAction('resource_name', 'custom_action_name')
     *        'action:custom_action_name' => [...],
     *        // Pages accesed by routeToCmfItemCustomPage('resource_name', $pkValue, 'custom_page_name')
     *        'item_page:custom_page_name' => [...],
     *        // Pages accesed by routeToCmfItemCustomAction('resource_name', $pkValue, 'custom_action_name')
     *        'item_action:custom_action_name' => [...],
     *        'others' => [...]
     *    ],
     *    ...
     * ]
     * 'others' means 'all not listed abilities'.
     * To allow access for all roles use 'true' instead of roles array.
     * @var array
     */
    protected static array $resources = [
        'buildings' => [
            'others' => true,
        ],
    ];

    /**
     * List of CMF resources that require user to be owner in order to access
     * certain item of the resource.
     * Format: [
     *        'resource_name' => [
     *        'details' => ['role1', 'role2', 'role3'],
     *        'create' => ['role1', 'role2', ...],
     *        'update' => true,
     *        'delete' => ['role2']
     *    ],
     * ]
     * Abilities affected by this setting: 'details', 'create', 'update', 'delete'.
     * If any of these abilities is absent - access is allowed for any role.
     * To check ownership for all roles use 'true' instead of roles array.
     * Note that if resource does not have "owner id column name" - access
     * to its items will be allowed to any role.
     * @var array
     */
    protected static array $resourcesWithOwnershipValidation = [

    ];

    /**
     * list of "owner id column names" for resources that store owner id
     * not in default column name.
     * Default column name is configured via static::$defaultOwnerIdColumnName property.
     * Format: ['resource_name' => 'column_name']
     * Example: [
     *    'posts' => 'user_id'
     * ]
     * @var array
     */
    protected static array $ownerColumnForTable = [

    ];

    /**
     * Default "owner id column name"
     * @var string
     */
    protected static string $defaultOwnerIdColumnName = 'admin_id';

    /**
     * Access to CMF pages: routeToCmfPage('page_name')
     * Format: [
     *    'page_name' => ['role1', 'role2', ...],
     *    'page_name2' => true,   //< allowed for everyone
     *    'page_name3' => false,  //< allowed only for user with is_superadmin = true
     * ]
     * To allow access for all roles use 'true' instead of roles array.
     * @var array
     */
    protected static array $cmfPages = [
        'login_as' => false,
    ];

    /**
     * Default access rules for abilities. Used when there is no rules
     * for resource or part of its abilities.
     * and 'others' rule is not provided for resource
     * Format: [
     *    'ability1' => ['role1', 'role2', ...],
     *    'others' => true
     * ]
     * To allow access for all roles use 'true' instead of roles array.
     * @var array
     */
    protected static array $defaults = [
        'others' => true,
    ];

    protected CmfConfig $cmfConfig;

    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
    }

    protected function isSuperadmin(RecordInterface $user): bool
    {
        if ($user->hasColumn('is_superadmin')) {
            return $user->getValue('is_superadmin');
        }
        return false;
    }

    protected function getUserRole(RecordInterface $user): ?string
    {
        return $user->hasColumn('role') ? $user->getValue('role') : 'admin';
    }

    protected function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }

    public function cmfPage(RecordInterface $user, string $pageName): bool
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }
        if (array_key_exists($pageName, static::$cmfPages)) {
            return (
                static::$cmfPages[$pageName] === true
                || in_array(
                    $this->getUserRole($user),
                    (array)static::$cmfPages[$pageName],
                    true
                )
            );
        }
        return $this->getAccessFromDefaults($this->getUserRole($user), 'cmf_page');
    }

    /**
     * $conditions used only for bulk actions when $recordOrItemIdOrFkValue === null
     */
    protected function resource(
        RecordInterface $user,
        string $ability,
        string $resourceName,
        RecordInterface|array|float|int|string $recordOrItemIdOrFkValue = null,
        array $conditions = []
    ): bool {
        if ($this->isSuperadmin($user)) {
            return true;
        }
        return (
            $this->roleHasAccessToResource(
                $this->getUserRole($user),
                $resourceName,
                $ability
            )
            && $this->userHasAccessToRecord(
                $user,
                $resourceName,
                $ability,
                $recordOrItemIdOrFkValue,
                $conditions
            )
        );
    }

    protected function roleHasAccessToResource(
        string $role,
        string $resourceName,
        string $ability
    ): bool {
        if (array_key_exists($resourceName, static::$resources)) {
            if (array_key_exists($ability, static::$resources[$resourceName])) {
                return (
                    static::$resources[$resourceName][$ability] === true
                    || in_array(
                        $role,
                        (array)static::$resources[$resourceName][$ability],
                        true
                    )
                );
            }
            if (array_key_exists('others', static::$resources[$resourceName])) {
                return (
                    static::$resources[$resourceName]['others'] === true
                    || in_array(
                        $role,
                        (array)static::$resources[$resourceName]['others'],
                        true
                    )
                );
            }
        }
        return $this->getAccessFromDefaults($role, $ability);
    }

    /**
     * $conditions used only for bulk actions when $recordOrItemIdOrFkValue === null
     */
    protected function userHasAccessToRecord(
        RecordInterface $user,
        string $resourceName,
        string $ability,
        RecordInterface|array|float|int|string $recordOrItemIdOrFkValue = null,
        array $conditions = []
    ): bool {
        if (
            array_key_exists($resourceName, static::$resourcesWithOwnershipValidation)
            && array_key_exists(
                $ability,
                static::$resourcesWithOwnershipValidation[$resourceName]
            )
            && (
                static::$resourcesWithOwnershipValidation[$resourceName][$ability] === true
                || in_array(
                    $this->getUserRole($user),
                    (array)static::$resourcesWithOwnershipValidation[$resourceName][$ability],
                    true
                )
            )
        ) {
            $table = $this->getCmfConfig()->getTableByResourceName($resourceName);
            $ownerColumn = Arr::get(
                static::$ownerColumnForTable,
                $resourceName,
                static::$defaultOwnerIdColumnName
            );
            if (!$table->getTableStructure()->hasColumn($ownerColumn)) {
                return true;
            }
            if ($recordOrItemIdOrFkValue === null) {
                // bulk action
                $countConditions = array_merge(
                    $conditions,
                    [$ownerColumn => $user->getPrimaryKeyValue()]
                );
                return $table::count($conditions) === $table::count($countConditions);
            }

            if (
                $recordOrItemIdOrFkValue instanceof RecordInterface
                || is_array($recordOrItemIdOrFkValue)
            ) {
                return $recordOrItemIdOrFkValue[$ownerColumn] === $user->getPrimaryKeyValue();
            }

            $countConditions = [
                $table::getPkColumnName() => $recordOrItemIdOrFkValue,
                $ownerColumn => $user->getPrimaryKeyValue(),
            ];
            return $table::count($countConditions) === 1;
        }
        return true;
    }

    protected function getAccessFromDefaults(string $role, string $ability): bool
    {
        if (array_key_exists($ability, static::$defaults)) {
            return (
                static::$defaults[$ability] === true
                || in_array($role, (array)static::$defaults[$ability], true)
            );
        }
        if (array_key_exists('others', static::$defaults)) {
            return (
                static::$defaults['others'] === true
                || in_array($role, (array)static::$defaults['others'], true)
            );
        }
        return false;
    }

    public function view(RecordInterface $user, string $resourceName): bool
    {
        return $this->resource($user, 'view', $resourceName);
    }

    public function details(
        RecordInterface $user,
        string $resourceName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return $this->resource($user, 'details', $resourceName, $recordOrItemIdOrFkValue);
    }

    public function create(RecordInterface $user, string $resourceName): bool
    {
        return $this->resource($user, 'create', $resourceName);
    }

    public function update(
        RecordInterface $user,
        string $resourceName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return $this->resource($user, 'update', $resourceName, $recordOrItemIdOrFkValue);
    }

    public function edit(
        RecordInterface $user,
        string $resourceName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return $this->resource($user, 'update', $resourceName, $recordOrItemIdOrFkValue);
    }

    public function delete(
        RecordInterface $user,
        string $resourceName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return $this->resource($user, 'delete', $resourceName, $recordOrItemIdOrFkValue);
    }

    public function updateBulk(
        RecordInterface $user,
        string $resourceName,
        array $conditions = []
    ): bool {
        return $this->resource($user, 'update_bulk', $resourceName, null, $conditions);
    }

    public function deleteBulk(
        RecordInterface $user,
        string $resourceName,
        array $conditions = []
    ): bool {
        return $this->resource($user, 'delete_bulk', $resourceName, null, $conditions);
    }

    public function others(
        RecordInterface $user,
        string $resourceName,
        string $ability,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return $this->resource($user, $ability, $resourceName, $recordOrItemIdOrFkValue);
    }

    public function customPage(
        RecordInterface $user,
        string $resourceName,
        string $pageName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return (
            $this->resource($user, 'view', $resourceName, $recordOrItemIdOrFkValue)
            && $this->resource($user, 'page:' . $pageName, $resourceName, $recordOrItemIdOrFkValue)
        );
    }

    public function customAction(
        RecordInterface $user,
        string $resourceName,
        string $pageName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return (
            $this->resource($user, 'view', $resourceName, $recordOrItemIdOrFkValue)
            && $this->resource($user, 'action:' . $pageName, $resourceName, $recordOrItemIdOrFkValue)
        );
    }

    public function customPageForItem(
        RecordInterface $user,
        string $resourceName,
        string $pageName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return (
            $this->resource($user, 'details', $resourceName, $recordOrItemIdOrFkValue)
            && $this->resource($user, 'item_page:' . $pageName, $resourceName, $recordOrItemIdOrFkValue)
        );
    }

    public function customActionForItem(
        RecordInterface $user,
        string $resourceName,
        string $actionName,
        $recordOrItemIdOrFkValue = null
    ): bool {
        return (
            $this->resource($user, 'details', $resourceName, $recordOrItemIdOrFkValue)
            && $this->resource($user, 'item_action:' . $actionName, $resourceName, $recordOrItemIdOrFkValue)
        );
    }
}
