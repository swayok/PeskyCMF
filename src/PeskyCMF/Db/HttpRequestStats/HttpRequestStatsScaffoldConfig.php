<?php

namespace PeskyCMF\Db\HttpRequestStats;

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
            ->readRelations([
                
            ])
            ->setOrderBy('id', 'desc')
            ->setColumns([
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
            ])
            ->setIsBulkItemsDeleteAllowed(true);
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
            ->readRelations([
                
            ])
            ->setValueCells([
                'id',
                'http_method',
                'url',
                'route',
                'duration',
                'duration_sql',
                'memory_usage_mb',
                'is_cache',
                'url_params',
                'sql',
                'http_code',
                'created_at',
            ]);
    }
    
}