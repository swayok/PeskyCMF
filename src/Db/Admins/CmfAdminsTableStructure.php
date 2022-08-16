<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORMColumns\TableStructureTraits\IdColumn;
use PeskyORMColumns\TableStructureTraits\IsActiveColumn;
use PeskyORMColumns\TableStructureTraits\TimestampColumns;
use PeskyORMColumns\TableStructureTraits\UserAuthColumns;

/**
 * @property-read Column $id
 * @property-read Column $parent_id
 * @property-read Column $name
 * @property-read Column $email
 * @property-read Column $password
 * @property-read Column $ip
 * @property-read Column $is_superadmin
 * @property-read Column $is_active
 * @property-read Column $role
 * @property-read Column $language
 * @property-read Column $created_at
 * @property-read Column $updated_at
 * @property-read Column $timezone
 * @property-read Column $remember_token
 */
class CmfAdminsTableStructure extends CmfDbTableStructure
{
    
    use IdColumn;
    use TimestampColumns;
    use IsActiveColumn;
    use UserAuthColumns;
    
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'admins';
    }
    
    private function parent_id(): Column
    {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }
    
    private function email(): Column
    {
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
    
    private function login(): Column
    {
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
    
    private function name(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }
    
    private function ip(): Column
    {
        return Column::create(Column::TYPE_IPV4_ADDRESS)
            ->convertsEmptyStringToNull();
    }
    
    private function is_superadmin(): Column
    {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue(false);
    }
    
    private function role(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue($this->getCmfConfig()->getAuthModule()->getDefaultUserRole());
    }
    
    private function language(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue($this->getCmfConfig()->default_locale());
    }
    
    private function timezone(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->convertsEmptyStringToNull();
    }
    
    private function ParentAdmin(): Relation
    {
        return Relation::create(
            'parent_id',
            Relation::BELONGS_TO,
            $this->getCmfConfig()->getAuthModule()->getUsersTable(),
            'id'
        )
            ->setDisplayColumnName($this->getCmfConfig()->getAuthModule()->getUserLoginColumnName());
    }
    
    public function getCmfConfig(): CmfConfig
    {
        return CmfConfig::getDefault();
    }
    
}