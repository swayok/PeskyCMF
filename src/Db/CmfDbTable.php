<?php

declare(strict_types=1);

namespace PeskyCMF\Db;

use PeskyORM\Core\DbExpr;
use PeskyORM\Core\Utils;
use PeskyORM\ORM\Table;

abstract class CmfDbTable extends Table
{
    
    private static ?array $timeZonesList = null;
    private static ?array $timeZonesOptions = null;
    
    public static function getTimezonesList($asOptions = false): array
    {
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
    
    public function getCurrentTime(): int
    {
        $result = static::selectValue(static::getCurrentTimeDbExpr());
        return $result ? strtotime($result) : time();
    }
    
    public static function getCurrentTimeDbExpr(): DbExpr
    {
        return DbExpr::create('NOW()');
    }
    
    public static function _getCurrentTime(): int
    {
        $ds = self::getConnection(false);
        $query = 'SELECT ' . $ds->quoteDbExpr(static::getCurrentTimeDbExpr());
        /** @var string $result */
        $result = $ds->query($query, Utils::FETCH_VALUE);
        return $result ? strtotime($result) : time();
    }
    
}