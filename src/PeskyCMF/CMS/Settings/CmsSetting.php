<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\CmsRecord;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueRecordHelpers;

/**
 * @property-read int         $id
 * @property-read string      $key
 * @property-read string      $value
 * @property-read CmsAdmin    $Admin
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setKey($value, $isFromDb = false)
 * @method $this    setValue($value, $isFromDb = false)
 *
 * @method static CmsSettingsTable getTable()
 */
class CmsSetting extends CmsRecord {

    use KeyValueRecordHelpers;

    static protected $tableClass = CmsSettingsTable::class;

}