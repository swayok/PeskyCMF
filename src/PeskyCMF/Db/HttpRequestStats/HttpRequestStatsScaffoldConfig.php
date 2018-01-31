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
                        return "<span class='text-nowrap'>{$record['duration']}s (-{$record['duration_error']}s)</span>
                            <br><span class='text-nowrap'>SQL: {$record['duration_sql']}s</span>
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
                    ->setValueConverter(function ($value) {
                        return $value . 's';
                    }),
                'duration_sql' => ValueCell::create()
                    ->setValueConverter(function ($value) {
                        return $value . 's';
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
                            if (empty($phpCheckpoints)) {
                                $ret .= '<pre class="json-text">' . $phpCheckpoints . '</pre>';
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
            $ret .= "<hr class='mv10'>PHP: <b>{$delta}s</b> ({$prevStatement['started_at']} -> {$statement['started_at']})<hr class='mv10'>";
            $ret .= $this->buildSqlQueryLog($statement, '<b>{$index}.</b>');
            $prevStatement = $statement;
            $index++;
        }
        $diffToEnd = (float)$totalDuration - (float)$prevStatement['started_at'];
        $ret .= "<hr class='mv10'>PHP: <b>{$diffToEnd}s</b> ({$prevStatement['started_at']} -> {$totalDuration}s)" ;
        return $ret;
    }

    protected function buildMergedSqlAndCheckpointsLog(array $phpCheckpoints, array $sqlCheckpoints, array $record) {
        $errorPercentage = round($record['duration_error'] / $record['duration'], 3) * 100;
        $ret = "<div>
                Total duration: {$record['duration']}s 
                / Error due to profiler: {$record['duration_error']}s ({$errorPercentage}%) 
                / Peak memory: {$record['memory_usage_mb']} MB
            </div>";
        $ret .= $this->buildSqlLogIntro($sqlCheckpoints);
        // normalize php checkpoints
        $baseCheckpointEndsOn = count($phpCheckpoints) ? array_values($phpCheckpoints)[0]['started_at'] : $record['duration'];
        $prevCheckpoint = $this->makeNotTracedPhpCheckpoint(0, $baseCheckpointEndsOn);
        $log = [];
        if ($prevCheckpoint['duration'] > 0) {
            $log[] = $prevCheckpoint;
        }
        foreach ($phpCheckpoints as $key => $checkpoint) {
            if ($checkpoint['started_at'] - $prevCheckpoint['ended_at'] > 0) {

            }
        }

        return $ret;
    }

    /**
     * @param float $startsAt
     * @param float $endsAt
     * @return array
     */
    protected function makeNotTracedPhpCheckpoint(float $startsAt, float $endsAt) {
        return [
            'started_at' => $startsAt,
            'ended_at' => $endsAt,
            'duration' => $endsAt - $startsAt,
            'description' => 'Not traced PHP',
            'memory_before' => '?',
            'memory_after' => '?',
            'memory_usage' => '?',
            'data' => [],
            'checkpoints' => []
        ];
    }

    protected function normalizePhpCheckpoints(array $checkpoints, $startedAt) {

    }

    protected function buildSqlLogIntro(array $data) {
        return "
            <div>
                SQL queries executed: {$data['statements_count']} 
                / Total Duration: {$data['total_duration']} 
                / Peak memory: {$data['max_memory_usage']}
            </div>
            ";
    }

    protected function buildSqlQueryLog(array $statement, string $numeration = '') {
        $query = htmlentities($statement['query']);
        $ret = "
            <div>
                $numeration
                Duration: <b>{$statement['duration']}</b> ({$statement['started_at']} -> {$statement['ended_at']}) <br>
                Memory: {$statement['memory_used']} ({$statement['memory_before']} -> {$statement['memory_after']})
            </div>
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
    
}