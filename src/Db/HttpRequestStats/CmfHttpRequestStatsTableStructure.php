<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestStats;

use PeskyORM\ORM\TableStructure\TableColumn\Column\BooleanColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\CreatedAtColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\FloatColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IdColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IntegerColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\JsonObjectColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\StringColumn;
use PeskyORM\ORM\TableStructure\TableStructure;

class CmfHttpRequestStatsTableStructure extends TableStructure
{
    public function getTableName(): string
    {
        return 'http_request_stats';
    }

    protected function registerColumns(): void
    {
        $this->addColumn(new IdColumn());

        $this->addColumn(
            new StringColumn('http_method')
        );
        $this->addColumn(
            new StringColumn('url')
        );
        $this->addColumn(
            (new JsonObjectColumn('request_data'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            new StringColumn('route')
        );
        $this->addColumn(
            new FloatColumn('duration')
        );
        $this->addColumn(
            new FloatColumn('duration_sql')
        );
        $this->addColumn(
            (new FloatColumn('duration_error'))
                ->setDefaultValue(0)
        );
        $this->addColumn(
            new FloatColumn('memory_usage_mb')
        );
        $this->addColumn(
            (new BooleanColumn('is_cache'))
                ->setDefaultValue(false)
        );
        $this->addColumn(
            (new JsonObjectColumn('url_params'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            (new JsonObjectColumn('sql'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            (new JsonObjectColumn('checkpoints'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            (new JsonObjectColumn('counters'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            new IntegerColumn('http_code')
        );
        $this->addColumn(
            (new CreatedAtColumn())
                ->withTimezone()
        );
    }

    protected function registerRelations(): void
    {
    }
}
