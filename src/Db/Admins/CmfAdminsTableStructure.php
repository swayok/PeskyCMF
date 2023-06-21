<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyCMF\CmfManager;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\TableStructure\TableStructure;

class CmfAdminsTableStructure extends TableStructure
{
    use IdColumn;
    use TimestampColumns;
    use IsActiveColumn;
    use UserAuthColumns;

    public function getTableName(): string
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
            ->setDefaultValue($this->getCmfConfig()->defaultLocale());
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
            ->setDisplayColumnName(function () {
                return $this->getCmfConfig()->getAuthModule()->getUserLoginColumnName();
            });
    }

    public function getCmfConfig(): CmfConfig
    {
        return app(CmfManager::class)->getCurrentCmfConfig();
    }

    protected function registerColumns(): void
    {
        // TODO: Implement registerColumns() method.
    }

    protected function registerRelations(): void
    {
        // TODO: Implement registerRelations() method.
    }
}
