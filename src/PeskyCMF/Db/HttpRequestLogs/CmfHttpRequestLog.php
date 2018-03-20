<?php

namespace PeskyCMF\Db\HttpRequestLogs;

use App\Db\AbstractRecord;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TempRecord;
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
 * @property-read object      $request_as_object
 * @property-read null|string $response
 * @property-read null|string $debug
 * @property-read null|string $table
 * @property-read null|int    $item_id
 * @property-read null|string $data_before
 * @property-read array       $data_before_as_array
 * @property-read object      $data_before_as_object
 * @property-read null|string $data_after
 * @property-read array       $data_after_as_array
 * @property-read object      $data_after_as_object
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
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setRespondedAt($value, $isFromDb = false)
 */
class CmfHttpRequestLog extends AbstractRecord implements ScaffoldLoggerInterface {

    static private $serverDataKeys = [
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

    /**
     * @return CmfHttpRequestLogsTable
     */
    static public function getTable() {
        return CmfHttpRequestLogsTable::getInstance();
    }

    /**
     * @param Request $request
     * @param bool $force - create log forcefully ignoring all restrictions
     * @return $this
     */
    public function fromRequest(Request $request, $force = false) {
        if ($this->hasValue('request')) {
            throw new \BadMethodCallException('You should not call this method twice');
        }
        try {
            $route = $request->route();
            $logName = $this->getLogName($route);
            if (empty($logName)) {
                if ($force && $logName !== false) {
                    $logName = $route->uri();
                } else {
                    return $this;
                }
            }

            $requestData = [
                'GET' => $this->hidePasswords($request->query()),
                'POST' => $this->hidePasswords($request->input()),
                'SERVER' => array_intersect_key($request->server(), array_flip(static::$serverDataKeys))
            ];
            $this
                ->setUrl('/' . $request->path())
                ->setHttpMethod($request->getMethod())
                ->setRequest($requestData)
                ->setIp($request->ip())
                ->setFilter($logName)
                ->setSection(array_get($route->getAction(), 'prefix', 'web'))
            ;
        } catch (\Exception $exception) {
            \Log::error($exception);
            $this->reset();
        }
        return $this;
    }

    protected function hidePasswords(array $data) {
        return hidePasswords($data);
    }

    /**
     * @param Route $route
     * @return string|null|false
     */
    protected function getLogName(Route $route) {
        return array_get($route->getAction(), 'log', function () use ($route) {
            if ($route->hasParameter('table_name')) {
                $logName = $route->parameter('table_name');
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

    /**
     * @param Request $request
     * @param Response $response
     * @param RecordInterface $user
     * @return $this
     */
    public function logResponse(Request $request, Response $response, RecordInterface $user = null) {
        if ($this->isAllowed() || ($response->getStatusCode() >= 500)) {
            if (!$this->hasValue('request')) {
                // server error happened on not loggable request
                $this->fromRequest($request, true);
            }
            try {
                if ($this->hasValue('response')) {
                    throw new \BadMethodCallException('You should not call this method twice');
                }
                if (!empty($user) && $user->existsInDb()) {
                    $this
                        ->setRequesterTable($user::getTable()->getTableStructure()->getTableName())
                        ->setRequesterId($user->getPrimaryKeyValue())
                        ->setRequesterInfo($this->findRequesterInfo($user));
                } else {
                    $this->setRequesterInfo(array_get(
                        $this->request_as_array,
                        'POST.email',
                        function () {
                            return array_get($this->request_as_array, 'POST.login');
                        }
                    ));
                }
                $this
                    ->setResponse($response->getContent())
                    ->setResponseCode($response->getStatusCode())
                    ->setResponseType(strtolower(preg_replace('%(Response|Cmf)%', '', class_basename($response))) ?: 'text')
                    ->setRespondedAt(static::getTable()->getCurrentTimeDbExpr())
                    ->save();
            } catch (\Exception $exception) {
                \Log::error($exception);
            }
        }
        return $this;
    }

    /**
     * @param RecordInterface $user
     * @return null|string
     */
    protected function findRequesterInfo(RecordInterface $user) {
        try {
            if (!empty($user->email)) {
                return $user->email;
            } else if (!empty($user->login)) {
                return $user->login;
            } else if (!empty($user->name) || !empty($user->first_name)) {
                $name = empty($user->name) ? $user->first_name : $user->name;
                if (!empty($user->surname)) {
                    $name .= ' ' . $user->surname;
                }
                if (!empty($user->last_name)) {
                    $name .= ' ' . $user->last_name;
                }
                return $name;
            }
        } catch (\Exception $exception) {
            \Log::error($exception, [
                'user class' => get_class($user),
                'pk value' => $user->getAuthIdentifier()
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
    public function logDbRecordBeforeChange(RecordInterface $record, $tableName = null, array $columnsToLog = null, array $relationsToLog = null) {
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
                \Log::error($exception);
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
    public function logDbRecordAfterChange(RecordInterface $record, array $columnsToLog = null, array $relationsToLog = null) {
        if ($this->isAllowed()) {
            try {
                if ($columnsToLog !== null) {
                    $columnsToLog[] = $record::getTable()->getTableStructure()->getPkColumnName();
                } else {
                    $columnsToLog = [];
                }
                $this->setDataAfter($record->existsInDb() ? $record->toArray($columnsToLog, $relationsToLog ?: ['*']) : []);
            } catch (\Exception $exception) {
                \Log::error($exception);
            }
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @param null|string $tableName - for cases when table name differs from record's table name (so-called table name for routes)
     * @return $this
     */
    public function logDbRecordUsage(RecordInterface $record, $tableName = null) {
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
                \Log::error($exception);
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowed() {
        return $this->hasValue('request');
    }

}
