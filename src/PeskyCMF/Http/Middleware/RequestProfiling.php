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
     * @param string $mode - 'all', 'custom'; custom mode means that profiling will be enabled for routes that
     *      have 'profiler' option with positive value in route's configuration.
     *      Example: Route::get('/path', [..., 'profiler' => true, ...]);
     * @return mixed
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function handle(Request $request, \Closure $next, $mode = 'all') {
        if ($mode === 'all' || array_get($request->route()->getAction(), 'profiler')) {
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