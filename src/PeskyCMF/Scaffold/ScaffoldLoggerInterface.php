<?php

namespace PeskyCMF\Scaffold;

use PeskyORM\ORM\RecordInterface;

interface ScaffoldLoggerInterface {

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes or resource name)
     * @param array|null $columnsToLog - list of columns to store within Log (default: all columns)
     * @param array|null $relationsToLog - list of relations to store within Log (default: all loaded relations)
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record, $tableName = null, array $columnsToLog = null, array $relationsToLog = null);

    /**
     * @param RecordInterface $record
     * @param array $columnsToLog - list of columns to store within Log (default: all columns)
     * @param array|null $relationsToLog - list of relations to store within Log (default: all loaded relations)
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null);

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes or resource name)
     * @return $this
     */
    public function logDbRecordUsage(RecordInterface $record, $tableName = null);
}