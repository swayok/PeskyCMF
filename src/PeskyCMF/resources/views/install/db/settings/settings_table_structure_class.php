<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Admins;

use PeskyCMF\Db\Traits\AdminIdColumn;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableStructure;

/**
 * @property-read Column    $id
 * @property-read Column    $key
 * @property-read Column    $admin_id
 * @property-read Column    $value
 */
class SettingsTableStructure extends TableStructure {

    use IdColumn,
        AdminIdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'settings';
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