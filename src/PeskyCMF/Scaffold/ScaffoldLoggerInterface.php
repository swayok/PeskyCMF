<?php

namespace PeskyCMF\Scaffold;

use PeskyORM\ORM\RecordInterface;

interface ScaffoldLoggerInterface {

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record);

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record);

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordUsage(RecordInterface $record);
}