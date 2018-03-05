<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use App\Db\AbstractTable;
use Illuminate\Http\Request;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

class CmfHttpRequestLogsTable extends AbstractTable {


    /** @var CmfHttpRequestLog */
    static private $currentLog;
    /** @var RecordInterface|null */
    static private $currentLogRecord;
    /** @var null|array */
    static private $columnsToLog;
    /** @var null|array */
    static private $relationsToLog;

    /**
     * @return CmfHttpRequestLogsTableStructure
     */
    public function getTableStructure() {
        return CmfHttpRequestLogsTableStructure::getInstance();
    }

    /**
     * @return CmfHttpRequestLog
     */
    public function newRecord() {
        return new CmfHttpRequestLog();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'HttpRequestLogs';
    }

    /**
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getCurrentLog() {
        if (!static::$currentLog) {
            static::$currentLog = static::getInstance()->newRecord();
        }
        return static::$currentLog;
    }

    /**
     * @param Request $request
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function logRequest(Request $request) {
        return static::getCurrentLog()->fromRequest($request);
    }

    /**
     * @param Response $response
     * @param CmfAdmin|null $admin
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function logResponse(Request $request, Response $response, CmfAdmin $admin = null) {
        return static::getCurrentLog()->logResponse($request, $response, $admin);
    }

    /**
     * @param RecordInterface $record
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function logDbRecordBeforeChange(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null) {
        static::$currentLogRecord = $record;
        static::$columnsToLog = $columnsToLog;
        static::$relationsToLog = $relationsToLog;
        return static::getCurrentLog()->logDbRecordBeforeChange($record, null, $columnsToLog, $relationsToLog);
    }

    /**
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function logDbRecordAfterChange() {
        return static::getCurrentLog()->logDbRecordAfterChange(static::$currentLogRecord, static::$columnsToLog, static::$relationsToLog);
    }

    /**
     * @param RecordInterface $record
     * @return CmfHttpRequestLog
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function logDbRecordUsage(RecordInterface $record) {
        return static::getCurrentLog()->logDbRecordUsage($record);
    }

}
