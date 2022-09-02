<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use PeskyCMF\CmfManager;

class UseCmfSection
{
    
    protected Application $app;
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    /**
     * @param Request $request
     * @param \Closure $next
     * @param string $cmfSectionName - name of a key from config('peskycmf.cmf_configs')
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, string $cmfSectionName): mixed
    {
        /** @var CmfManager $cmfManager */
        $cmfManager = $this->app->make(CmfManager::class);
        $cmfManager->setCurrentCmfSection($cmfSectionName);
        return $next($request);
    }
}