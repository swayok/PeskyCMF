<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use App\Db\AbstractRecord;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TempRecord;
use Swayok\Utils\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read int         $id
 * @property-read string      $requester_table
 * @property-read null|int    $requester_id
 * @property-read string      $requester_info
 * @property-read string      $url
 * @property-read string      $http_method
 * @property-read string      $ip
 * @property-read string      $filter
 * @property-read string      $section
 * @property-read null|int    $response_code
 * @property-read null|string $response_type
 * @property-read string      $request
 * @property-read array       $request_as_array
 * @property-read \stdClass   $request_as_object
 * @property-read null|string $response
 * @property-read null|string $debug
 * @property-read null|string $table
 * @property-read null|int    $item_id
 * @property-read null|string $data_before
 * @property-read array       $data_before_as_array
 * @property-read \stdClass   $data_before_as_object
 * @property-read null|string $data_after
 * @property-read array       $data_after_as_array
 * @property-read \stdClass   $data_after_as_object
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read null|string $responded_at
 * @property-read string      $responded_at_as_date
 * @property-read string      $responded_at_as_time
 * @property-read int         $responded_at_as_unix_ts
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setRequesterTable($value, $isFromDb = false)
 * @method $this    setRequesterId($value, $isFromDb = false)
 * @method $this    setRequesterInfo($value, $isFromDb = false)
 * @method $this    setUrl($value, $isFromDb = false)
 * @method $this    setHttpMethod($value, $isFromDb = false)
 * @method $this    setIp($value, $isFromDb = false)
 * @method $this    setFilter($value, $isFromDb = false)
 * @method $this    setSection($value, $isFromDb = false)
 * @method $this    setResponseCode($value, $isFromDb = false)
 * @method $this    setResponseType($value, $isFromDb = false)
 * @method $this    setRequest($value, $isFromDb = false)
 * @method $this    setResponse($value, $isFromDb = false)
 * @method $this    setDebug($value, $isFromDb = false)
 * @method $this    setTable($value, $isFromDb = false)
 * @method $this    setItemId($value, $isFromDb = false)
 * @method $this    setDataBefore($value, $isFromDb = false)
 * @method $this    setDataAfter($value, $isFromDb = false)
 * @method $this    setRespondedAt($value, $isFromDb = false)
 */
class CmfHttpRequestLog extends AbstractRecord implements ScaffoldLoggerInterface {

    /** @var \Closure[] */
    static protected $requestDataMinifiers = [];
    static protected $responseContentMinifiers = [];

    static protected $serverDataKeys = [
        'HTTP_USER_AGENT',
        'REQUEST_URI',
        'REQUEST_METHOD',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT',
        'HTTP_POSTMAN_TOKEN',
        'HTTP_CONTENT_TYPE',
        'HTTP_CACHE_CONTROL',
        'HTTP_CONNECTION',
        'HTTP_HOST',
        'REMOTE_ADDR',
        'SERVER_ADDR',
        'QUERY_STRING',
        'HTTPS',
        'REQUEST_TIME',
    ];

    /** @var \Closure|null */
    protected $responseContentMinifier;
    /** @var \Closure|null */
    protected $requestDataMinifier;

    /**
     * @return CmfHttpRequestLogsTable
     */
    static public function getTable() {
        return CmfHttpRequestLogsTable::getInstance();
    }

    /**
     * Register request data minifier that may be used by during request logging via
     * route's 'log_data_minifier' action.
     * @param string $name
     * @param \Closure $minifier
     */
    static public function registerRequestDataMinifier(string $name, \Closure $minifier) {
        static::$requestDataMinifiers[$name] = $minifier;
    }
    
    /**
     * Register response data minifier that may be used by during request logging via
     * route's 'log_response' action.
     * @param string $name
     * @param \Closure $minifier
     */
    static public function registerResponseContentMinifier(string $name, \Closure $minifier) {
        static::$responseContentMinifiers[$name] = $minifier;
    }

    /**
     * @param Request $request
     * @param bool $enabledByDefault - create log even when log name not provided via route's 'log' action
     * @param bool $force - create log forcefully ignoring all restrictions
     * @return $this
     */
    public function fromRequest(Request $request, bool $enabledByDefault = false, bool $force = false) {
        if ($this->hasValue('request')) {
            throw new \BadMethodCallException('You should not call this method twice');
        }
        try {
            $route = $request->route();
            $logName = $this->getLogName($route, $force, $enabledByDefault);
            if (!$logName) {
                return null;
            }

            $files = array_map(function ($file) {
                if ($file instanceof UploadedFile) {
                    $fileExists = !empty($file->getPathname()) && File::exist($file->getPathname());
                    return [
                        'name' => $file->getClientOriginalName(),
                        'path' => $file->getPathname(),
                        'size' => $fileExists ? $file->getSize() : -1,
                        'type' => $fileExists ? $file->getMimeType() : null,
                        'error' => $file->getError(),
                        'error_message' => $file->getError() !== 0 ? $file->getErrorMessage() : ''
                    ];
                } else if ($file instanceof \SplFileInfo) {
                    $fileExists = !empty($file->getPathname()) && File::exist($file->getPathname());
                    return [
                        'name' => $file->getPathname(),
                        'size' => $fileExists ? $file->getSize() : -1,
                        'type' => $fileExists ? $file->getType() : null,
                        'error' => $fileExists ? 0 : -1,
                        'error_message' => $fileExists ? 'File not exists' : '',
                    ];
                } else {
                    return $file;
                }
            }, $request->file());

            // set data minifier from rotute's 'log_data_minifier' action if provided and registered
            if (!$this->requestDataMinifier) {
                $requestMinifierName = array_get($route->getAction(), 'log_data_minifier');
                if (!empty($requestMinifierName) && isset(static::$requestDataMinifiers[$requestMinifierName])) {
                    $this->setRequestDataMinifier(static::$requestDataMinifiers[$requestMinifierName]);
                }
            }
            // set data minifier from rotute's 'log_response' action if provided and registered
            if (!$this->responseContentMinifier) {
                $responseMinifierName = array_get($route->getAction(), 'log_response');
                if (!empty($responseMinifierName) && isset(static::$responseContentMinifiers[$responseMinifierName])) {
                    $this->setResponseContentMinifier(static::$responseContentMinifiers[$responseMinifierName]);
                } else if ($responseMinifierName === false) {
                    // do not log response at all
                    $this->setResponseContentMinifier(function () {
                        return '[Response logging disabled]';
                    });
                }
            }

            $requestData = [
                'GET' => $this->getMinifiedRequestData($this->hidePasswords($request->query())),
                'POST' => $this->getMinifiedRequestData($this->hidePasswords($request->post())),
                'FILES' => $files,
                'HEADERS' => array_map(function ($value) {
                    if (is_array($value) && count($value) === 1 && isset($value[0])) {
                        return $value[0];
                    }
                    return $value;
                }, $request->header()),
                'SERVER' => array_intersect_key($request->server(), array_flip(static::$serverDataKeys))
            ];
            $this
                ->setUrl('/' . $request->path())
                ->setHttpMethod($request->getMethod())
                ->setRequest($requestData)
                ->setIp($request->ip())
                ->setFilter($logName)
                ->setSection($this->getSectionName($route))
                ->save();
            $this->begin(); //< to start collecting changes untill logResponse() is called
        } catch (\Exception $exception) {
            $this->logException($exception);
            $this->reset();
        }
        return $this;
    }

    protected function hidePasswords(array $data): array {
        return hidePasswords($data);
    }

    /**
     * Set minifier closure to reduce size of request data to be logged.
     * Useful for heavy requests that contain lots of data or heavy data like files.
     * @param \Closure $minifier - function (array $data) { return $data; }
     * @return $this
     */
    public function setRequestDataMinifier(\Closure $minifier) {
        $this->requestDataMinifier = $minifier;
        return $this;
    }

    protected function getMinifiedRequestData(array $data): array {
        if ($this->requestDataMinifier !== null) {
            try {
                return call_user_func($this->requestDataMinifier, $data);
            } catch (\Throwable $exception) {
                $this->logException($exception);
                // proceed to default version
            }
        }
        if (!empty($data)) {
            $maxSize = (int)config('peskycmf.http_request_logs.max_request_value_size', 0);
            if ($maxSize > 0) {
                array_walk_recursive($data, function (&$value) use ($maxSize) {
                    if (is_string($value) && mb_strlen($value) > $maxSize) {
                        $value = mb_substr($value, 0, $maxSize) . '...( value length limit reached )';
                    }
                });
            }
        }
        return $data;
    }
    
    /**
     * @param Route $route
     * @param bool $forceLogging - true: ignores false returned from $this->getCustomLogName($route)
     * @param bool $enabledByDefault - true: ignores false returned from $this->getCustomLogName($route)
     * @return $this|string
     */
    protected function getLogName(Route $route, bool $forceLogging, bool $enabledByDefault): ?string {
        $logName = $this->getLogNameFromRouteActions($route);
        if (!empty($logName)) {
            return $logName;
        }
        // Situations:
        // = $logName === false: do not log unless $force is true
        // = $logName === null or empty string: could not get name using route params or 'log' action,
        //      will use request URI as $logName when $enabledByDefault is true
        if (!$forceLogging && ($logName === false || !$enabledByDefault)) {
            // do not log
            return null;
        } else {
            return $this->normalizeDefaultLogName($route->uri());
        }
    }
    
    protected function normalizeDefaultLogName(string $logName): string {
        return $logName;
    }

    /**
     * @param Route $route
     * @return string|null|false - false - disable logging until forced; null: no custom log name; string: custom log name
     */
    protected function getLogNameFromRouteActions(Route $route) {
        return array_get($route->getAction(), 'log', function () use ($route) {
            if ($route->hasParameter('resource')) {
                $logName = $route->parameter('resource');
                if ($route->hasParameter('id')) {
                    $logName .= '.' . $route->parameter('id');
                }
                if ($route->hasParameter('page')) {
                    $logName .= '.' . $route->parameter('page');
                }
                return $logName;
            } else if ($route->hasParameter('page')) {
                return 'page' . $route->parameter('page');
            }
            return null;
        });
    }
    
    protected function getSectionName(Route $route): string {
        return $route->getAction('prefix') ?: 'web';
    }

    /**
     * Set minifier closure to reduce size of response content to be logged.
     * @param \Closure $minifier - function (Symfony\Component\HttpFoundation\Response $response) { return $content; }
     * @return $this
     */
    public function setResponseContentMinifier(\Closure $minifier) {
        $this->responseContentMinifier = $minifier;
        return $this;
    }

    protected function getMinifiedResponseContent(Response $response): string {
        if ($this->responseContentMinifier !== null) {
            try {
                return call_user_func($this->responseContentMinifier, $response);
            } catch (\Throwable $exception) {
                $this->logException($exception);
                // proceed to default version
            }
        }
        $responseContent = $response->getContent();
        $maxSize = (int)config('peskycmf.http_request_logs.max_response_size', 3145728);
        return $maxSize > 0 && mb_strlen($responseContent) > $maxSize
            ? mb_substr($responseContent, 0, $maxSize) . ' ( value length limit reached )'
            : $responseContent;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param RecordInterface $user
     * @return $this
     */
    public function logResponse(Request $request, Response $response, ?RecordInterface $user = null) {
        if ($this->isAllowed() || ($response->getStatusCode() >= 500)) {
            try {
                if (!$this->hasValue('request')) {
                    // server error happened on not loggable request
                    $this->fromRequest($request, true, true);
                    if (!$this->existsInDb()) {
                        // something wrong with database connection
                        return $this;
                    }
                }
            
                if ($this->hasValue('response') && !empty($this->response)) {
                    throw new \BadMethodCallException('You should not call this method twice');
                }
                
                if (!$this->isCollectingUpdates()) {
                    $this->begin();
                }
                
                $this
                    ->logRequester($user)
                    ->setResponse($this->getMinifiedResponseContent($response))
                    ->setResponseCode($response->getStatusCode())
                    ->setResponseType(strtolower(preg_replace('%(Response|Cmf)%', '', class_basename($response))) ?: 'text')
                    ->setRespondedAt(static::getTable()->getCurrentTimeDbExpr())
                    ->commit();
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }
    
    /**
     * @param RecordInterface|null $user
     * @return $this
     */
    public function logRequester(?RecordInterface $user = null) {
        if ($this->isAllowed()) {
            if (!empty($user) && $user->existsInDb()) {
                $this
                    ->setRequesterTable($user::getTable()->getTableStructure()->getTableName())
                    ->setRequesterId($user->getPrimaryKeyValue())
                    ->setRequesterInfo($this->findRequesterInfo($user));
            } else if (!isset($this->requester_id)) {
                $this->setRequesterInfo(array_get(
                    $this->request_as_array,
                    'POST.email',
                    function () {
                        return array_get($this->request_as_array, 'POST.login');
                    }
                ));
            }
        }
        return $this;
    }

    /**
     * @param RecordInterface $user
     * @return null|string
     * @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function findRequesterInfo(RecordInterface $user): ?string {
        try {
            if ($user::hasColumn('email') && !empty($user->email)) {
                return $user->email;
            } else if ($user::hasColumn('login') && !empty($user->login)) {
                return $user->login;
            } else if (
                ($user::hasColumn('name') && !empty($user->name))
                || ($user::hasColumn('first_name') && !empty($user->first_name))
            ) {
                $name = $user::hasColumn('name') && !empty($user->name) ? $user->name : $user->first_name;
                if ($user::hasColumn('surname') && !empty($user->surname)) {
                    $name .= ' ' . $user->surname;
                } else if ($user::hasColumn('last_name') && !empty($user->last_name)) {
                    $name .= ' ' . $user->last_name;
                }
                return $name;
            }
        } catch (\Exception $exception) {
            $this->logException($exception, [
                'user class' => get_class($user),
                'pk value' => $user->getPrimaryKeyValue()
            ]);
        }
        return null;
    }

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes)
     * @param array|null $columnsToLog - list of columns to store within Log
     * @param array|null $relationsToLog - list of loaded relations to store within Log (default: all loaded relations)
     * @return $this
     */
    public function logDbRecordBeforeChange(
        RecordInterface $record,
        ?string $tableName = null,
        array $columnsToLog = null,
        array $relationsToLog = null
    ) {
        if ($this->isAllowed()) {
            try {
                if ($columnsToLog !== null) {
                    $columnsToLog[] = $record::getTable()->getTableStructure()->getPkColumnName();
                } else {
                    $columnsToLog = [];
                }
                $this
                    ->logDbRecordUsage($record, $tableName)
                    ->setDataBefore($record->existsInDb() ? $record->toArray($columnsToLog, $relationsToLog ?: ['*']) : []);
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @param array|null $columnsToLog - list of columns to store within Log
     * @param array|null $relationsToLog - list of loaded relations to store within Log (default: all loaded relations)
     * @return $this
     */
    public function logDbRecordAfterChange(
        RecordInterface $record,
        array $columnsToLog = null,
        array $relationsToLog = null
    ) {
        if ($this->isAllowed()) {
            try {
                if ($columnsToLog !== null) {
                    $columnsToLog[] = $record::getTable()->getTableStructure()->getPkColumnName();
                } else {
                    $columnsToLog = [];
                }
                $this->setDataAfter($record->existsInDb() ? $record->toArray($columnsToLog, $relationsToLog ?: ['*']) : []);
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }
    
    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes)
     * @return $this
     */
    public function logDbRecordUsage(RecordInterface $record, ?string $tableName = null) {
        if ($this->isAllowed()) {
            try {
                if (empty($tableName)) {
                    $tableName = $record instanceof TempRecord
                        ? $record->getTableName()
                        : $record::getTable()->getTableStructure()->getTableName();
                }
                $this->setTable($tableName);
                if (!($record instanceof TempRecord)) {
                    $this->setItemId($record->existsInDb() ? $record->getPrimaryKeyValue() : null);
                }
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    public function isAllowed(): bool {
        return $this->hasValue('request');
    }

    protected function logException(\Exception $exception, array $context = []) {
        $context['exception'] = $exception;
        \Log::critical($exception->getMessage(), $context);
    }

    /**
     * @param string $key
     * @param mixed $value - no objects supported!!
     * @return $this
     */
    public function addDebugData(string $key, $value) {
        if ($this->isAllowed()) {
            try {
                $debug = $this->hasValue('debug') ? $this->debug : '';
                $debug .= $key . ': ' . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
                $this->setDebug($debug);
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }
    
    /**
     * @param array $data
     * @return $this
     */
    public function addDebugDataFromArray(array $data) {
        if ($this->isAllowed()) {
            foreach ($data as $key => $value) {
                $this->addDebugData($key, $value);
            }
        }
        return $this;
    }

}
