<?php

declare(strict_types=1);

namespace PeskyCMF\Db\HttpRequestLogs;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\DbExpr;
use PeskyORM\ORM\Record\Record;
use PeskyORM\ORM\Record\RecordInterface;
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
 * @property-read string      $creation_date
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
class CmfHttpRequestLog extends Record implements ScaffoldLoggerInterface
{
    /** @var \Closure[] */
    protected static array $requestDataMinifiers = [];
    /** @var \Closure[] */
    protected static array $responseContentMinifiers = [];

    protected static array $serverDataKeys = [
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

    protected ?\Closure $responseContentMinifier = null;
    protected ?\Closure $requestDataMinifier = null;
    protected bool $ignoreResponseLogging = false;

    public function __construct()
    {
        parent::__construct(CmfHttpRequestLogsTable::getInstance());
    }

    /**
     * Register request data minifier that may be used by during request logging via
     * route's 'log_data_minifier' action.
     */
    public static function registerRequestDataMinifier(string $name, \Closure $minifier): void
    {
        static::$requestDataMinifiers[$name] = $minifier;
    }

    /**
     * Register response data minifier that may be used by during request logging via
     * route's 'log_response' action.
     */
    public static function registerResponseContentMinifier(string $name, \Closure $minifier): void
    {
        static::$responseContentMinifiers[$name] = $minifier;
    }

    public function fromRequest(Request $request, bool $enabledByDefault = false, bool $force = false): ?static
    {
        if ($this->hasValue('request')) {
            throw new \BadMethodCallException('You should not call this method twice');
        }
        try {
            $route = $request->route();
            $logName = $this->getLogName($route, $force, $enabledByDefault);
            if (!$logName) {
                return null;
            }

            $files = array_map(static function ($file) {
                if ($file instanceof UploadedFile) {
                    $fileExists = !empty($file->getPathname()) && File::exist($file->getPathname());
                    return [
                        'name' => $file->getClientOriginalName(),
                        'path' => $file->getPathname(),
                        'size' => $fileExists ? $file->getSize() : -1,
                        'type' => $fileExists ? $file->getMimeType() : null,
                        'error' => $file->getError(),
                        'error_message' => $file->getError() !== 0 ? $file->getErrorMessage() : '',
                    ];
                }

                if ($file instanceof \SplFileInfo) {
                    $fileExists = !empty($file->getPathname()) && File::exist($file->getPathname());
                    return [
                        'name' => $file->getPathname(),
                        'size' => $fileExists ? $file->getSize() : -1,
                        'type' => $fileExists ? $file->getType() : null,
                        'error' => $fileExists ? 0 : -1,
                        'error_message' => $fileExists ? 'File not exists' : '',
                    ];
                }

                return $file;
            }, $request->file());

            // set data minifier from route's 'log_data_minifier' action if provided and registered
            if (!$this->requestDataMinifier) {
                $requestMinifierName = Arr::get($route->getAction(), 'log_data_minifier');
                if (
                    !empty($requestMinifierName)
                    && isset(static::$requestDataMinifiers[$requestMinifierName])
                ) {
                    $this->setRequestDataMinifier(
                        static::$requestDataMinifiers[$requestMinifierName]
                    );
                }
            }
            // set data minifier from route's 'log_response' action if provided and registered
            if (!$this->responseContentMinifier) {
                $responseMinifierName = Arr::get($route->getAction(), 'log_response');
                if (
                    !empty($responseMinifierName)
                    && isset(static::$responseContentMinifiers[$responseMinifierName])
                ) {
                    $this->setResponseContentMinifier(
                        static::$responseContentMinifiers[$responseMinifierName]
                    );
                } elseif ($responseMinifierName === false) {
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
                'HEADERS' => array_map(static function ($value) {
                    if (is_array($value) && count($value) === 1 && isset($value[0])) {
                        return $value[0];
                    }
                    return $value;
                }, $request->header()),
                'SERVER' => array_intersect_key(
                    $request->server(),
                    array_flip(static::$serverDataKeys)
                ),
            ];
            $this
                ->setUrl('/' . $request->path())
                ->setHttpMethod($request->getMethod())
                ->setRequest($requestData)
                ->setIp($request->ip())
                ->setFilter($logName)
                ->setSection($this->getSectionName($route))
                ->save();
            $this->begin(); //< to start collecting changes until logResponse() is called
        } catch (\Exception $exception) {
            $this->logException($exception);
            $this->reset();
        }
        return $this;
    }

    protected function hidePasswords(array $data): array
    {
        return hidePasswords($data);
    }

    /**
     * Set minifier closure to reduce size of request data to be logged.
     * Useful for heavy requests that contain lots of data or heavy data like files.
     *
     * @param \Closure $minifier - function (array $data) { return $data; }
     */
    public function setRequestDataMinifier(\Closure $minifier): static
    {
        $this->requestDataMinifier = $minifier;
        return $this;
    }

    protected function getMinifiedRequestData(array $data): array
    {
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
                array_walk_recursive($data, static function (&$value) use ($maxSize) {
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
     * @param bool  $forceLogging - true: ignores false returned from $this->getCustomLogName($route)
     * @param bool  $enabledByDefault - true: if log name not provided $this->getCustomLogName($route)
     *
     * @return string|null
     */
    protected function getLogName(
        Route $route,
        bool $forceLogging,
        bool $enabledByDefault
    ): ?string {
        $logName = $this->getLogNameFromRouteActions($route, $enabledByDefault, $forceLogging);
        if (is_string($logName)) {
            // custom log name or cmf resource/page props passed to route
            return $logName;
        }
        if (!$logName) {
            // logs disabled and not forced (decided in $this->getLogNameFromRouteActions())
            return null;
        }
        // generate log name from URI
        return $this->normalizeDefaultLogName($route->uri());
    }

    protected function normalizeDefaultLogName(string $logName): string
    {
        return $logName;
    }

    /**
     * Returns:
     * - false: disable logging unless forced;
     * - true: need to generate log name automatically;
     * - string: custom log name.
     */
    protected function getLogNameFromRouteActions(
        Route $route,
        bool $enabledByDefault,
        bool $forceLogging
    ): bool|string|null {
        $logName = Arr::get($route->getAction(), 'log');
        if (is_string($logName) && trim($logName) !== '') {
            // custom log name
            return $logName;
        }
        if ($logName === false) {
            // logging is disabled but it may be forced,
            // so we use $forceLogging as return value
            // ($forceLogging === true will autogenerate log name)
            return $forceLogging;
        }
        if (
            ($logName === null || is_string($logName))
            && !$enabledByDefault
            && !$forceLogging
        ) {
            // logging is disabled by default and not forced so
            // empty string or null disable logging
            return false;
        }
        // try to generate log name automatically from route params
        if ($route->hasParameter('resource')) {
            $logName = $route->parameter('resource');
            if ($route->hasParameter('id')) {
                $logName .= '.' . $route->parameter('id');
            }
            if ($route->hasParameter('page')) {
                $logName .= '.' . $route->parameter('page');
            }
            return $logName;
        }
        if ($route->hasParameter('page')) {
            return 'page' . $route->parameter('page');
        }
        // logging is allowed but have no name -> autogenerate log name
        return true;
    }

    protected function getSectionName(Route $route): string
    {
        return $route->getAction('prefix') ?: 'web';
    }

    /**
     * Set minifier closure to reduce size of response content to be logged.
     *
     * @param \Closure $minifier - function (Symfony\Component\HttpFoundation\Response $response) { return $content; }
     */
    public function setResponseContentMinifier(\Closure $minifier): static
    {
        $this->responseContentMinifier = $minifier;
        return $this;
    }

    protected function getMinifiedResponseContent(Response $response): string
    {
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

    public function ignoreResponseLogging(): static
    {
        $this->ignoreResponseLogging = true;
        return $this;
    }

    public function logResponse(
        Request $request,
        Response $response,
        ?RecordInterface $user = null
    ): static {
        if ($this->ignoreResponseLogging) {
            return $this;
        }
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

                if (!empty($this->response)) {
                    throw new \BadMethodCallException('You should not call this method twice');
                }

                if (!$this->isCollectingUpdates()) {
                    $this->begin();
                }

                $this
                    ->logRequester($user)
                    ->setResponse($this->getMinifiedResponseContent($response))
                    ->setResponseCode($response->getStatusCode())
                    ->setResponseType(
                        strtolower(preg_replace('%(Response|Cmf)%', '', class_basename($response)))
                            ?: 'text'
                    )
                    ->setRespondedAt(DbExpr::create('NOW()'))
                    ->commit();
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    public function logRequester(?RecordInterface $user = null): static
    {
        if ($this->isAllowed()) {
            if ($user && $user->existsInDb()) {
                $this
                    ->setRequesterTable(
                        $user->getTable()->getTableStructure()->getTableName()
                    )
                    ->setRequesterId($user->getPrimaryKeyValue())
                    ->setRequesterInfo($this->findRequesterInfo($user));
            } elseif (!isset($this->requester_id)) {
                $this->setRequesterInfo(
                    Arr::get(
                        $this->request_as_array,
                        'POST.email',
                        function () {
                            return Arr::get($this->request_as_array, 'POST.login');
                        }
                    )
                );
            }
        }
        return $this;
    }

    /**
     * @noinspection NotOptimalIfConditionsInspection
     */
    protected function findRequesterInfo(RecordInterface $user): ?string
    {
        try {
            $tableStructure = $user->getTable()->getTableStructure();
            if ($tableStructure->hasColumn('email') && !empty($user->email)) {
                return $user->email;
            }
            if ($tableStructure->hasColumn('login') && !empty($user->login)) {
                return $user->login;
            }
            if (
                ($tableStructure->hasColumn('name') && !empty($user->name))
                || ($tableStructure->hasColumn('first_name') && !empty($user->first_name))
            ) {
                $name = $tableStructure->hasColumn('name') && !empty($user->name)
                    ? $user->getValue('name')
                    : $user->getValue('first_name');
                if ($tableStructure->hasColumn('surname') && !empty($user->surname)) {
                    $name .= ' ' . $user->surname;
                } elseif ($tableStructure->hasColumn('last_name') && !empty($user->last_name)) {
                    $name .= ' ' . $user->last_name;
                }
                return $name;
            }
        } catch (\Exception $exception) {
            $this->logException($exception, [
                'user class' => get_class($user),
                'pk value' => $user->getPrimaryKeyValue(),
            ]);
        }
        return null;
    }

    public function logDbRecordBeforeChange(
        RecordInterface $record,
        ?string $tableName = null,
        ?array $columnsToLog = null,
        ?array $relationsToLog = null
    ): static {
        if ($this->isAllowed()) {
            try {
                if ($columnsToLog !== null) {
                    $columnsToLog[] = $record->getTable()
                        ->getTableStructure()
                        ->getPkColumnName();
                } else {
                    $columnsToLog = [];
                }
                $this->logDbRecordUsage($record, $tableName);
                if ($record->existsInDb()) {
                    $this->setDataBefore(
                        $this->normalizeDbRecordData(
                            $record->toArray($columnsToLog, $relationsToLog ?: ['*'])
                        )
                    );
                } else {
                    $this->setDataBefore([]);
                }
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    private function normalizeDbRecordData(array $data): array
    {
        $data = array_filter($data, function ($value) {
            return !is_resource($value);
        });
        ksort($data);
        return $data;
    }

    public function logDbRecordAfterChange(
        RecordInterface $record,
        ?array $columnsToLog = null,
        ?array $relationsToLog = null
    ): static {
        if ($this->isAllowed()) {
            try {
                if ($columnsToLog !== null) {
                    $columnsToLog[] = $record->getTable()
                        ->getTableStructure()
                        ->getPkColumnName();
                } else {
                    $columnsToLog = [];
                }
                if ($record->existsInDb()) {
                    $this->setDataAfter(
                        $this->normalizeDbRecordData(
                            $record->toArray($columnsToLog, $relationsToLog ?: ['*'])
                        )
                    );
                } else {
                    $this->setDataAfter([]);
                }
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    public function logDbRecordUsage(RecordInterface $record, ?string $tableName = null): static
    {
        if ($this->isAllowed()) {
            try {
                if (empty($tableName)) {
                    $tableName = $record->getTable()
                        ->getTableStructure()
                        ->getTableName();
                }
                $this->setTable($tableName);
                $this->setItemId(
                    $record->existsInDb() ? $record->getPrimaryKeyValue() : null
                );
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    public function isAllowed(): bool
    {
        return $this->hasValue('request');
    }

    protected function logException(\Exception $exception, array $context = []): void
    {
        $context['exception'] = $exception;
        Log::critical($exception->getMessage(), $context);
    }

    /**
     * Do not pass complex objects to $value - json_encode might fail
     */
    public function addDebugData(string $key, mixed $value): static
    {
        if ($this->isAllowed()) {
            try {
                $debug = $this->hasValue('debug') ? $this->debug : '';
                $debug .= $key . ': '
                    . json_encode(
                        $value,
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
                        10
                    )
                    . "\n";
                $this->setDebug($debug);
            } catch (\Exception $exception) {
                $this->logException($exception);
            }
        }
        return $this;
    }

    /**
     * Do not pass complex objects as array values - json_encode might fail
     */
    public function addDebugDataFromArray(array $data): static
    {
        if ($this->isAllowed()) {
            foreach ($data as $key => $value) {
                $this->addDebugData($key, $value);
            }
        }
        return $this;
    }
}
