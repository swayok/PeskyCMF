<?php

namespace PeskyCMF\Http\Middleware;

use Illuminate\Http\Request;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsTable;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\RecordInterface;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequest {

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
        $isAllowed = empty($methods) || preg_match('%' . implode('|', $methods) . '%i', $request->getMethod());
        // reset logs to allow requests via test cases
        if (app()->bound(CmfHttpRequestLogsTable::class)) {
            $logsTable = app()->make(CmfHttpRequestLogsTable::class);
        } else {
            $logsTable = CmfHttpRequestLogsTable::getInstance();
        }
        $logsTable::resetCurrentLog();
        app()->offsetUnset(ScaffoldLoggerInterface::class);
        if ($isAllowed) {
            try {
                $log = $logsTable::logRequest($request, (bool)$enableByDefault);
                app()->instance(ScaffoldLoggerInterface::class, $log);
            } catch (\Throwable $exception) {
                \Log::error($exception);
            }
        }
        $response = $next($request);
        if ($response instanceof Response) {
            if (!$isAllowed && $response->getStatusCode() >= 400) {
                // now we can wrap into if ($isAllowed) {} and will not lose server errors logging
                $isAllowed = true;
            }
            if ($isAllowed) {
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
                    $logsTable::logResponse($request, $response, $user);
                } catch (\Throwable $exception) {
                    \Log::error($exception);
                }
            }
        } else {
            \Log::error('LogHttpRequest: cannot log this response (not a Symfony response)', ['response' => $response]);
        }
        return $response;
    }

}
