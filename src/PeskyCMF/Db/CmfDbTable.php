<?php

namespace PeskyCMF\Db;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\Table;

abstract class CmfDbTable extends Table {

    /** @var null|ScaffoldSectionConfig */
    protected $scaffoldConfig = null;
    /** @var array */
    static private $timeZonesList = null;
    /** @var array */
    static private $timeZonesOptions = null;

    static public function getTimezonesList($asOptions = false) {
        if (self::$timeZonesList === null) {
            $ds = self::getConnection();
            $query = $ds->quoteDbExpr(
                DbExpr::create('SELECT * from `pg_timezone_names` ORDER BY `utc_offset` ASC')->get()
            );
            self::$timeZonesList = $ds->query($query, Utils::FETCH_ALL);
        }
        if ($asOptions) {
            if (self::$timeZonesOptions === null) {
                self::$timeZonesOptions = [];
                foreach (self::$timeZonesList as $tzInfo) {
                    $offset = preg_replace('%:\d\d$%', '', $tzInfo['utc_offset']);
                    $offsetPrefix = $offset[0] === '-' ? '' : '+';
                    self::$timeZonesOptions[$tzInfo['name']] = "({$offsetPrefix}{$offset}) {$tzInfo['name']}";
                }
            }
            return self::$timeZonesOptions;
        } else {
            return self::$timeZonesList;
        }
    }

    /**
     * @return ScaffoldSectionConfig
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

}