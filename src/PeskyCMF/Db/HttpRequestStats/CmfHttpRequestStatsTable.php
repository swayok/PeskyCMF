<?php

namespace PeskyCMF\Db\HttpRequestStats;

use App\Db\AbstractTable;

class CmfHttpRequestStatsTable extends AbstractTable {

    /**
     * @return CmfHttpRequestStatsTableStructure
     */
    public function getTableStructure() {
        return CmfHttpRequestStatsTableStructure::getInstance();
    }

    /**
     * @return CmfHttpRequestStat
     */
    public function newRecord() {
        return new CmfHttpRequestStat();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'HttpRequestStats';
    }

}
