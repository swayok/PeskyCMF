<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use <?php echo $parentFullClassNameForRecord ?>;
use PeskyCMF\Db\Traits\KeyValueRecordHelpers;

/**
 * @property-read int         $id
 * @property-read string      $key
 * @property-read null|int    $admin_id
 * @property-read string      $value
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
class <?php echo $baseClassNameSingular; ?> extends <?php echo $parentClassNameForRecord ?> {

    use KeyValueRecordHelpers;

    const DEFAULT_BROWSER_TITLE = 'default_browser_title';
    const BROWSER_TITLE_ADDITION = 'browser_title_addition';
    const LANGUAGES = 'languages';
    const DEFAULT_LANGUAGE = 'default_language';
    const FALLBACK_LANGUAGES = 'fallback_languages';

    /**
     * @return <?php echo $baseClassNamePlural; ?>Table
     */
    static public function getTable() {
        return <?php echo $baseClassNamePlural; ?>Table::getInstance();
    }

}