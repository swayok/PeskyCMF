<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

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
class <?php echo $baseClassNameSingular; ?> extends AbstractRecord {

    /**
     * @return <?php echo $baseClassNamePlural; ?>Table
     */
    static public function getTable() {
        return <?php echo $baseClassNamePlural; ?>Table::getInstance();
    }

}