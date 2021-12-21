<?php

namespace PeskyCMF\Http\Middleware;

use App\Db\HttpRequestLogs\HttpRequestLog;
use Illuminate\Http\Request;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsTable;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequest {
    
    /**
     * list of logs with urls
     * @var HttpRequestLog[]
     */
    static private array $logs = [];

    /**
     * Middleware examples:
     * 1. Use default auth guard and log all requests with any HTTP method:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class
     * 2. Use custom auth guard and log all requests with any HTTP method:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api'
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api,1'
     * 3. Use custom auth guard and log only requests with 'log' action in route with any HTTP method:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api,0'
     * 4. Use custom auth guard and log all requests with custom HTTP methods list:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api,1,post,put,delete'
     * 5. Use custom auth guard and log only requests with 'log' action in route and custom HTTP methods list:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api,0,post,put,delete'
     * 6. Use default auth guard and log all requests with custom HTTP methods list:
     *      \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':,1,post,put,delete'
     *
     * If you use $enableByDefault = false then to activate logging for a route - add 'log' action to a route.
     * 'log' must be a string and will be recorded to DB in order to group requests by short name like 'user.me'.
     * Route example: Route::get('/user/me', ['log' => 'user.me'])
     *
     * If you use $enableByDefault = true then in order to disable logging you need to set 'log' action to false:
     * Route example: Route::get('/user/me', ['log' => false])
     *
     * Note: if there was a server error during any request - it will be forcefully logged ignoring any restrictions.
     * @param Request $request
     * @param \Closure $next
     * @param null|string $authGuard
     * @param bool $enableByDefault - true: logs requests even if route has no 'log' action in its description
     * @param array $methods - list of HTTP methods to log. If empty - log all methods
     * @return mixed
     */
    public function handle($request, \Closure $next, $authGuard = null, $enableByDefault = true, ...$methods) {
        $isAllowed = (
            empty($methods)
            || preg_match('%' . implode('|', $methods) . '%i', $request->getMethod())
            || !empty(array_get($request->route()->getAction(), 'log'))
        );
        // reset logs to allow requests via test cases
        if (app()->bound(CmfHttpRequestLogsTable::class)) {
            $logsTable = app()->make(CmfHttpRequestLogsTable::class);
        } else {
            $logsTable = CmfHttpRequestLogsTable::getInstance();
        }
        $logKey = $request->getMethod() . ': ' . $request->fullUrl();
        if (isset(static::$logs[$logKey])) {
            // request already logged (sitaution when middleware was declared 2 times - in Kernel and in route)
            // unexpectedly when you use middleware with parameters - each one is called resulting in duplicate logs
            if ($isAllowed && !static::$logs[$logKey]->hasValue('request')) {
                // start logging of existing request if it was not logged yet (in previous call it was not allowed)
                $this->logRequest(static::$logs[$logKey], $request, $enableByDefault);
            }
            // stop here because previous instance of this middleware will handle the rest
            return $next($request);
        }
        $logsTable::resetCurrentLog();
        app()->offsetUnset(ScaffoldLoggerInterface::class);
        $log = $logsTable::getCurrentLog();
        app()->instance(ScaffoldLoggerInterface::class, $log);
        static::$logs[$logKey] = $log;
        if ($isAllowed) {
            $this->logRequest($log, $request, $enableByDefault);
        }
        $response = $next($request);
        if ($response instanceof Response) {
            try {
                if ($response->getStatusCode() === HttpCode::UNAUTHORISED) {
                    $user = null;
                } else {
                    $user = \Auth::guard($authGuard ?: null)->user();
                    if (!($user instanceof RecordInterface)) {
                        $user = null;
                    }
                }
                /** @var RecordInterface|null $user */
                $log->logResponse($request, $response, $user);
            } catch (\Throwable $exception) {
                \Log::error($exception);
            }
        } else {
            \Log::error('LogHttpRequest: cannot log this response (not a Symfony response)', ['response' => $response]);
        }
        return $response;
    }
    
    /**
     * @param HttpRequestLog $log
     */
    protected function logRequest($log, $request, bool $enableByDefault) {
        try {
            $log->fromRequest($request, $enableByDefault);
        } catch (\Throwable $exception) {
            \Log::error($exception);
        }
    }

}
