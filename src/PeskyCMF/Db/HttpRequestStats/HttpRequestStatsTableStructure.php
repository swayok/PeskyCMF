<?php

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyCMF\Db\CmfDbTableStructure;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;

/**
 * @property-read Column    $id
 * @property-read Column    $http_method
 * @property-read Column    $url
 * @property-read Column    $route
 * @property-read Column    $created_at
 * @property-read Column    $duration
 * @property-read Column    $duration_sql
 * @property-read Column    $memory_usage_mb
 * @property-read Column    $is_cache
 * @property-read Column    $url_params
 * @property-read Column    $sql
 * @property-read Column    $http_code
 * @property-read Column    $request_data
 * @property-read Column    $checkpoints
 */
class HttpRequestStatsTableStructure extends CmfDbTableStructure {

    use IdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'http_request_stats';
    }

    private function http_method() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function url() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function request_data() {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }

    private function route() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function duration() {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }

    private function duration_sql() {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }

    private function memory_usage_mb() {
        return Column::create(Column::TYPE_FLOAT)
            ->disallowsNullValues();
    }

    private function is_cache() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->setDefaultValue(false);
    }

    private function url_params() {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }

    private function sql() {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }

    private function checkpoints() {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }

    private function http_code() {
        return Column::create(Column::TYPE_INT)
            ->disallowsNullValues();
    }

    private function created_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->disallowsNullValues()
            ->setDefaultValue(DbExpr::create('NOW()'));
    }

}
