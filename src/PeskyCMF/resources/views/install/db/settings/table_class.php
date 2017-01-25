<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use <?php echo $parentFullClassNameForTable ?>;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Db\Traits\KeyValueTableHelpers;

class <?php echo $baseClassNamePlural; ?>Table extends <?php echo $parentClassNameForTable ?> implements KeyValueTableInterface {

    use KeyValueTableHelpers;

    public function getMainForeignKeyColumnName() {
        return null;
    }

    /**
     * @param null $foreignKeyValue
     * @return null|string
     */
    static public function getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue = null) {
        return 'app-<?php echo $baseClassNameUnderscored ?>';
    }

    /**
     * @return <?php echo $baseClassNamePlural; ?>TableStructure
     */
    public function getTableStructure() {
        return <?php echo $baseClassNamePlural; ?>TableStructure::getInstance();
    }

    /**
     * @return <?php echo $baseClassNameSingular . "\n"; ?>
     */
    public function newRecord() {
        return new <?php echo $baseClassNameSingular; ?>();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return '<?php echo $baseClassNamePlural; ?>';
    }

}
