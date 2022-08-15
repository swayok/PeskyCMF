<?php

namespace PeskyCMF\Db\Settings;

use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueRecordHelpers;

/**
 * @property-read int         $id
 * @property-read string      $key
 * @property-read string      $value
 * @property-read CmfAdmin    $Admin
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setKey($value, $isFromDb = false)
 * @method $this    setValue($value, $isFromDb = false)
 *
 */
class CmfSetting extends CmfDbRecord {

    use KeyValueRecordHelpers;

    protected static $tableClass;

    /** @var CmfSettingsTable */
    private static $table;

    /**
     * @return CmfSettingsTable
     */
    public static function getTable() {
        if (static::$table === null) {
            static::$table = app()->bound(CmfSettingsTable::class)
                ? app(CmfSettingsTable::class)
                : CmfSettingsTable::getInstance();
        }
        return static::$table;
    }

}