<?php

namespace PeskyCMF\Db\HttpRequestStats;

use App\Db\AbstractTable;

class HttpRequestStatsTable extends AbstractTable {

    /**
     * @return HttpRequestStatsTableStructure
     */
    public function getTableStructure() {
        return HttpRequestStatsTableStructure::getInstance();
    }

    /**
     * @return HttpRequestStat
     */
    public function newRecord() {
        return new HttpRequestStat();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'HttpRequestStats';
    }

}
