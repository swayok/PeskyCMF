<?php
/** @noinspection PhpUnusedPrivateMethodInspection */

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use PeskyORM\DbExpr;
use PeskyORM\ORM\TableStructure\TableColumn\Column\CreatedAtColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\DateColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IdColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IntegerColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\IpV4AddressColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\JsonObjectColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\StringColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\TextColumn;
use PeskyORM\ORM\TableStructure\TableColumn\Column\TimestampColumn;
use PeskyORM\ORM\TableStructure\TableStructure;

class CmfHttpRequestLogsTableStructure extends TableStructure
{
    public function getTableName(): string
    {
        return 'http_request_logs';
    }

    protected function registerColumns(): void
    {
        $this->addColumn(new IdColumn());
        $this->addColumn(
            (new StringColumn('requester_table'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new IntegerColumn('requester_id'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new StringColumn('requester_info'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new StringColumn('url'))
        );
        $this->addColumn(
            (new StringColumn('http_method'))
        );
        $this->addColumn(
            (new IpV4AddressColumn('ip'))
        );
        $this->addColumn(
            (new StringColumn('filter'))
        );
        $this->addColumn(
            (new StringColumn('section'))
        );
        $this->addColumn(
            (new IntegerColumn('response_code'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new StringColumn('response_type'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new JsonObjectColumn('request'))
                ->setDefaultValue('{}')
        );
        $this->addColumn(
            (new TextColumn('response'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new TextColumn('debug'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new StringColumn('table'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new IntegerColumn('item_id'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new JsonObjectColumn('data_before'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new JsonObjectColumn('data_after'))
                ->allowsNullValues()
        );
        $this->addColumn(
            (new DateColumn('creation_date'))
                ->setDefaultValue(new DbExpr('NOW()::date'))
        );
        $this->addColumn(
            (new CreatedAtColumn())
                ->withTimezone()
        );
        $this->addColumn(
            (new TimestampColumn('responded_at'))
                ->withTimezone()
                ->allowsNullValues()
        );
    }

    protected function registerRelations(): void
    {
    }
}
