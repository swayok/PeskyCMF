<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Admins;

use App\<?php echo $dbClassesAppSubfolder ?>\AbstractTable;

class AdminsTable extends AbstractTable {

    /**
     * @return AdminsTableStructure
     */
    public function getTableStructure() {
        return AdminsTableStructure::getInstance();
    }

    /**
     * @return Admin
     */
    public function newRecord() {
        return new Admin();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'Admins';
    }
}