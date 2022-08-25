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
    
    /** @var CmfSettingsTableStructure */
    private static TableStructureInterface $tableStructure;
    /** @var CmfSetting */
    private static RecordInterface $recordClass;
    
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
        if (static::$tableStructure === null) {
            static::$tableStructure = app()->bound(CmfSettingsTableStructure::class)
                ? app(CmfSettingsTableStructure::class)
                : CmfSettingsTableStructure::getInstance();
        }
        return static::$tableStructure;
    }
    
    /**
     * @return CmfSetting
     */
    public function newRecord(): RecordInterface
    {
        if (static::$recordClass === null) {
            static::$recordClass = app()->bound(CmfSetting::class)
                ? app(CmfSetting::class)
                : CmfSetting::class;
        }
        return static::$recordClass::newEmptyRecord();
    }
    
}
