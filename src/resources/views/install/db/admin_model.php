<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use App\Db\BaseDbModel;

class AdminModel extends BaseDbModel {

    protected $orderField = 'id';
    protected $orderDirection = self::ORDER_ASCENDING;
    protected $alias = 'Admin';
}