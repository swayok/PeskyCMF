<?php

namespace PeskyCMF\Http\Middleware;

use Illuminate\Http\Request;
use PeskyCMF\Db\HttpRequestStats\HttpRequestStat;
use PeskyORM\Profiling\PeskyOrmPdoProfiler;
use Symfony\Component\HttpFoundation\Response;

class RequestProfiling {

    /**
     * @param Request $request
     * @param \Closure $next
     * @param string $routesToProfileByDefault
     *  - 'all' - profile all routes until route has 'prifiler' === false option (action):
     *      Route::get('/path', [..., 'profiler' => false, ...]); - profiling for such route will be disabled
     *  - 'none' - profile only routes that have 'prifiler' === true otion (action):
     *      Route::get('/path', [..., 'profiler' => true, ...]); - profiling for such route will be enabled
     * @return mixed
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function handle(Request $request, \Closure $next, $routesToProfileByDefault = 'all') {
        if (array_get($request->route()->getAction(), 'profiler', $routesToProfileByDefault === 'all')) {
            // begin profiling
            PeskyOrmPdoProfiler::init();
            $stat = HttpRequestStat::createForProfiling();
            // process request
            $response = $next($request);
            // on HTTP response
            if ($response instanceof Response) {
                try {
                    $stat
                        ->processRequest($request)
                        ->processResponse($response)
                        ->addSqlProfilingData()
                        ->finishAndSave();
                } catch (\Exception $exception) {
                    \Log::error($exception);
                }
            }
            // save results to DB
            return $response;
        } else {
            return $next($request);
        }
    }

}