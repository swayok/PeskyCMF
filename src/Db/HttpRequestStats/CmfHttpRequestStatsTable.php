<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyORM\ORM\Table\Table;

class CmfHttpRequestStatsTable extends Table
{
    public function __construct(?string $tableAlias = 'HttpRequestStats')
    {
        parent::__construct(
            new CmfHttpRequestStatsTableStructure(),
            CmfHttpRequestStat::class,
            $tableAlias
        );
    }
}
