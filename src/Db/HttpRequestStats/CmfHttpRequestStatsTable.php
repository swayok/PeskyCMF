<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyCMF\Db\CmfDbTable;

class CmfHttpRequestStatsTable extends CmfDbTable
{
    
    public function getTableStructure(): CmfHttpRequestStatsTableStructure
    {
        return CmfHttpRequestStatsTableStructure::getInstance();
    }
    
    public function newRecord(): CmfHttpRequestStat
    {
        return new CmfHttpRequestStat();
    }
    
    public function getTableAlias(): string
    {
        return 'HttpRequestStats';
    }
    
}
