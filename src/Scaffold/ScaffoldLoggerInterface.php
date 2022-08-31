<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Http\Request;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

interface ScaffoldLoggerInterface
{
    
    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes or resource name)
     * @param array|null $columnsToLog - list of columns to store within Log (default: all columns)
     * @param array|null $relationsToLog - list of relations to store within Log (default: all loaded relations)
     * @return static
     */
    public function logDbRecordBeforeChange(
        RecordInterface $record,
        ?string $tableName = null,
        array $columnsToLog = null,
        array $relationsToLog = null
    );
    
    /**
     * @param RecordInterface $record
     * @param array|null $columnsToLog - list of columns to store within Log (default: all columns)
     * @param array|null $relationsToLog - list of relations to store within Log (default: all loaded relations)
     * @return static
     */
    public function logDbRecordAfterChange(
        RecordInterface $record,
        ?array $columnsToLog = null,
        ?array $relationsToLog = null
    );
    
    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes or resource name)
     * @return static
     */
    public function logDbRecordUsage(RecordInterface $record, ?string $tableName = null);
    
    /**
     * @param Request $request
     * @param bool $enabledByDefault - create log even when log name not provided via route's 'log' action
     * @param bool $force - create log forcefully ignoring all restrictions
     * @return static|null - null retrned when logging is forbidden for passed $request
     */
    public function fromRequest(Request $request, bool $enabledByDefault = false, bool $force = false);
    
    /**
     * @param Request $request
     * @param Response $response
     * @param RecordInterface|null $user
     * @return static
     */
    public function logResponse(Request $request, Response $response, ?RecordInterface $user = null);
}