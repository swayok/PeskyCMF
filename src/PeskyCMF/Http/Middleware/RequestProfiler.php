<?php

namespace PeskyCMF\Http\Middleware;

use Illuminate\Http\Request;
use PeskyCMF\Db\HttpRequestStats\HttpRequestStat;
use PeskyORM\Profiling\PeskyOrmPdoProfiler;
use Symfony\Component\HttpFoundation\Response;

class RequestProfiler {

    /**
     * @param Request $request
     * @param \Closure $next
     * @param string $mode - 'all', 'none', 'custom'
     * @return mixed
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function handle(Request $request, \Closure $next, $mode = 'all') {
        if ($mode === 'all' || array_get($request->route()->getAction(), 'profiler')) {
            // begin profiling
            PeskyOrmPdoProfiler::init();
            $stat = HttpRequestStat::new1()
                ->setCreatedAt(date('Y-m-d H:i:s'));
            $startedAt = microtime(true);

            $response = $next($request);

            if ($response instanceof Response) {
                try {
                    // finish profiling
                    $stat
                        ->setDuration(round(microtime(true) - $startedAt, 3))
                        ->setUrl($request->getRequestUri())
                        ->setHttpMethod($request->getMethod())
                        ->setRoute('/' . ltrim($request->route()->getPrefix() . '/' . ltrim($request->route()->uri(), '/'), '/'))
                        ->setUrlParams($request->route()->parameters())
                        ->setHttpCode($response->getStatusCode())
                        ->setMemoryUsageMb(memory_get_peak_usage(true) / 1024 / 1024);
                    $sqlQueriesInfo = PeskyOrmPdoProfiler::collect();
                    $stat
                        ->setSql($sqlQueriesInfo)
                        ->setDurationSql(round($sqlQueriesInfo['accumulated_duration'], 3))
                        ->save();
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