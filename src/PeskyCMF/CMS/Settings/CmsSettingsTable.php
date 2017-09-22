<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\CMS\CmsTable;
use PeskyORMLaravel\Db\KeyValueTableHelpers;
use PeskyORMLaravel\Db\KeyValueTableInterface;

/**
 * @method CmsSettingsTableStructure getTableStructure()
 * @method CmsSetting newRecord()
 */
class CmsSettingsTable extends CmsTable implements KeyValueTableInterface {

    use KeyValueTableHelpers;

    static protected $tableStructureClass = CmsSettingsTableStructure::class;
    static protected $recordClass = CmsSetting::class;

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
        return 'CmsSettings';
    }

}
