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
 *
 * @method static string default_browser_title($fkValue = null, $default = null)
 * @method static string browser_title_addition($fkValue = null, $default = null)
 * @method static array languages($fkValue = null, $default = null)
 * @method static string default_language($fkValue = null, $default = null)
 * @method static array fallback_languages($fkValue = null, $default = null)
 */
class CmsSetting extends CmsRecord {

    use KeyValueRecordHelpers;

    const DEFAULT_BROWSER_TITLE = 'default_browser_title';
    const BROWSER_TITLE_ADDITION = 'browser_title_addition';
    const LANGUAGES = 'languages';
    const DEFAULT_LANGUAGE = 'default_language';
    const FALLBACK_LANGUAGES = 'fallback_languages';

    /**
     * @return CmsSettingsTable|KeyValueTableInterface
     */
    static public function getTable() {
        return app(CmsSettingsTable::class);
    }

}