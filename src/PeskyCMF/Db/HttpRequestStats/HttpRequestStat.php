<?php

namespace PeskyCMF\Db\HttpRequestStats;

use App\Db\AbstractRecord;
use Illuminate\Http\Request;
use PeskyORM\Profiling\PeskyOrmPdoProfiler;
use Symfony\Component\HttpFoundation\Response;

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
 * @property-read string      $request_data
 * @property-read array       $request_data_as_array
 * @property-read object      $request_data_as_object
 * @property-read string      $checkpoints
 * @property-read array       $checkpoints_as_array
 * @property-read object      $checkpoints_as_object
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
 * @method $this    setRequestData($value, $isFromDb = false)
 * @method $this    setCheckpoints($value, $isFromDb = false)
 */
class HttpRequestStat extends AbstractRecord {

    static protected $startedAt;

    /**
     * @return HttpRequestStatsTable
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getTable() {
        return HttpRequestStatsTable::getInstance();
    }

    /**
     * @param float $startedAt
     * @return static
     */
    static public function createForProfiling(float $startedAt = null) {
        static::$startedAt = $startedAt === null ? microtime(true) : $startedAt;
        $stat = static::new1()
            ->setCreatedAt(date('Y-m-d H:i:s'));
        app()->instance(self::class, $stat);
        return $stat;
    }

    /**
     * @return static
     */
    static public function getCurrent() {
        if (!app()->bound(self::class)) {
            $stat = static::new1();
            app()->instance(self::class, $stat);
        }
        return app(self::class);
    }

    /**
     * @param string $key - checkpoint key to be used to finish it
     * @param string|null $descrption
     * @return static
     */
    static public function startCheckpoint(string $key, string $descrption = null) {
        $stat = static::getCurrent();
        if (static::$startedAt) {
            $checkpoints = $stat->checkpoints_as_array;
            $checkpoints[$key] = [
                'started_at' => microtime(true) - static::$startedAt,
                'desctiption' => $descrption,
                'memory_before' => memory_get_usage(true)
            ];
            $stat->setCheckpoints($checkpoints);
        }
        return $stat;
    }

    /**
     * @param string $key - checkpoint key used in static::startCheckpoint()
     * @param array $data
     * @return static
     * @throws \InvalidArgumentException
     */
    static public function endCheckpoint(string $key, array $data) {
        $stat = static::getCurrent();
        if (static::$startedAt) {
            $checkpoints = $stat->checkpoints_as_array;
            if (!array_key_exists($key, $checkpoints)) {
                throw new \InvalidArgumentException(
                    'There is no checkpoint with key "' . $key . '". Use HttpRequestStat::startCheckpoint() before.'
                );
            }
            $checkpoints[$key]['ended_at'] = microtime(true) - static::$startedAt;
            $checkpoints[$key]['duration'] = $checkpoints[$key]['ended_at'] - $checkpoints[$key]['started_at'];
            $checkpoints[$key]['memory_after'] = memory_get_usage(true);
            $checkpoints[$key]['memory_usage'] = $checkpoints[$key]['memory_after'] - $checkpoints[$key]['memory_before'];
            $checkpoints[$key]['data'] = $data;
            $stat->setCheckpoints($checkpoints);
        }
        return $stat;
    }

    /**
     * @param Request $request
     * @return $this
     * @throws \LogicException
     */
    public function processRequest(Request $request) {
        $this
            ->setUrl($request->getRequestUri())
            ->setHttpMethod($request->getMethod())
            ->setRoute('/' . ltrim($request->route()->uri(), '/'))
            ->setUrlParams($request->route()->parameters())
            ->setRequestData([
                'GET' => hidePasswords($request->query()),
                'POST' => hidePasswords($request->input())
            ]);
        return $this;
    }

    /**
     * @param Response $response
     * @param float $startedAt
     * @return $this
     */
    public function processResponse(Response $response, float $startedAt = null) {
        if ($startedAt === null) {
            $startedAt = static::$startedAt;
        }
        $this
            ->setDuration(round(microtime(true) - $startedAt, 6))
            ->setMemoryUsageMb(memory_get_peak_usage(true) / 1024 / 1024)
            ->setHttpCode($response->getStatusCode());
        return $this;
    }

    /**
     * @param float $startedAt
     * @return $this
     */
    public function addSqlProfilingData(float $startedAt = null) {
        if ($startedAt === null) {
            $startedAt = static::$startedAt;
        }
        $sqlQueriesInfo = PeskyOrmPdoProfiler::collect();
        $this
            ->setDurationSql(round($sqlQueriesInfo['accumulated_duration'], 6))
            ->setSql(static::processSqlProfiling($sqlQueriesInfo, $startedAt));
        return $this;
    }

    /**
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\OrmException
     */
    public function finishAndSave() {
        $this->save();
        return $this;
    }

    /**
     * @param array $sqlProfilingData
     * @param float $startedAt
     * @return array
     */
    static protected function processSqlProfiling(array $sqlProfilingData, float $startedAt) {
        $ret = [
            'statements_count' => "{$sqlProfilingData['statements_count']} (Failed: {$sqlProfilingData['failed_statements_count']})",
            'total_duration' => round($sqlProfilingData['accumulated_duration'], 6) . 's',
            'max_memory_usage' => round($sqlProfilingData['max_memory_usage'], 4) . ' MB',
            'statements' => []
        ];
        foreach ($sqlProfilingData['statements'] as $connection => $statements) {
            foreach ($statements as $statementStats) {
                $stats = [
                    'query' => $statementStats['sql'],
                ];
                if (!empty($statementStats['params'])) {
                    $stats['query_params'] = $statementStats['params'];
                }
                $stats['rows_affected'] = $statementStats['row_count'];
                $stats['started_at'] = round($statementStats['started_at'] - $startedAt, 6) . 's';
                $stats['duration'] = round($statementStats['duration'], 6) . 's';
                $stats['ended_at'] = round($statementStats['ended_at'] - $startedAt, 6) . 's';
                $stats['memory_before'] = round($statementStats['memory_before'] / 1024 / 1024, 4) . ' MB';
                $stats['memory_after'] = round($statementStats['memory_after'] / 1024 / 1024, 4) . ' MB';
                $stats['memory_used'] = round($statementStats['memory_used'] / 1024 / 1024, 4) . ' MB';
                if (array_key_exists('is_success', $statementStats) && !$statementStats['is_success']) {
                    $stats['error'] = $statementStats['error_code'] . ': ' . $statementStats['error_message'];
                }
                $stats['connection'] = $connection;
                $ret['statements'][] = $stats;
            }
        }
        usort($ret['statements'], function ($stat1, $stat2) {
            if ($stat1['started_at'] === $stat2['started_at']) {
                return 0;
            } else {
                return $stat1['started_at'] > $stat2['started_at'] ? 1 : -1;
            }
        });
        return $ret;
    }
}
