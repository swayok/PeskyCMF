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
        return parent::createDataGridConfig()
            ->setOrderBy('created_at', 'desc')
            ->setInvisibleColumns('http_method', 'route', 'duration_sql', 'id')
            ->setColumns([
                'url' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $_, array $record) {
                        return "<span class='text-nowrap'>{$record['route']}<br>{$record['http_method']} {$record['url']}</span>";
                    }),
                'duration' => DataGridColumn::create()
                    ->setValueConverter(function ($value, $_, array $record) {
                        return "<span class='text-nowrap'>{$record['duration']}s (SQL: {$record['duration_sql']}s)</span>";
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
        return parent::createItemDetailsConfig()
            ->setWidth(100)
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
                'sql' => ValueCell::create()
                    ->setValueConverter(function ($value, $_, $record) {
                        $data = json_decode($value, true);
                        if (!is_array($data)) {
                            return $value;
                        }
                        return $this->buildSqlQueriesLog($data, $record['duration']);
                    }),
                'checkpoints'
            ]);
    }

    protected function buildSqlQueriesLog(array $data, $totalDuration) {
        $ret = "
            <div>
                SQL queries executed: {$data['statements_count']} 
                / Total Duration: {$data['total_duration']} 
                / Peak memory: {$data['max_memory_usage']}
            </div>
            ";
        $index = 1;
        $prevStatement = [
            'started_at' => 0.00
        ];
        foreach ($data['statements'] as $statement) {
            $delta = (float)$statement['started_at'] - (float)$prevStatement['started_at'];
            $ret .= "<hr class='mv10'>PHP: <b>{$delta}s</b> ({$prevStatement['started_at']} -> {$statement['started_at']})<hr class='mv10'>";
            $query = htmlentities($statement['query']);
            $ret .= "
                <div>
                    <b>{$index}.</b> 
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
            $prevStatement = $statement;
            $index++;
        }
        $diffToEnd = (float)$totalDuration - (float)$prevStatement['started_at'];
        $ret .= "<hr class='mv10'>PHP: <b>{$diffToEnd}s</b> ({$prevStatement['started_at']} -> {$totalDuration}s)" ;
        return $ret;
    }
    
}