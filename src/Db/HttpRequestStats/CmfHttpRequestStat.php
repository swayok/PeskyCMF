<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\Db\CmfDbRecord;
use PeskyORM\Profiling\PeskyOrmPdoProfiler;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read int $id
 * @property-read string $http_method
 * @property-read string $url
 * @property-read string $route
 * @property-read string $created_at
 * @property-read string $created_at_as_date
 * @property-read string $created_at_as_time
 * @property-read int $created_at_as_unix_ts
 * @property-read float $duration
 * @property-read float $duration_sql
 * @property-read float $duration_error
 * @property-read float $memory_usage_mb
 * @property-read bool $is_cache
 * @property-read string $url_params
 * @property-read array $url_params_as_array
 * @property-read \stdClass $url_params_as_object
 * @property-read string $sql
 * @property-read array $sql_as_array
 * @property-read \stdClass $sql_as_object
 * @property-read integer $http_code
 * @property-read string $request_data
 * @property-read array $request_data_as_array
 * @property-read \stdClass $request_data_as_object
 * @property-read string $checkpoints
 * @property-read array $checkpoints_as_array
 * @property-read \stdClass $checkpoints_as_object
 * @property-read string $counters
 * @property-read array $counters_as_array
 * @property-read \stdClass $counters_as_object
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
class CmfHttpRequestStat extends CmfDbRecord
{
    
    protected static ?float $startedAt = null;
    protected static array $checkpointsStack = [];
    protected int $accumulatedDurationError = 0;
    protected static CmfHttpRequestStat $current;
    
    public static function getTable(): CmfHttpRequestStatsTable
    {
        return CmfHttpRequestStatsTable::getInstance();
    }
    
    public static function createForProfiling(?float $startedAt = null): CmfHttpRequestStat
    {
        static::$current = static::new1()->setCreatedAt(date('Y-m-d H:i:s'));
        static::$startedAt = $startedAt ?? microtime(true);
        return static::$current;
    }
    
    public static function getCurrent(): CmfHttpRequestStat
    {
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
    public static function startCheckpoint(string $key, ?string $descrption = null): void
    {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $checkpoints = $stat->checkpoints_as_array;
            $data = [
                'started_at' => microtime(true) - static::$startedAt,
                'description' => $descrption ?: 'Checkpoint "' . $key . '"',
                'memory_before' => memory_get_usage(false),
                'checkpoints' => [],
            ];
            if (count(static::$checkpointsStack) === 0) {
                if (array_key_exists($key, $checkpoints)) {
                    throw new \InvalidArgumentException("Checkpoint with key \"$key\" already exists");
                }
                $checkpoints[$key] = $data;
            } else {
                $path = implode('.checkpoints.', static::$checkpointsStack) . '.checkpoints.' . $key;
                if (Arr::has($checkpoints, $path)) {
                    throw new \InvalidArgumentException("Checkpoint at path \"$path\" already exists");
                }
                Arr::set($checkpoints, $path, $data);
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
    public static function endCheckpoint(string $key, array $data = []): void
    {
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
            if (!Arr::has($checkpoints, $path)) {
                throw new \InvalidArgumentException(
                    'There is no checkpoint at path "' . $path . '". Use CmfHttpRequestStat::startCheckpoint() before CmfHttpRequestStat::endCheckpoint().'
                );
            }
            $checkpoint = Arr::get($checkpoints, $path);
            $checkpoint['ended_at'] = microtime(true) - static::$startedAt;
            $checkpoint['duration'] = $checkpoint['ended_at'] - $checkpoint['started_at'];
            $checkpoint['memory_after'] = memory_get_usage(false);
            $checkpoint['memory_usage'] = $checkpoint['memory_after'] - $checkpoint['memory_before'];
            $checkpoint['data'] = $data;
            Arr::set($checkpoints, $path, $checkpoint);
            $stat->setCheckpoints($checkpoints);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }
    
    public static function profileClosure(string $checkpointKey, \Closure $closure): mixed
    {
        static::startCheckpoint($checkpointKey);
        $ret = value($closure);
        static::endCheckpoint($checkpointKey);
        return $ret;
    }
    
    public static function setCounterValue(string $counterName, float $value): void
    {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $counters = $stat->counters_as_array;
            $counters[$counterName] = $value;
            $stat->setCounters($counters);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }
    
    public static function increment(string $counterName, float $increment = 1): void
    {
        if (static::$startedAt) {
            $time = microtime(true);
            $stat = static::getCurrent();
            $counters = $stat->counters_as_array;
            $counters[$counterName] = Arr::get($counters, $counterName, 0) + $increment;
            $stat->setCounters($counters);
            $stat->accumulatedDurationError += microtime(true) - $time;
        }
    }
    
    public static function decrement(string $counterName, float $increment = 1): void
    {
        static::increment($counterName, -$increment);
    }
    
    public static function requestUsesCachedData(): void
    {
        if (static::$startedAt) {
            static::getCurrent()->setIsCache(true);
        }
    }
    
    /**
     * @param Request $request
     * @return static
     */
    public function processRequest(Request $request): CmfHttpRequestStat
    {
        $time = microtime(true);
        $route = $request->route();
        $this
            ->setUrl($request->getPathInfo())
            ->setHttpMethod($request->getMethod())
            ->setRoute('/' . ltrim($route->uri(), '/'))
            ->setUrlParams($route->parameters())
            ->setRequestData([
                'GET' => $this->hidePasswords($request->query()),
                'POST' => $this->hidePasswords($request->input()),
            ]);
        $this->accumulatedDurationError += microtime(true) - $time;
        return $this;
    }
    
    protected function hidePasswords(array $data): array
    {
        return hidePasswords($data);
    }
    
    public function processResponse(Response $response, ?float $startedAt = null): CmfHttpRequestStat
    {
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
    
    public function addSqlProfilingData(?float $startedAt = null): CmfHttpRequestStat
    {
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
     * @throws \LogicException
     */
    public function finishAndSave(): CmfHttpRequestStat
    {
        $this->setDurationError(round($this->accumulatedDurationError, 6));
        if (count(static::$checkpointsStack) > 0) {
            if ($this->http_code >= 400) {
                $stackReversed = array_reverse(static::$checkpointsStack);
                foreach ($stackReversed as $key) {
                    static::endCheckpoint($key, ['automatically closed due to error response']);
                }
            } else {
                throw new \LogicException(
                    'All checkpoints must be ended. Possibly you have forgotten to call CmfHttpRequestStat::endCheckpoint() somewhere'
                );
            }
        }
        $this->save();
        return $this;
    }
    
    protected static function processSqlProfiling(array $sqlProfilingData, float $startedAt): array
    {
        $ret = [
            'statements_count' => $sqlProfilingData['statements_count'],
            'failed_statements_count' => $sqlProfilingData['failed_statements_count'],
            'rows_affected' => 0,
            'statements_count_str' => "{$sqlProfilingData['statements_count']} (Failed: {$sqlProfilingData['failed_statements_count']})",
            'total_duration' => round($sqlProfilingData['accumulated_duration'], 6) . 's',
            'max_memory_usage' => round($sqlProfilingData['max_memory_usage'] / 1024 / 1024, 4) . ' MB',
            'statements' => [],
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
