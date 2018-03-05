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
     * To activate logging for a route - add 'log' action to a route. 'log' must be a string and will be recorded to DB
     * in order to group requests by short name like 'user.me'.
     * Route example: Route::get('/user/me', ['log' => 'user.me'])
     *
     * Note: if there was a server error during any request - it will be forcefully logged ignoring any restrictions.
     * @param Request $request
     * @param \Closure $next
     * @param null|string $authGuard
     * @param array $methods - list of HTTP methods to log. If empty - log all methods
     * @return mixed
     * @throws \PeskyORM\Exception\OrmException
     */
    public function handle($request, \Closure $next, $authGuard = null, ...$methods) {
        $isAllowed = empty($methods) || preg_match('%' . implode('|', $methods) . '%i', $request->getMethod());
        if ($isAllowed) {
            $log = CmfHttpRequestLogsTable::logRequest($request);
            app()->instance(ScaffoldLoggerInterface::class, $log);
        }
        $response = $next($request);
        // do not wrap into if ($isAllowed) {} or you will lose server errors logging
        CmfHttpRequestLogsTable::logResponse($request, $response, \Auth::guard($authGuard ?: null)->user());
        return $response;
    }

}
