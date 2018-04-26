<?php

namespace PeskyCMF\Http\Middleware;

use Illuminate\Http\Request;
use PeskyCMF\PeskyCmfManager;

class UseCmfSection {

    /**
     * @param Request $request
     * @param \Closure $next
     * @param $cmfSectionName - name of a key from config('peskycmf.cmf_configs')
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $cmfSectionName) {
        /** @var PeskyCmfManager $cmfManager */
        $cmfManager = app(PeskyCmfManager::class);
        $cmfManager->setCurrentCmfSection($cmfSectionName);
        return $next($request);
    }
}