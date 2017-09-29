<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\CmfDbTable;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableHelpers;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface;

class CmfSettingsTable extends CmfDbTable implements KeyValueTableInterface {

    use KeyValueTableHelpers;

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
        static $tableStructure;
        if ($tableStructure === null) {
            $tableStructure = app()->bound(CmfSettingsTableStructure::class)
                ? app(CmfSettingsTableStructure::class)
                : CmfSettingsTableStructure::getInstance();
        }
        return $tableStructure;
    }

    /**
     * @return CmfSetting
     */
    public function newRecord() {
        /** @var CmfSetting $recordClass */
        static $recordClass;
        if ($recordClass === null) {
            $recordClass = app()->bound(CmfSetting::class) ? app(CmfSetting::class) : CmfSetting::class;
        }
        return $recordClass::newEmptyRecord();
    }

}
