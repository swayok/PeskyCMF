<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Settings;

use App\<?php echo $dbClassesAppSubfolder ?>\AbstractTable;
use PeskyCMF\Db\KeyValueTableInterface;
use PeskyCMF\Db\Traits\KeyValueTableHelpers;

class SettingsTable extends AbstractTable implements KeyValueTableInterface {

    use KeyValueTableHelpers;

    public function getMainForeignKeyColumnName() {
        return null;
    }

    /**
     * @return SettingsTableStructure
     */
    public function getTableStructure() {
        return SettingsTableStructure::getInstance();
    }

    /**
     * @return Setting
     */
    public function newRecord() {
        return new Setting();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'Settings';
    }

}
