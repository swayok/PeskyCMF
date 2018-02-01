<?php

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;

class HttpRequestStatsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = false;
    protected $isEditAllowed = false;
    protected $isCloningAllowed = false;
    protected $isDeleteAllowed = true;
    
    static public function getTable() {
        return HttpRequestStatsTable::getInstance();
    }
    
    static protected function getIconForMenuItem() {
        return 'fa fa-area-chart'; //< icon classes like: 'fa fa-cog' or just delete if you do not want an icon
    }
    
    protected function createDataGridConfig() {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return parent::createDataGridConfig()
            ->setOrderBy('created_at', 'desc')
            ->setInvisibleColumns('http_method', 'route', 'duration_sql', 'duration_error', 'id')
            ->setColumns([
                'url' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $_, array $record) {
                        return "<span class='text-nowrap'>{$record['route']}<br>{$record['http_method']} {$record['url']}</span>";
                    }),
                'duration' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $_, array $record) {
                        $durErrorPercent = round($record['duration_error'] / $record['duration'], 4) * 100;
                        $sqlDurPercent = round($record['duration_sql'] / $record['duration'], 4) * 100;
                        return "<span class='text-nowrap'>{$record['duration']}s (-{$record['duration_error']}s ~ {$durErrorPercent}%)</span>
                            <br><span class='text-nowrap'>SQL: {$record['duration_sql']}s ({$sqlDurPercent}%)</span>
                        ";
                    }),
                'memory_usage_mb' => DataGridColumn::create()
                    ->setValueConverter(function ($value) {
                        return "$value MB";
                    }),
                'is_cache',
                'http_code',
                'created_at',
            ])
            ->setIsBulkItemsDeleteAllowed(true)
            ->setIsRowActionsEnabled(false);
    }
    
    protected function createDataGridFilterConfig() {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'http_method',
                'url',
                'route',
                'duration',
                'duration_sql',
                'memory_usage_mb',
                'is_cache',
                'http_code',
                'created_at',
            ]);
    }

    protected function createItemDetailsConfig() {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return parent::createItemDetailsConfig()
            ->setWidth(100)
            ->setAdditionalColumnsToSelect('sql')
            ->setValueCells([
                'id',
                'http_method',
                'url',
                'route',
                'url_params',
                'request_data',
                'duration' => ValueCell::create()
                    ->setValueConverter(function ($value) {
                        return $value . 's';
                    }),
                'duration_error' => ValueCell::create()
                    ->setValueConverter(function ($value, $_, $record) {
                        $percent = round($value / $record['duration'], 4) * 100;
                        return "{$value}s ({$percent}%)";
                    }),
                'duration_sql' => ValueCell::create()
                    ->setValueConverter(function ($value, $_, $record) {
                        $percent = round($value / $record['duration'], 4) * 100;
                        return "{$value}s ({$percent}%)";
                    }),
                'memory_usage_mb' => ValueCell::create()
                    ->setValueConverter(function ($value) {
                        return $value . ' MB';
                    }),
                'http_code',
                'is_cache',
                'created_at',
                'checkpoints' => ValueCell::create()
                    ->setValueConverter(function ($_, $__, $record) {
                        $sqlCheckpoints = json_decode($record['sql'], true);
                        $phpCheckpoints = json_decode($record['checkpoints'], true);
                        if (!is_array($phpCheckpoints) || empty($phpCheckpoints)) {
                            $ret = is_array($sqlCheckpoints)
                                ? $this->buildSqlQueriesLog($sqlCheckpoints, $record['duration'])
                                : '<pre class="json-text">' . $sqlCheckpoints . '</pre>';
                            if (!empty($phpCheckpoints)) {
                                $ret .= '<pre class="json-text">' . (string)$phpCheckpoints . '</pre>';
                            }
                            return $ret;
                        } else {
                            if (!is_array($sqlCheckpoints)) {
                                $sqlCheckpoints = [
                                    'statements_count' => 0,
                                    'total_duration' => '0s',
                                    'max_memory_usage' => '0 MB',
                                    'statements' => []
                                ];
                            }
                            return $this->buildMergedSqlAndCheckpointsLog(
                                $phpCheckpoints,
                                $sqlCheckpoints,
                                $record
                            );
                        }
                    }),
            ]);
    }

    protected function buildSqlQueriesLog(array $data, $totalDuration) {
        $ret = $this->buildSqlLogIntro($data);
        $index = 1;
        $prevStatement = [
            'started_at' => 0.00
        ];
        foreach ($data['statements'] as $statement) {
            $delta = (float)$statement['started_at'] - (float)$prevStatement['started_at'];
            $precent = round($delta / $totalDuration, 2) * 100;
            $ret .= "<hr class='mv10'>PHP: <b>{$delta}s ~ {$precent}%</b> ({$prevStatement['started_at']} -> {$statement['started_at']})<hr class='mv10'>";
            $ret .= $this->buildSqlQueryLog($statement, "<b>{$index}.</b>", $totalDuration);
            $prevStatement = $statement;
            $index++;
        }
        $diffToEnd = (float)$totalDuration - (float)$prevStatement['started_at'];
        $precent = round($diffToEnd / $totalDuration, 2) * 100;
        $ret .= "<hr class='mv10'>PHP: <b>{$diffToEnd}s ~ {$precent}%</b> ({$prevStatement['started_at']} -> {$totalDuration}s)" ;
        return $ret;
    }

    protected function buildMergedSqlAndCheckpointsLog(array $phpCheckpoints, array $sqlCheckpoints, array $record) {
        $errorPercentage = round($record['duration_error'] / $record['duration'], 4) * 100;
        $ret = "<div>
                Total duration: {$record['duration']}s 
                / Error due to profiler: {$record['duration_error']}s ({$errorPercentage}%) 
                / Peak memory: {$record['memory_usage_mb']} MB
            </div>";
        $ret .= $this->buildSqlLogIntro($sqlCheckpoints);
        // normalize php checkpoints
        $checkpoints = $this->injectSqlCheckpoints($phpCheckpoints, $sqlCheckpoints['statements']);
        $log = $this->normalizeMergedCheckpoints($checkpoints, 0, $record['duration']);
        return $ret . $this->buildMergedCheckpointsLog($log, (float)$record['duration']);
    }

    protected function injectSqlCheckpoints(array $phpCheckpoints, array $sqlCheckpoints): array {
        $ret = [];
        foreach ($phpCheckpoints as $checkpoint) {
            $sqlBefore = [];
            $sqlInside = [];
            $sqlAfter = [];
            foreach ($sqlCheckpoints as $sqlCheckpoint) {
                if ((float)$sqlCheckpoint['ended_at'] < $checkpoint['started_at']) {
                    $sqlBefore[] = $sqlCheckpoint;
                } else if (
                    (float)$sqlCheckpoint['started_at'] >= $checkpoint['started_at']
                    && (float)$sqlCheckpoint['ended_at'] <= $checkpoint['ended_at']
                ) {
                    $sqlInside[] = $sqlCheckpoint;
                } else {
                    $sqlAfter[] = $sqlCheckpoint;
                }
            }
            foreach ($sqlBefore as $sqlCheckpoint) {
                $ret[] = $sqlCheckpoint;
            }
            $checkpoint['checkpoints'] = $this->injectSqlCheckpoints($checkpoint['checkpoints'], $sqlInside);
            $ret[] = $checkpoint;
            $sqlCheckpoints = $sqlAfter;
        }
        return $ret;
    }

    protected function normalizeMergedCheckpoints(
        array $checkpoints,
        float $prevCheckpointEndsAt,
        float $sectionEndsAt
    ): array {
        $log = [];
        foreach ($checkpoints as $checkpoint) {
            if ((float)$checkpoint['started_at'] - $prevCheckpointEndsAt > 0) {
                $log[] = $this->makeNotTracedPhpCheckpoint($prevCheckpointEndsAt, (float)$checkpoint['started_at']);
            }
            if (!array_key_exists('query', $checkpoint)) {
                if (empty($checkpoint['checkpoints'])) {
                    $checkpoint['checkpoints'] = [];
                }
                if (!empty($checkpoint['checkpoints'])) {
                    $checkpoint['checkpoints'] = $this->normalizeMergedCheckpoints(
                        $checkpoint['checkpoints'],
                        (float)$checkpoint['started_at'],
                        (float)$checkpoint['ended_at']
                    );
                }
            }
            $log[] = $checkpoint;
            $prevCheckpointEndsAt = (float)$checkpoint['ended_at'];
        }
        if ($sectionEndsAt > $prevCheckpointEndsAt) {
            $log[] = $this->makeNotTracedPhpCheckpoint($prevCheckpointEndsAt, $sectionEndsAt);
        }
        return $log;
    }

    protected function makeNotTracedPhpCheckpoint(float $startsAt, float $endsAt): array {
        return [
            'started_at' => $startsAt,
            'ended_at' => $endsAt,
            'duration' => $endsAt - $startsAt,
            'description' => 'Not traced PHP',
            'memory_before' => null,
            'memory_after' => null,
            'memory_usage' => null,
            'data' => [],
            'checkpoints' => []
        ];
    }

    protected function buildMergedCheckpointsLog(array $checkpoints, float $totalDuration) {
        $ret = '';
        foreach ($checkpoints as $checkpoint) {
            if (array_key_exists('query', $checkpoint)) {
                $ret .= '<li>' . $this->buildSqlQueryLog($checkpoint, 'SQL query<br>', $totalDuration) . '</li>';
            } else {
                $ret .= '<li>' . $this->buildPhpCheckpointLog($checkpoint, $totalDuration) . '</li>';
            }
        }
        return '<ul>' . $ret . '</ul>';
    }

    protected function buildSqlLogIntro(array $data): string {
        return "
            <div>
                SQL queries executed: {$data['statements_count']} 
                / Total Duration: {$data['total_duration']} 
                / Peak memory: {$data['max_memory_usage']}
            </div>
            ";
    }

    protected function buildSqlQueryLog(array $statement, string $numeration = '', float $totalDuration): string {
        $query = trim(preg_replace(
            '%(?: |^)(WITH|SELECT|INSERT|UPDATE|DELETE|FROM|ORDER BY|WHERE|GROUP BY|HAVING|SET|VALUES|INTO|LIMIT|OFFSET) %is',
            "\n  <b>$1</b> ",
            htmlentities($statement['query'])
        ));
        $durationPercent = round((float)$statement['duration'] / $totalDuration, 4) * 100;
        $ret = "
            <div>
                $numeration
                Duration: <b>{$statement['duration']}~ {$durationPercent}%</b> ({$statement['started_at']} -> {$statement['ended_at']})
            </div>
            <div>Memory: {$statement['memory_used']} ({$statement['memory_before']} -> {$statement['memory_after']})</div>
            <div>Rows affected: {$statement['rows_affected']}</div><pre class='json-text'>$query</pre>
        ";
        if (!empty($statement['query_params'])) {
            $params = htmlentities(stripslashes(json_encode($statement['query_params'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)));
            $ret .= "<div>Query params:</div><pre class='json-text'>{$params}</pre>";
        }
        if (!empty($statement['error'])) {
            $ret .= '<div class="text-danger">' . $statement['error'] . '</div>';
        }
        return $ret;
    }

    protected function buildPhpCheckpointLog(array $checkpoint, float $totalDuration): string {
        $ret = '';
        if (!empty($checkpoint['description']) && trim($checkpoint['description']) !== '') {
            $ret .= '<div>' . $checkpoint['description'] . '</div>';
        } else {
            $ret .= '<div>Checkpoint</div>';
        }
        foreach (['duration', 'started_at', 'ended_at'] as $key) {
            $checkpoint[$key] = round($checkpoint[$key], 8);
        }
        $precent = round($checkpoint['duration'] / $totalDuration, 4) * 100;
        $ret .= "<div>Duration: <b>{$checkpoint['duration']}s ~ {$precent}%</b> ({$checkpoint['started_at']}s -> {$checkpoint['ended_at']}s)</div>";
        if (!empty($checkpoint['memory_usage'])) {
            foreach (['memory_usage', 'memory_before', 'memory_after'] as $key) {
                $checkpoint[$key] = round($checkpoint[$key] / 1024 / 1024, 4);
            }
            $ret .= "<div>Memory: {$checkpoint['memory_usage']} MB ({$checkpoint['memory_before']} MB -> {$checkpoint['memory_after']} MB)</div>";
        }
        if (!empty($checkpoint['data'])) {
            $ret .= '<pre class="json-text">'
                    . htmlentities(stripslashes(json_encode($checkpoint['data'], JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT)))
                . '</pre>';
        }
        if (!empty($checkpoint['checkpoints'])) {
            $ret .= $this->buildMergedCheckpointsLog($checkpoint['checkpoints'], $totalDuration);
        }
        return $ret;
    }
    
}