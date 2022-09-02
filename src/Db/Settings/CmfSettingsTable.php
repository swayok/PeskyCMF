<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableStructureInterface;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueTableHelpers;
use PeskyORMLaravel\Db\LaravelKeyValueTableHelpers\LaravelKeyValueTableInterface;

class CmfSettingsTable extends CmfDbTable implements LaravelKeyValueTableInterface
{
    
    use LaravelKeyValueTableHelpers;
    
    public function getMainForeignKeyColumnName(): ?string
    {
        return null;
    }
    
    public static function getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue = null): ?string
    {
        return 'app-settings';
    }
    
    public function getTableAlias(): string
    {
        return 'CmfSettings';
    }
    
    /**
     * @return CmfSettingsTableStructure
     */
    public function getTableStructure(): TableStructureInterface
    {
        return app(CmfSettingsTableStructure::class);
    }
    
    /**
     * @return CmfSetting
     */
    public function newRecord(): RecordInterface
    {
        return app(CmfSetting::class);
    }
    
}
