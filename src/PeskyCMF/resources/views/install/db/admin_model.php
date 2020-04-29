<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use App\Db\AppTable;

class AdminTable extends AppTable {

    protected $orderField = 'id';
    protected $orderDirection = self::ORDER_ASCENDING;
    protected $alias = 'Admin';
}