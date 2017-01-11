<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\Settings;

use App\Db\AbstractRecord;

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
class Setting extends AbstractRecord {

    /**
     * @return SettingsTable
     */
    static public function getTable() {
        return SettingsTable::getInstance();
    }

}