<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use <?php echo $parentFullClassNameForTable ?>;

class <?php echo $baseClassNamePlural; ?>Table extends <?php echo $parentClassNameForTable ?> {

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