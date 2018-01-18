<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\CmfDbTable;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableHelpers;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface;

class CmfSettingsTable extends CmfDbTable implements KeyValueTableInterface {

    use KeyValueTableHelpers;

    /** @var CmfSettingsTableStructure */
    static private $tableStructure;
    /** @var CmfSetting */
    static private $recordClass;

    public function getMainForeignKeyColumnName() {
        return null;
    }

    /**
     * @param null $foreignKeyValue
     * @return null|string
     */
    static public function getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue = null) {
        return 'app-settings';
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmfSettings';
    }

    /**
     * @return CmfSettingsTableStructure
     */
    public function getTableStructure() {
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
    public function newRecord() {
        if (static::$recordClass === null) {
            static::$recordClass = app()->bound(CmfSetting::class)
                ? app(CmfSetting::class)
                : CmfSetting::class;
        }
        return static::$recordClass::newEmptyRecord();
    }

}
