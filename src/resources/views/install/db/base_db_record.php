<?php
declare(strict_types=1);
/**
 * @var string $dbClassesAppSubfolder
 */
echo "<?php\n";
?>

namespace App\<?php echo $dbClassesAppSubfolder ?>;

use PeskyCMF\Db\CmfDbRecord;

abstract class AbstractRecord extends CmfDbRecord {

}