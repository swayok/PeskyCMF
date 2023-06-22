<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyORM\ORM\TableStructure\TableColumn\Column\IdColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\MixedJsonColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\StringColumn;
use PeskyORM\ORM\TableStructure\TableStructure;

class CmfSettingsTableStructure extends TableStructure
{
    public function getTableName(): string
    {
        return 'settings';
    }

    protected function registerColumns(): void
    {
        $this->addColumn(new IdColumn());
        $this->addColumn(
            (new StringColumn('key'))
                ->convertsEmptyStringValuesToNull()
                ->uniqueValues()
        );
        $this->addColumn(
            (new MixedJsonColumn('value'))
                ->allowsNullValues()
        );
    }

    protected function registerRelations(): void
    {
    }
}
