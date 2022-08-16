<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use Illuminate\Http\Request;
use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

class CmfHttpRequestLogsTable extends CmfDbTable
{
    
    /** @var CmfHttpRequestLog */
    protected $currentLog;
    /** @var RecordInterface|null */
    protected $currentLogRecord;
    /** @var null|array */
    protected $columnsToLog;
    /** @var null|array */
    protected $relationsToLog;
    
    /**
     * @return CmfHttpRequestLogsTableStructure
     */
    public function getTableStructure()
    {
        return CmfHttpRequestLogsTableStructure::getInstance();
    }
    
    /**
     * @return CmfHttpRequestLog
     */
    public function newRecord()
    {
        return new CmfHttpRequestLog();
    }
    
    public function getTableAlias(): string
    {
        return 'HttpRequestLogs';
    }
    
    /**
     * @return CmfHttpRequestLog
     */
    public static function getCurrentLog()
    {
        $instance = static::getInstance();
        if (!$instance->currentLog) {
            $instance->currentLog = static::getInstance()->newRecord();
        }
        return $instance->currentLog;
    }
    
    public static function resetCurrentLog(): void
    {
        $instance = static::getInstance();
        $instance->currentLog = null;
        $instance->currentLogRecord = null;
        $instance->columnsToLog = null;
        $instance->relationsToLog = null;
    }
    
    /**
     * Minify response content.
     * Useful for heavy responses that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (Request $request) { return $content; }
     * @return CmfHttpRequestLog
     */
    public static function setResponseContentMinifier(\Closure $minifier)
    {
        return static::getCurrentLog()->setResponseContentMinifier($minifier);
    }
    
    /**
     * Minify request data.
     * Useful for heavy requests that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (array $data) { return $data; }
     * @return CmfHttpRequestLog
     */
    public static function setRequestDataMinifier(\Closure $minifier)
    {
        return static::getCurrentLog()->setRequestDataMinifier($minifier);
    }
    
    /**
     * Register request data minifier that may be used by during request logging via
     * route's 'log_data_minifier' action.
     * @param string $name
     * @param \Closure $minifier
     */
    public static function registerRequestDataMinifier(string $name, \Closure $minifier): void
    {
        static::getCurrentLog()->registerRequestDataMinifier($name, $minifier);
    }
    
    /**
     * @param Request $request
     * @param bool $force - log request even if route has no 'log' action in its config
     * @return CmfHttpRequestLog|null
     */
    public static function logRequest(Request $request, bool $force = false)
    {
        return static::getCurrentLog()->fromRequest($request, $force);
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param RecordInterface|null $user
     * @return CmfHttpRequestLog
     */
    public static function logResponse(Request $request, Response $response, ?RecordInterface $user = null)
    {
        $currentLog = static::getCurrentLog();
        if (isset(static::getInstance()->currentLogRecord) && !isset($currentLog->data_after)) {
            static::logDbRecordAfterChange();
        }
        return $currentLog->logResponse($request, $response, $user);
    }
    
    /**
     * Response will not be logged
     * @return CmfHttpRequestLog
     */
    public static function ignoreResponseLogging()
    {
        return static::getCurrentLog()->ignoreResponseLogging();
    }
    
    /**
     * @param RecordInterface|null $user
     * @return CmfHttpRequestLog
     */
    public static function logRequester(?RecordInterface $user = null)
    {
        return static::getCurrentLog()->logRequester($user);
    }
    
    /**
     * @param RecordInterface $record
     * @param array|null $columnsToLog
     * @param array|null $relationsToLog
     * @return CmfHttpRequestLog
     */
    public static function logDbRecordBeforeChange(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null)
    {
        $instance = static::getInstance();
        $instance->currentLogRecord = $record;
        $instance->columnsToLog = $columnsToLog;
        $instance->relationsToLog = $relationsToLog;
        return static::getCurrentLog()->logDbRecordBeforeChange($record, null, $columnsToLog, $relationsToLog);
    }
    
    /**
     * @return CmfHttpRequestLog
     */
    public static function logDbRecordAfterChange()
    {
        $instance = static::getInstance();
        return static::getCurrentLog()->logDbRecordAfterChange(
            $instance->currentLogRecord,
            $instance->columnsToLog,
            $instance->relationsToLog
        );
    }
    
    /**
     * @param RecordInterface $record
     * @param array|null $columnsToLog
     * @param array|null $relationsToLog
     * @return CmfHttpRequestLog
     */
    public static function logDbRecordCreation(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null)
    {
        $instance = static::getInstance();
        $instance->currentLogRecord = $record;
        $instance->columnsToLog = $columnsToLog;
        $instance->relationsToLog = $relationsToLog;
        static::getCurrentLog()->logDbRecordUsage($record, null);
        return static::getCurrentLog()->logDbRecordAfterChange($record, $columnsToLog, $relationsToLog);
    }
    
    /**
     * @param RecordInterface $record
     * @return CmfHttpRequestLog
     */
    public static function logDbRecordUsage(RecordInterface $record)
    {
        return static::getCurrentLog()->logDbRecordUsage($record);
    }
    
    /**
     * @param string $key
     * @param mixed $value - no objects supported!!
     * @return CmfHttpRequestLog
     */
    public static function addDebugData(string $key, $value)
    {
        return static::getCurrentLog()->addDebugData($key, $value);
    }
    
    /**
     * @param array $data
     * @return CmfHttpRequestLog
     */
    public static function addDebugDataFromArray(array $data)
    {
        return static::getCurrentLog()->addDebugDataFromArray($data);
    }
    
}
