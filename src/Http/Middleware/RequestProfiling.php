<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\Db\HttpRequestStats\CmfHttpRequestStat;
use PeskyORM\Profiling\PeskyOrmPdoProfiler;
use Symfony\Component\HttpFoundation\Response;

class RequestProfiling
{
    
    protected ExceptionHandler $exceptionHandler;
    
    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }
    
    /**
     * Middleware examples:
     * 1. Use default arguments: \PeskyCMF\Http\Middleware\RequestProfiling::class
     * 2. Disable by default: \PeskyCMF\Http\Middleware\RequestProfiling::class . ':0'
     * 3. Change defaults: \PeskyCMF\Http\Middleware\RequestProfiling::class . ':0,0.5,10'
     *
     * Route examples:
     * 1. Use defaults: Route::get('/path', [ 'profiler' => null ]) or just avoid 'profiler' option
     * 2. Enable using defaults: Route::get('/path', [ 'profiler' => true ])
     * 3. Enable always (ignores limitations): Route::get('/path', [ 'profiler' => 'force' ])
     * 4. Disable: Route::get('/path', [ 'profiler' => false ]) or Route::get('/path', [ 'profiler' => [] ])
     * 5. Enable with custom configs: Route::get('/path', [ 'profiler' => ['min_queries' => 10, 'min_duration' => 0.5] ])
     *
     * How limits work ('min_queries'/$minDbQueries and 'min_duration'/$minDuration):
     * 1. If profiling contains checkpoints - limits are ignored
     * 2. If request duration is more or equals to 'min_duration'/$minDuration or amount of SQL queries is
     * more or equals to 'min_queries'/$minDbQueries - profiling will be saved to db. Otherwise - it won't.
     *
     * Note that profiling slightly affects overall perfomance (~50ms) if enabled so make sure you disable it for
     * requests that do not require profiling anymore.
     *
     * @param Request $request
     * @param \Closure $next
     * @param bool $enabledByDefault
     *  - true - profile all routes until route has 'prifiler' === false option (action):
     *      Route::get('/path', [..., 'profiler' => false, ...]); - profiling for such route will be disabled
     *  - false - profile only routes that have 'prifiler' === true otion (action):
     *      Route::get('/path', [..., 'profiler' => true, ...]); - profiling for such route will be enabled
     * @param float $minDuration - do not record profiling if duration is less then specified
     * @param int $minDbQueries - do not record profiling if min amount of queries is lee then specified
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, bool $enabledByDefault = true, float $minDuration = 0, int $minDbQueries = 0)
    {
        $route = $request->route();
        $config = Arr::get($route->getAction(), 'profiler');
        if ($config === null) {
            $enabled = $enabledByDefault;
            $config = [];
        } elseif (empty($config)) {
            $enabled = false;
        } elseif (is_array($config)) {
            $enabled = true;
        } else {
            $enabled = true;
            if ($config === 'force') {
                $config = [
                    'min_duration' => 0,
                    'min_queries' => 0,
                ];
            } else {
                $config = [];
            }
        }
        if ($enabled) {
            // begin profiling
            PeskyOrmPdoProfiler::init();
            $stat = CmfHttpRequestStat::createForProfiling();
            // process request
            $response = $next($request);
            // on HTTP response
            if ($response instanceof Response) {
                try {
                    $hasCheckpoints = count($stat->checkpoints_as_array);
                    $stat
                        ->processResponse($response)
                        ->addSqlProfilingData();
                    if (
                        $hasCheckpoints
                        || $stat->duration >= (float)Arr::get($config, 'min_duration', $minDuration)
                        || (
                            $stat->duration_sql > 0
                            && (int)Arr::get($stat->sql_as_array, 'statements_count', 999) >= (int)Arr::get($config, 'min_queries', $minDbQueries)
                        )
                    ) {
                        $stat
                            ->processRequest($request)
                            ->finishAndSave();
                    }
                } catch (\Exception $exception) {
                    $this->exceptionHandler->report($exception);
                }
            }
            // save results to DB
            return $response;
        } else {
            return $next($request);
        }
    }
    
}