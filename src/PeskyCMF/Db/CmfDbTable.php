<?php

namespace PeskyCMF\Db;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableStructure;
use Swayok\Utils\StringUtils;

abstract class CmfDbTable extends Table {

    /** @var null|ScaffoldConfig */
    protected $scaffoldConfig = null;
    /** @var null|string */
    protected $recordClass = null;
    /** @var array */
    static private $timeZonesList = null;
    /** @var array */
    static private $timeZonesOptions = null;
    /** @var int */
    static private $currentTime;

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
        if (self::$currentTime === null) {
            self::$currentTime = strtotime(static::selectValue(static::getCurrentTimeDbExpr()));
        }
        return self::$currentTime;
    }

    static public function getCurrentTimeDbExpr() {
        return DbExpr::create('NOW()');
    }

    static public function _getCurrentTime() {
        if (empty(self::$currentTime)) {
            $ds = self::getConnection(false);
            $query = 'SELECT ' . $ds->quoteDbExpr(static::getCurrentTimeDbExpr());
            self::$currentTime = strtotime($ds->query($query, Utils::FETCH_VALUE));
        }
        return self::$currentTime;
    }

    /**
     * @return ScaffoldConfig
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getScaffoldConfig() {
        if (!$this->scaffoldConfig) {
            $this->scaffoldConfig = CmfConfig::getScaffoldConfig($this, static::getName());
        }
        return $this->scaffoldConfig;
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