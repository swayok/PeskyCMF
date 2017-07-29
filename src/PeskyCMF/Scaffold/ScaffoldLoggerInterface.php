<?php

namespace PeskyCMF\Scaffold;

use PeskyORM\ORM\RecordInterface;

interface ScaffoldLoggerInterface {

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes)
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record, $tableName = null);

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record);

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes)
     * @return $this
     */
    public function logDbRecordUsage(RecordInterface $record, $tableName = null);
}