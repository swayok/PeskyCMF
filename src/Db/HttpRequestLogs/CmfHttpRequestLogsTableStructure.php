<?php
/** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyORM\ORM\TableStructure\TableStructure;

class CmfHttpRequestLogsTableStructure extends TableStructure
{
    use IdColumn;

    public function getTableName(): string
    {
        return 'http_request_logs';
    }

    private function requester_table(): Column
    {
        return Column::create(Column::TYPE_STRING);
    }

    private function requester_id(): Column
    {
        return Column::create(Column::TYPE_INT);
    }

    private function requester_info(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->trimsValue();
    }

    private function url(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function http_method(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function ip(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function filter(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function section(): Column
    {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function response_code(): Column
    {
        return Column::create(Column::TYPE_INT);
    }

    private function response_type(): Column
    {
        return Column::create(Column::TYPE_STRING);
    }

    private function request(): Column
    {
        return Column::create(Column::TYPE_JSON)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setDefaultValue('{}');
    }

    private function response(): Column
    {
        return Column::create(Column::TYPE_TEXT);
    }

    private function debug(): Column
    {
        return Column::create(Column::TYPE_TEXT);
    }

    private function table(): Column
    {
        return Column::create(Column::TYPE_STRING);
    }

    private function item_id(): Column
    {
        return Column::create(Column::TYPE_INT);
    }

    private function data_before(): Column
    {
        return Column::create(Column::TYPE_JSON);
    }

    private function data_after(): Column
    {
        return Column::create(Column::TYPE_JSON);
    }

    private function created_at(): Column
    {
        return Column::create(Column::TYPE_TIMESTAMP_WITH_TZ)
            ->valueCannotBeSetOrChanged();
    }

    private function responded_at(): Column
    {
        return Column::create(Column::TYPE_TIMESTAMP_WITH_TZ);
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
