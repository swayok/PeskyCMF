<?php

namespace PeskyCMF\CMS\Settings;

use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\CmsRecord;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Db\Traits\KeyValueRecordHelpers;

/**
 * @property-read int         $id
 * @property-read string      $key
 * @property-read null|int    $admin_id
 * @property-read string      $value
 * @property-read CmsAdmin    $Admin
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setKey($value, $isFromDb = false)
 * @method $this    setAdminId($value, $isFromDb = false)
 * @method $this    setValue($value, $isFromDb = false)
 */
class CmsSetting extends CmsRecord {

    use KeyValueRecordHelpers;

    /**
     * @return CmsSettingsTable|KeyValueTableInterface
     */
    static public function getTable() {
        return app(CmsSettingsTable::class);
    }

}