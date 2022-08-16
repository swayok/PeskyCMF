<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORMColumns\TableStructureTraits\IdColumn;

/**
 * @property-read Column $id
 * @property-read Column $http_method
 * @property-read Column $url
 * @property-read Column $route
 * @property-read Column $created_at
 * @property-read Column $duration
 * @property-read Column $duration_sql
 * @property-read Column $duration_error
 * @property-read Column $memory_usage_mb
 * @property-read Column $is_cache
 * @property-read Column $url_params
 * @property-read Column $sql
 * @property-read Column $http_code
 * @property-read Column $request_data
 * @property-read Column $checkpoints
 * @property-read Column $counters
 */
class CmfHttpRequestStatsTableStructure extends CmfDbTableStructure
{
    
    use IdColumn;
    
    public static function getTableName(): string
    {
        return 'http_request_stats';
    }
    
    private function http_method(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }
    
    private function url(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }
    
    private function request_data(): Column
    {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }
    
    private function route(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }
    
    private function duration(): Column
    {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }
    
    private function duration_sql(): Column
    {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }
    
    private function duration_error(): Column
    {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues()
            ->setDefaultValue(0);
    }
    
    private function memory_usage_mb(): Column
    {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }
    
    private function is_cache(): Column
    {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->setDefaultValue(false);
    }
    
    private function url_params(): Column
    {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }
    
    private function sql(): Column
    {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }
    
    private function checkpoints(): Column
    {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }
    
    private function counters(): Column
    {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }
    
    private function http_code(): Column
    {
        return Column::create(Column::TYPE_INT)
            ->disallowsNullValues();
    }
    
    private function created_at(): Column
    {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->disallowsNullValues()
            ->setDefaultValue(DbExpr::create('NOW()'));
    }
    
}
