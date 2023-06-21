<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyORM\ORM\Table\Table;

class CmfSettingsTable extends Table
{
    public function __construct(?string $tableAlias = 'CmfSettings')
    {
        parent::__construct(new CmfSettingsTableStructure(), CmfSetting::class, $tableAlias);
    }

    public function getMainForeignKeyColumnName(): ?string
    {
        return null;
    }

    public static function getCacheKeyToStoreAllValuesForAForeignKey(): string
    {
        return 'app-settings';
    }
}
