<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use Illuminate\Http\Request;
use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

class CmfHttpRequestLogsTable extends CmfDbTable
{
    
    protected ?CmfHttpRequestLog $currentLog = null;
    protected ?RecordInterface $recordToTrack = null;
    protected ?array $columnsToLog = null;
    protected ?array $relationsToLog = null;
    
    public function getTableStructure(): CmfHttpRequestLogsTableStructure
    {
        return CmfHttpRequestLogsTableStructure::getInstance();
    }
    
    public function newRecord(): CmfHttpRequestLog
    {
        return new CmfHttpRequestLog();
    }
    
    public function getTableAlias(): string
    {
        return 'HttpRequestLogs';
    }
    
    public static function getCurrentLog(): CmfHttpRequestLog
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
        $instance->recordToTrack = null;
        $instance->columnsToLog = null;
        $instance->relationsToLog = null;
    }
    
    /**
     * Minify response content.
     * Useful for heavy responses that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (Request $request) { return $content; }
     */
    public static function setResponseContentMinifier(\Closure $minifier): CmfHttpRequestLog
    {
        return static::getCurrentLog()->setResponseContentMinifier($minifier);
    }
    
    /**
     * Minify request data.
     * Useful for heavy requests that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (array $data) { return $data; }
     */
    public static function setRequestDataMinifier(\Closure $minifier): CmfHttpRequestLog
    {
        return static::getCurrentLog()->setRequestDataMinifier($minifier);
    }
    
    /**
     * Register request data minifier that may be used by during request logging via
     * route's 'log_data_minifier' action.
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
    public static function logRequest(Request $request, bool $force = false): ?CmfHttpRequestLog
    {
        return static::getCurrentLog()->fromRequest($request, $force);
    }
    
    public static function logResponse(Request $request, Response $response, ?RecordInterface $user = null): CmfHttpRequestLog
    {
        static::logDbRecordAfterChange();
        return static::getCurrentLog()->logResponse($request, $response, $user);
    }
    
    /**
     * Response will not be logged
     */
    public static function ignoreResponseLogging(): CmfHttpRequestLog
    {
        return static::getCurrentLog()->ignoreResponseLogging();
    }
    
    public static function logRequester(?RecordInterface $user = null): CmfHttpRequestLog
    {
        return static::getCurrentLog()->logRequester($user);
    }
    
    public static function logDbRecordBeforeChange(
        RecordInterface $record,
        ?array $columnsToLog = null,
        ?array $relationsToLog = null
    ): CmfHttpRequestLog {
        $instance = static::getInstance();
        $instance->recordToTrack = $record;
        $instance->columnsToLog = $columnsToLog;
        $instance->relationsToLog = $relationsToLog;
        return static::getCurrentLog()->logDbRecordBeforeChange($record, null, $columnsToLog, $relationsToLog);
    }
    
    public static function logDbRecordAfterChange(): CmfHttpRequestLog
    {
        $instance = static::getInstance();
        $currentLog = static::getCurrentLog();
        if (!isset($currentLog->data_after) && $instance->recordToTrack) {
            return $currentLog->logDbRecordAfterChange(
                $instance->recordToTrack,
                $instance->columnsToLog,
                $instance->relationsToLog
            );
        }
        return $currentLog;
    }
    
    public static function logDbRecordCreation(
        RecordInterface $record,
        ?array $columnsToLog = null,
        ?array $relationsToLog = null
    ): CmfHttpRequestLog {
        $instance = static::getInstance();
        $instance->recordToTrack = $record;
        $instance->columnsToLog = $columnsToLog;
        $instance->relationsToLog = $relationsToLog;
        static::getCurrentLog()->logDbRecordUsage($record, null);
        return static::getCurrentLog()->logDbRecordAfterChange($record, $columnsToLog, $relationsToLog);
    }
    
    public static function logDbRecordUsage(RecordInterface $record): CmfHttpRequestLog
    {
        return static::getCurrentLog()->logDbRecordUsage($record);
    }
    
    /**
     * @param string $key
     * @param mixed $value - no objects supported!!
     * @return CmfHttpRequestLog
     */
    public static function addDebugData(string $key, $value): CmfHttpRequestLog
    {
        return static::getCurrentLog()->addDebugData($key, $value);
    }
    
    public static function addDebugDataFromArray(array $data): CmfHttpRequestLog
    {
        return static::getCurrentLog()->addDebugDataFromArray($data);
    }
    
}
