<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use App\Db\AbstractTable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
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
     */
    static public function getCurrentLog() {
        if (!static::$currentLog) {
            static::$currentLog = static::getInstance()->newRecord();
        }
        return static::$currentLog;
    }

    /**
     * @param Request $request
     * @param bool $force - log request even if route has no 'log' action in its config
     * @return CmfHttpRequestLog
     */
    static public function logRequest(Request $request, $force = false) {
        return static::getCurrentLog()->fromRequest($request, $force);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param RecordInterface|null $user
     * @return CmfHttpRequestLog
     */
    static public function logResponse(Request $request, Response $response, RecordInterface $user = null) {
        return static::getCurrentLog()->logResponse($request, $response, $user);
    }

    /**
     * @param RecordInterface $record
     * @param array|null $columnsToLog
     * @param array|null $relationsToLog
     * @return CmfHttpRequestLog
     */
    static public function logDbRecordBeforeChange(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null) {
        static::$currentLogRecord = $record;
        static::$columnsToLog = $columnsToLog;
        static::$relationsToLog = $relationsToLog;
        return static::getCurrentLog()->logDbRecordBeforeChange($record, null, $columnsToLog, $relationsToLog);
    }

    /**
     * @return CmfHttpRequestLog
     */
    static public function logDbRecordAfterChange() {
        return static::getCurrentLog()->logDbRecordAfterChange(static::$currentLogRecord, static::$columnsToLog, static::$relationsToLog);
    }

    /**
     * @param RecordInterface $record
     * @return CmfHttpRequestLog
     */
    static public function logDbRecordUsage(RecordInterface $record) {
        return static::getCurrentLog()->logDbRecordUsage($record);
    }

}
