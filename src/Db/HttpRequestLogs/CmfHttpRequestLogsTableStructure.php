<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableStructure;
use PeskyORMColumns\TableStructureTraits\IdColumn;

/**
 * @property-read Column $id
 * @property-read Column $requester_table
 * @property-read Column $requester_id
 * @property-read Column $requester_info
 * @property-read Column $url
 * @property-read Column $http_method
 * @property-read Column $ip
 * @property-read Column $filter
 * @property-read Column $section
 * @property-read Column $response_code
 * @property-read Column $response_type
 * @property-read Column $request
 * @property-read Column $response
 * @property-read Column $debug
 * @property-read Column $table
 * @property-read Column $item_id
 * @property-read Column $data_before
 * @property-read Column $data_after
 * @property-read Column $created_at
 * @property-read Column $responded_at
 */
class CmfHttpRequestLogsTableStructure extends TableStructure
{
    
    use IdColumn;
    
    public static function getTableName(): string
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
    
}
