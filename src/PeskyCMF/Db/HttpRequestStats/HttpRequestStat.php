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
 * @property-read float       $duration_error
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
 * @property-read string      $counters
 * @property-read array       $counters_as_array
 * @property-read object      $counters_as_object
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setHttpMethod($value, $isFromDb = false)
 * @method $this    setUrl($value, $isFromDb = false)
 * @method $this    setRoute($value, $isFromDb = false)
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setDuration($value, $isFromDb = false)
 * @method $this    setDurationSql($value, $isFromDb = false)
 * @method $this    setDurationError($value, $isFromDb = false)
 * @method $this    setMemoryUsageMb($value, $isFromDb = false)
 * @method $this    setIsCache($value, $isFromDb = false)
 * @method $this    setUrlParams($value, $isFromDb = false)
 * @method $this    setSql($value, $isFromDb = false)
 * @method $this    setHttpCode($value, $isFromDb = false)
 * @method $this    setRequestData($value, $isFromDb = false)
 * @method $this    setCheckpoints($value, $isFromDb = false)
 * @method $this    setCounters($value, $isFromDb = false)
 */
class HttpRequestStat extends AbstractRecord {

    static protected $startedAt;
    static protected $checkpointsStack = [];
    protected $accumulatedDurationError = 0;
    static protected $current;

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
        static::$current = static::new1()->setCreatedAt(date('Y-m-d H:i:s'));
        static::$startedAt = $startedAt === null ? microtime(true) : $startedAt;
        return static::$current;
    }

    /**
     * @return static
     */
    static public function getCurrent() {
        if (!static::$current) {
            static::$current = static::new1();
        }
        return static::$current;
    }

    /**
     * @param string $key - checkpoint key to be used to finish it
     * @param string|null $descrption
     * @throws \InvalidArgumentException
     */
    static public function startCheckpoint(string $key, string $descrption = null) {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $checkpoints = $stat->checkpoints_as_array;
            $data = [
                'started_at' => microtime(true) - static::$startedAt,
                'description' => $descrption ?: 'Checkpoint "' . $key . '"',
                'memory_before' => memory_get_usage(false),
                'checkpoints' => []
            ];
            if (count(static::$checkpointsStack) === 0) {
                if (array_key_exists($key, $checkpoints)) {
                    throw new \InvalidArgumentException("Checkpoint with key \"$key\" already exists");
                }
                $checkpoints[$key] = $data;
            } else {
                $path = implode('.checkpoints.', static::$checkpointsStack) . '.checkpoints.' . $key;
                if (array_has($checkpoints, $path)) {
                    throw new \InvalidArgumentException("Checkpoint at path \"$path\" already exists");
                }
                array_set($checkpoints, $path, $data);
            }
            $stat->setCheckpoints($checkpoints);
            static::$checkpointsStack[] = $key;
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }

    /**
     * @param string $key - checkpoint key used in static::startCheckpoint()
     * @param array $data
     * @throws \InvalidArgumentException
     */
    static public function endCheckpoint(string $key, array $data = []) {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $checkpoints = $stat->checkpoints_as_array;
            $path = implode('.checkpoints.', static::$checkpointsStack);
            $lastKeyInStack = array_pop(static::$checkpointsStack);
            if ($key !== $lastKeyInStack) {
                throw new \InvalidArgumentException(
                    "You need to end checkpoint at path \"$lastKeyInStack\" before trying to end checkpoint with key \"$key\""
                );
            }
            if (!array_has($checkpoints, $path)) {
                throw new \InvalidArgumentException(
                    'There is no checkpoint at path "' . $path . '". Use HttpRequestStat::startCheckpoint() before HttpRequestStat::endCheckpoint().'
                );
            }
            $checkpoint = array_get($checkpoints, $path);
            $checkpoint['ended_at'] = microtime(true) - static::$startedAt;
            $checkpoint['duration'] = $checkpoint['ended_at'] - $checkpoint['started_at'];
            $checkpoint['memory_after'] = memory_get_usage(false);
            $checkpoint['memory_usage'] = $checkpoint['memory_after'] - $checkpoint['memory_before'];
            $checkpoint['data'] = $data;
            array_set($checkpoints, $path, $checkpoint);
            $stat->setCheckpoints($checkpoints);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }

    static public function profileClosure(string $checkpointKey, \Closure $closure): mixed {
        static::startCheckpoint($checkpointKey);
        $ret = value($closure);
        static::endCheckpoint($checkpointKey);
        return $ret;
    }

    static public function setCounterValue(string $counterName, float $value) {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $counters = $stat->counters_as_array;
            $counters[$counterName] = $value;
            $stat->setCounters($counters);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }

    static public function increment(string $counterName, float $increment = 1) {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $counters = $stat->counters_as_array;
            $counters[$counterName] = array_get($counters, $counterName, 0) + $increment;
            $stat->setCounters($counters);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }

    static public function decrement(string $counterName, float $increment = 1) {
        static::increment($counterName, -$increment);
    }

    static public function requestUsesCachedData() {
        if (static::$startedAt) {
            static::getCurrent()->setIsCache(true);
        }
    }

    /**
     * @param Request $request
     * @return $this
     * @throws \LogicException
     */
    public function processRequest(Request $request) {
        $time = microtime(true);
        $this
            ->setUrl($request->getPathInfo())
            ->setHttpMethod($request->getMethod())
            ->setRoute('/' . ltrim($request->route()->uri(), '/'))
            ->setUrlParams($request->route()->parameters())
            ->setRequestData([
                'GET' => hidePasswords($request->query()),
                'POST' => hidePasswords($request->input())
            ]);
        $this->accumulatedDurationError += microtime(true) - $time;
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
        $time = microtime(true);
        $this
            ->setDuration(round(microtime(true) - $startedAt, 6))
            ->setMemoryUsageMb(round(memory_get_peak_usage(false) / 1024 / 1024, 4))
            ->setHttpCode($response->getStatusCode());
        $this->accumulatedDurationError += microtime(true) - $time;
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
        $time = microtime(true);
        $sqlQueriesInfo = PeskyOrmPdoProfiler::collect();
        $formattedLog = static::processSqlProfiling($sqlQueriesInfo, $startedAt);
        $this
            ->setDurationSql(round($sqlQueriesInfo['accumulated_duration'], 6))
            ->setSql($formattedLog);
        $this->accumulatedDurationError += microtime(true) - $time;
        return $this;
    }

    /**
     * @return $this
     * @throws \LogicException
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
        $this->setDurationError(round($this->accumulatedDurationError, 6));
        if (count(static::$checkpointsStack) > 0) {
            if ($this->http_code >= 400) {
                $stackReversed = array_reverse(static::$checkpointsStack);
                foreach ($stackReversed as $key) {
                    static::endCheckpoint($key, ['automatically closed due to error response']);
                }
            } else {
                throw new \LogicException(
                    'All checkpoints must be ended. Possibly you have forgotten to call HttpRequestStat::endCheckpoint() somewhere'
                );
            }
        }
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
            'statements_count' => $sqlProfilingData['statements_count'],
            'failed_statements_count' => $sqlProfilingData['failed_statements_count'],
            'rows_affected' => 0,
            'statements_count_str' => "{$sqlProfilingData['statements_count']} (Failed: {$sqlProfilingData['failed_statements_count']})",
            'total_duration' => round($sqlProfilingData['accumulated_duration'], 6) . 's',
            'max_memory_usage' => round($sqlProfilingData['max_memory_usage'] / 1024 / 1024, 4) . ' MB',
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
                $ret['rows_affected'] += $stats['rows_affected'] = $statementStats['row_count'];
                $stats['started_at'] = round($statementStats['started_at'] - $startedAt, 8) . 's';
                $stats['duration'] = round($statementStats['duration'], 8) . 's';
                $stats['ended_at'] = round($statementStats['ended_at'] - $startedAt, 8) . 's';
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
