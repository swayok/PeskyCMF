<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use App\<?php echo $dbClassesAppSubfolder ?>\AbstractTable;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Db\Traits\KeyValueTableHelpers;

class <?php echo $baseClassNamePlural; ?>Table extends AbstractTable implements KeyValueTableInterface {

    use KeyValueTableHelpers;

    public function getMainForeignKeyColumnName() {
        return null;
    }

    /**
     * @return <?php echo $baseClassNamePlural; ?>TableStructure
     */
    public function getTableStructure() {
        return <?php echo $baseClassNamePlural; ?>TableStructure::getInstance();
    }

    /**
     * @return <?php echo $baseClassNameSingular; ?>
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
