<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Admins;

use PeskyCMF\CmfManager;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\TableStructure\Relation;
use PeskyORM\ORM\TableStructure\TableColumn\Column\BooleanColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\CreatedAtColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\EmailColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IdColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IntegerColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IpV4AddressColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\PasswordColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\StringColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\UpdatedAtColumn;
use PeskyORM\ORM\TableStructure\TableStructure;
use PeskyORMColumns\TableColumn\IsActiveColumn;
use PeskyORMColumns\TableColumn\RememberTokenColumn;

class CmfAdminsTableStructure extends TableStructure
{
    public function getTableName(): string
    {
        return 'admins';
    }

    public function getCmfConfig(): CmfConfig
    {
        return app(CmfManager::class)->getCurrentCmfConfig();
    }

    protected function registerColumns(): void
    {
        $cmfConfig = $this->getCmfConfig();

        $this->addColumn(new IdColumn());
        $this->addColumn(new EmailColumn());
        $this->addColumn(new IntegerColumn('parent_id'));

        $loginColumn = new StringColumn('login');
        $loginColumn->convertsEmptyStringValuesToNull()
            ->trimsValues()
            ->lowercasesValues()
            ->uniqueValues();
        if ($cmfConfig->getAuthModule()->getUserLoginColumnName() !== 'login') {
            $loginColumn->allowsNullValues();
        }
        $this->addColumn($loginColumn);
        $this->addColumn(new PasswordColumn());
        $this->addColumn(new RememberTokenColumn());

        $this->addColumn(
            (new StringColumn('name'))
                ->setDefaultValue('')
        );
        $this->addColumn(
            (new StringColumn('role'))
                ->convertsEmptyStringValuesToNull()
                ->setDefaultValue($cmfConfig->getAuthModule()->getDefaultUserRole())
        );

        $this->addColumn(
            (new StringColumn('language'))
                ->convertsEmptyStringValuesToNull()
                ->setDefaultValue($cmfConfig->defaultLocale())
        );

        $this->addColumn(
            (new StringColumn('timezone'))
                ->convertsEmptyStringValuesToNull()
        );

        $this->addColumn(new IpV4AddressColumn('ip'));
        $this->addColumn(
            (new BooleanColumn('is_superadmin'))
                ->setDefaultValue(false)
        );
        $this->addColumn(new IsActiveColumn());
        $this->addColumn(new CreatedAtColumn());
        $this->addColumn(new UpdatedAtColumn());
    }

    protected function registerRelations(): void
    {
        $this->addRelation(
            (new Relation(
                'parent_id',
                Relation::BELONGS_TO,
                // Recursion when $this->getCmfConfig()->getAuthModule()->getUsersTable()::class used
                CmfAdminsTable::class,
                'id',
                'ParentAdmin'
            ))
                ->setDisplayColumnName(function () {
                    return $this->getCmfConfig()->getAuthModule()->getUserLoginColumnName();
                })
        );
    }
}
