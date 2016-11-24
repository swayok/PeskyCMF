<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\StringUtils;

abstract class AbstractTable extends CmfDbTable {

}