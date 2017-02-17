<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\CMS\CmsTable;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Db\Traits\KeyValueTableHelpers;

class CmsSettingsTable extends CmsTable implements KeyValueTableInterface {

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
     * @return CmsSettingsTableStructure
     */
    public function getTableStructure() {
        return CmsSettingsTableStructure::getInstance();
    }

    /**
     * @return CmsSetting
     */
    public function newRecord() {
        return new CmsSetting();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsSettings';
    }

}
