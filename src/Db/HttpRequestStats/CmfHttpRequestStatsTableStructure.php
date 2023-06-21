<?php
/** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyORM\ORM\TableStructure\TableStructure;

class CmfHttpRequestStatsTableStructure extends TableStructure
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

    protected function registerColumns(): void
    {
        // TODO: Implement registerColumns() method.
    }

    protected function registerRelations(): void
    {
        // TODO: Implement registerRelations() method.
    }
}
