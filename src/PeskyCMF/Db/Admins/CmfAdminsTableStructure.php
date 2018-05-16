<?php

namespace PeskyCMF\Db\Admins;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;
use PeskyORMLaravel\Db\TableStructureTraits\IsActiveColumn;
use PeskyORMLaravel\Db\TableStructureTraits\TimestampColumns;
use PeskyORMLaravel\Db\TableStructureTraits\UserAuthColumns;

/**
 * @property-read Column    $id
 * @property-read Column    $parent_id
 * @property-read Column    $name
 * @property-read Column    $email
 * @property-read Column    $password
 * @property-read Column    $ip
 * @property-read Column    $is_superadmin
 * @property-read Column    $is_active
 * @property-read Column    $role
 * @property-read Column    $language
 * @property-read Column    $created_at
 * @property-read Column    $updated_at
 * @property-read Column    $timezone
 * @property-read Column    $remember_token
 */
class CmfAdminsTableStructure extends CmfDbTableStructure {

    use IdColumn,
        TimestampColumns,
        IsActiveColumn,
        UserAuthColumns
        ;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'admins';
    }

    private function parent_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function email() {
        $column = Column::create(Column::TYPE_EMAIL)
            ->convertsEmptyStringToNull()
            ->trimsValue()
            ->lowercasesValue()
            ->uniqueValues();
        if ($this->getCmfConfig()->getAuthModule()->getUserLoginColumnName() === 'email') {
            $column->disallowsNullValues();
        }
        return $column;
    }

    private function login() {
        $column = Column::create(Column::TYPE_STRING)
            ->convertsEmptyStringToNull()
            ->trimsValue()
            ->lowercasesValue()
            ->uniqueValues();
        if ($this->getCmfConfig()->getAuthModule()->getUserLoginColumnName() === 'login') {
            $column->disallowsNullValues();
        }
        return $column;
    }

    private function name() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function ip() {
        return Column::create(Column::TYPE_IPV4_ADDRESS)
            ->convertsEmptyStringToNull();
    }

    private function is_superadmin() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(false);
    }

    private function role() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue($this->getCmfConfig()->getAuthModule()->getDefaultUserRole());
    }

    private function language() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue($this->getCmfConfig()->default_locale());
    }

    private function timezone() {
        return Column::create(Column::TYPE_STRING)
            ->convertsEmptyStringToNull();
    }

    private function ParentAdmin() {
        return Relation::create(
                'parent_id',
                Relation::BELONGS_TO,
                $this->getCmfConfig()->getAuthModule()->getUsersTable(),
                'id'
            )
            ->setDisplayColumnName($this->getCmfConfig()->getAuthModule()->getUserLoginColumnName());
    }

    public function getCmfConfig() {
        return CmfConfig::getDefault();
    }

}