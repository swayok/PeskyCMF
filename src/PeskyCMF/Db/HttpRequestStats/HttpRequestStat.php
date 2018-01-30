<?php

namespace PeskyCMF\Db\HttpRequestStats;

use App\Db\AbstractRecord;

/**
 * @property-read int         $id
 * @property-read string      $http_method
 * @property-read string      $url
 * @property-read string      $route
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read float       $duration
 * @property-read float       $duration_sql
 * @property-read float       $memory_usage_mb
 * @property-read bool        $is_cache
 * @property-read string      $url_params
 * @property-read array       $url_params_as_array
 * @property-read object      $url_params_as_object
 * @property-read string      $sql
 * @property-read array       $sql_as_array
 * @property-read object      $sql_as_object
 * @property-read integer     $http_code
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setHttpMethod($value, $isFromDb = false)
 * @method $this    setUrl($value, $isFromDb = false)
 * @method $this    setRoute($value, $isFromDb = false)
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setDuration($value, $isFromDb = false)
 * @method $this    setDurationSql($value, $isFromDb = false)
 * @method $this    setMemoryUsageMb($value, $isFromDb = false)
 * @method $this    setIsCache($value, $isFromDb = false)
 * @method $this    setUrlParams($value, $isFromDb = false)
 * @method $this    setSql($value, $isFromDb = false)
 * @method $this    setHttpCode($value, $isFromDb = false)
 */
class HttpRequestStat extends AbstractRecord {

    /**
     * @return HttpRequestStatsTable
     */
    static public function getTable() {
        return HttpRequestStatsTable::getInstance();
    }

}
