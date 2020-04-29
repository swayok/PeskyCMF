<?php echo "<?php\n"; ?>

namespace App\Db;

use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\Db\Traits\CacheableDbObject;

abstract class BaseDbObject extends CmfDbObject {

    use CacheableDbObject;

    protected $_autoAddPkValueToPublicArray = false;
    protected $_baseModelClass = BaseDbModel::class;

    /**
     * Needed for IDE autocompletion
     * @return BaseDbModel
     */
    public function _getModel() {
        return parent::_getModel();
    }

    /**
     * @return string
     */
    public function getCurrentTime() {
        return static::getTable()->getCurrentTime();
    }

}