<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\CMS\CmsTableStructure;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsActiveColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyCMF\Db\Traits\UserAuthColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

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
class CmsAdminsTableStructure extends CmsTableStructure {

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
            ->uniqueValues();
        if (static::getCmsConfig()->user_login_column() === 'email') {
            $column->disallowsNullValues();
        }
        return $column;
    }

    private function login() {
        $column = Column::create(Column::TYPE_EMAIL)
            ->convertsEmptyStringToNull()
            ->trimsValue()
            ->uniqueValues();
        if (static::getCmsConfig()->user_login_column() === 'login') {
            $column->disallowsNullValues();
        }
        return $column;
    }

    private function name() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
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
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(static::getCmsConfig()->roles_list())
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(static::getCmsConfig()->default_role());
    }

    private function language() {
        return Column::create(Column::TYPE_ENUM)
            ->setAllowedValues(static::getCmsConfig()->locales())
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(static::getCmsConfig()->default_locale());
    }

    private function timezone() {
        return Column::create(Column::TYPE_STRING)
            ->convertsEmptyStringToNull();
    }

    private function ParentAdmin() {
        return Relation::create(
                'parent_id',
                Relation::BELONGS_TO,
                CmsAdminsTable::class,
                'id'
            )
            ->setDisplayColumnName(static::getCmsConfig()->user_login_column());
    }

}