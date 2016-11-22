<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\StringUtils;

abstract class AbstractTable extends CmfDbTable {

    /** @var null|string */
    protected $recordClass = null;
    /** @var int */
    static private $currentTime;

    public function newRecord() {
        if (!$this->recordClass) {
            $class = new \ReflectionClass(get_called_class());
            $this->recordClass = $class->getNamespaceName() . '\\'
                . StringUtils::singularize(str_replace('Table', '', $class->getShortName()));
        }
        return new $this->recordClass;
    }

    public function getTableStructure() {
        /** @var TableStructure $class */
        $class = get_called_class() . 'Structure';
        return $class::getInstance();
    }

    public function getCurrentTime() {
        if (self::$currentTime === null) {
            self::$currentTime = strtotime(static::selectValue(static::getCurrentTimeDbExpr()));
        }
        return self::$currentTime;
    }

    static public function getCurrentTimeDbExpr() {
        return DbExpr::create('timezone(``Europe/Moscow``, NOW())');
    }

    static public function _getCurrentTime() {
        if (empty(self::$currentTime)) {
            $ds = self::getConnection();
            $query = 'SELECT ' . $ds->quoteDbExpr(static::getCurrentTimeDbExpr());
            self::$currentTime = strtotime($ds->query($query, Utils::FETCH_VALUE));
        }
        return self::$currentTime;
    }

}