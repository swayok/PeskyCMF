<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLog;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsTable;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\RecordInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequest
{
    
    /**
     * list of logs with urls
     * @var CmfHttpRequestLog[]
     */
    private static array $logs = [];
    
    protected Application $app;
    protected LoggerInterface $logger;
    protected AuthFactory $auth;
    protected ExceptionHandler $exceptionHandler;
    
    public function __construct(
        Application $app,
        LoggerInterface $logger,
        AuthFactory $auth,
        ExceptionHandler $exceptionHandler
    ) {
        $this->app = $app;
        $this->logger = $logger;
        $this->auth = $auth;
        $this->exceptionHandler = $exceptionHandler;
    }
    
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
     * @param string|null $authGuard
     * @param bool $enableByDefault - true: logs requests even if route has no 'log' action in its description
     * @param array $methods - list of HTTP methods to log. If empty - log all methods
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ?string $authGuard = null, bool $enableByDefault = true, ...$methods): mixed
    {
        $route = $request->route();
        $isAllowed = (
            empty($methods)
            || preg_match('%' . implode('|', $methods) . '%i', $request->getMethod())
            || !empty(Arr::get($route->getAction(), 'log'))
        );
        // reset logs to allow requests via test cases
        if ($this->app->bound(CmfHttpRequestLogsTable::class)) {
            $logsTable = $this->app->make(CmfHttpRequestLogsTable::class);
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
        $this->app->offsetUnset(ScaffoldLoggerInterface::class);
        $log = $logsTable::getCurrentLog();
        $this->app->instance(ScaffoldLoggerInterface::class, $log);
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
                    $user = $this->auth->guard($authGuard ?: null)->user();
                    if (!($user instanceof RecordInterface)) {
                        $user = null;
                    }
                }
                /** @var RecordInterface|null $user */
                $log->logResponse($request, $response, $user);
            } catch (\Throwable $exception) {
                $this->exceptionHandler->report($exception);
            }
        } else {
            $this->logger->error('LogHttpRequest: cannot log this response (not a Symfony response)', ['response' => $response]);
        }
        return $response;
    }
    
    /**
     * @param CmfHttpRequestLog|ScaffoldLoggerInterface $log
     * @noinspection PhpDocSignatureInspection
     */
    protected function logRequest(ScaffoldLoggerInterface $log, $request, bool $enableByDefault): void
    {
        try {
            $log->fromRequest($request, $enableByDefault);
        } catch (\Throwable $exception) {
            $this->exceptionHandler->report($exception);
        }
    }
}
