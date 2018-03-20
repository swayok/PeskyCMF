<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyCMF\Db\TableStructureTraits\AdminIdColumn;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableStructure;
use PeskyORMLaravel\Db\TableStructureTraits\IdColumn;

/**
 * @property-read Column    $id
 * @property-read Column    $requester_class
 * @property-read Column    $requester_id
 * @property-read Column    $requester_info
 * @property-read Column    $url
 * @property-read Column    $http_method
 * @property-read Column    $ip
 * @property-read Column    $filter
 * @property-read Column    $section
 * @property-read Column    $response_code
 * @property-read Column    $response_type
 * @property-read Column    $request
 * @property-read Column    $response
 * @property-read Column    $debug
 * @property-read Column    $table
 * @property-read Column    $item_id
 * @property-read Column    $data_before
 * @property-read Column    $data_after
 * @property-read Column    $created_at
 * @property-read Column    $responded_at
 */
class CmfHttpRequestLogsTableStructure extends TableStructure {

    use IdColumn;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'http_request_logs';
    }

    private function requester_class() {
        return Column::create(Column::TYPE_STRING);
    }

    private function requester_id() {
        return Column::create(Column::TYPE_INT);
    }

    private function requester_info() {
        return Column::create(Column::TYPE_STRING)
            ->trimsValue();
    }

    private function url() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function http_method() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function ip() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function filter() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function section() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues();
    }

    private function response_code() {
        return Column::create(Column::TYPE_INT);
    }

    private function response_type() {
        return Column::create(Column::TYPE_STRING);
    }

    private function request() {
        return Column::create(Column::TYPE_JSON)
            ->disallowsNullValues();
    }

    private function response() {
        return Column::create(Column::TYPE_TEXT);
    }

    private function debug() {
        return Column::create(Column::TYPE_TEXT);
    }

    private function table() {
        return Column::create(Column::TYPE_STRING);
    }

    private function item_id() {
        return Column::create(Column::TYPE_INT);
    }

    private function data_before() {
        return Column::create(Column::TYPE_JSON);
    }

    private function data_after() {
        return Column::create(Column::TYPE_JSON);
    }

    private function created_at() {
        return Column::create(Column::TYPE_TIMESTAMP_WITH_TZ)
            ->valueCannotBeSetOrChanged();
    }

    private function responded_at() {
        return Column::create(Column::TYPE_TIMESTAMP_WITH_TZ);
    }

}
