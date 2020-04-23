<?php echo "<?php\n"; ?>

namespace App\Db;

use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Db\Traits\CacheableDbModel;
use PeskyORM\Db;
use PeskyORM\DbExpr;

abstract class BaseDbModel extends CmfDbModel {

    use CacheableDbModel;

    static public function getRootNamespace() {
        return __NAMESPACE__;
    }

    static private $currentTime = null;

    public function getCurrentTime() {
        if (empty(self::$currentTime)) {
            self::$currentTime = strtotime($this->expression(self::getCurrentTimeDbExpr()));
        }
        return self::$currentTime;
    }

    static public function getCurrentTimeDbExpr() {
        return DbExpr::create('timezone(``Europe/Moscow``, NOW())');
    }

    static public function _getCurrentTime() {
        if (empty(self::$currentTime)) {
            if (!empty(self::$dataSources['default'])) {
                $ds = self::$dataSources['default'];
                $query = $ds->replaceQuotes('SELECT ' . self::getCurrentTimeDbExpr()->get());
                self::$currentTime = strtotime(Db::processRecords($ds->query($query), Db::FETCH_VALUE));
            } else {
                throw new \UnexpectedValueException('There is no DataSource called [default]');
            }
        }
        return self::$currentTime;
    }

}