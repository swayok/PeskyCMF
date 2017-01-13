<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use <?php echo $parentFullClassNameForRecord ?>;

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
 */
class <?php echo $baseClassNameSingular; ?> extends <?php echo $parentClassNameForRecord ?> {

    const DEFAULT_BROWSER_TITLE = 'default_browser_title';
    const BROWSER_TITLE_ADDITION = 'browser_title_addition';
    const LANGUAGES = 'languages';

    /**
     * @return <?php echo $baseClassNamePlural; ?>Table
     */
    static public function getTable() {
        return <?php echo $baseClassNamePlural; ?>Table::getInstance();
    }

}