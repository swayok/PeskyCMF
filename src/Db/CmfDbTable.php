<?php

namespace PeskyCMF\Db;

use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\StringUtils;

abstract class CmfDbTable extends Table {

    /** @var null|string */
    private $recordClass;
    /** @var array */
    static private $timeZonesList;
    /** @var array */
    static private $timeZonesOptions;

    static public function getTimezonesList($asOptions = false) {
        if (self::$timeZonesList === null) {
            self::$timeZonesList = \DateTimeZone::listIdentifiers();
        }
        if ($asOptions) {
            if (self::$timeZonesOptions === null) {
                self::$timeZonesOptions = [];
                foreach (self::$timeZonesList as $tzName) {
                    self::$timeZonesOptions[$tzName] = $tzName;
                }
            }
            return self::$timeZonesOptions;
        } else {
            return self::$timeZonesList;
        }
    }

    public function getCurrentTime() {
        $result = static::selectValue(static::getCurrentTimeDbExpr());
        return $result ? strtotime($result) : time();
    }

    static public function getCurrentTimeDbExpr(): DbExpr {
        return DbExpr::create('NOW()');
    }

    static public function _getCurrentTime() {
        $ds = self::getConnection(false);
        $query = 'SELECT ' . $ds->quoteDbExpr(static::getCurrentTimeDbExpr());
        /** @var string $result */
        $result = $ds->query($query, Utils::FETCH_VALUE);
        return $result ? strtotime($result) : time();
    }

    /**
     * @return Record|CmfDbRecord
     */
    public function newRecord() {
        if (!$this->recordClass) {
            $class = new \ReflectionClass(get_called_class());
            $this->recordClass = $class->getNamespaceName() . '\\'
                . StringUtils::singularize(str_replace('Table', '', $class->getShortName()));
        }
        return new $this->recordClass;
    }

    /**
     * @return TableStructure
     */
    public function getTableStructure() {
        /** @var TableStructure $class */
        $class = get_called_class() . 'Structure';
        return $class::getInstance();
    }

}