<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use App\Db\AbstractTable;
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

    static public function resetCurrentLog() {
        static::$currentLog = null;
        static::$currentLogRecord = null;
        static::$columnsToLog = null;
        static::$relationsToLog = null;
    }

    /**
     * Minify response content.
     * Useful for heavy responses that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (Request $request) { return $content; }
     * @return CmfHttpRequestLog
     */
    static public function setResponseContentMinifier(\Closure $minifier) {
        return static::getCurrentLog()->setResponseContentMinifier($minifier);
    }

    /**
     * Minify request data.
     * Useful for heavy requests that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (array $data) { return $data; }
     * @return CmfHttpRequestLog
     */
    static public function setRequestDataMinifier(\Closure $minifier) {
        return static::getCurrentLog()->setRequestDataMinifier($minifier);
    }

    /**
     * Register request data minifier that may be used by during request logging via
     * route's 'log_data_minifier' action.
     * @param string $name
     * @param \Closure $minifier
     */
    static public function registerRequestDataMinifier(string $name, \Closure $minifier) {
        static::getCurrentLog()->registerRequestDataMinifier($name, $minifier);
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
     * @param array|null $columnsToLog
     * @param array|null $relationsToLog
     * @return CmfHttpRequestLog
     */
    static public function logDbRecordCreation(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null) {
        static::$currentLogRecord = $record;
        static::$columnsToLog = $columnsToLog;
        static::$relationsToLog = $relationsToLog;
        return static::getCurrentLog()->logDbRecordAfterChange($record, $columnsToLog, $relationsToLog);
    }

    /**
     * @param RecordInterface $record
     * @return CmfHttpRequestLog
     */
    static public function logDbRecordUsage(RecordInterface $record) {
        return static::getCurrentLog()->logDbRecordUsage($record);
    }

    /**
     * @param string $key
     * @param mixed $value - no objects supported!!
     * @return CmfHttpRequestLog
     */
    static public function addDebugData($key, $value) {
        return static::getCurrentLog()->addDebugData($key, $value);
    }

}
