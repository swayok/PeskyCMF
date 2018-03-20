<?php

namespace PeskyCMF\Http\Middleware;

use Illuminate\Http\Request;
use PeskyCMF\Db\HttpRequestLogs\CmfHttpRequestLogsTable;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;

class LogHttpRequest {

    /**
     * Middleware examples:
     * 1. Use default auth guard and all HTTP methods: \PeskyCMF\Http\Middleware\LogHttpRequest::class
     * 2. Use custom auth guard and all HTTP methods: \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api'
     * 3. Use custom auth guard and custom HTTP methods list: \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':api,post,put,delete'
     * 4. Use default auth guard and custom HTTP methods list: \PeskyCMF\Http\Middleware\LogHttpRequest::class . ':,post,put,delete'
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
        CmfHttpRequestLogsTable::resetCurrentLog();
        app()->offsetUnset(ScaffoldLoggerInterface::class);
        if ($isAllowed) {
            $log = CmfHttpRequestLogsTable::logRequest($request, (bool)$enableByDefault);
            app()->instance(ScaffoldLoggerInterface::class, $log);
        }
        $response = $next($request);
        // do not wrap into if ($isAllowed) {} or you will lose server errors logging
        CmfHttpRequestLogsTable::logResponse($request, $response, \Auth::guard($authGuard ?: null)->user());
        return $response;
    }

}
