<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use PeskyCMF\Db\Traits\AdminIdColumn;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyORM\ORM\Column;
use <?php echo $parentFullClassNameForTableStructure ?>;

/**
 * @property-read Column    $id
 * @property-read Column    $key
 * @property-read Column    $admin_id
 * @property-read Column    $value
 */
class <?php echo $baseClassNamePlural; ?>TableStructure extends <?php echo $parentClassNameForTableStructure ?> {

    use IdColumn,
        AdminIdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return '<?php echo $baseClassNameUnderscored; ?>';
    }

    /**
     * @return string|null
     */
    static public function getSchema() {
        return null;
    }

    private function key() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->disallowsNullValues();
    }

    private function value() {
        return Column::create(Column::TYPE_TEXT)
            ->disallowsNullValues();
    }

}