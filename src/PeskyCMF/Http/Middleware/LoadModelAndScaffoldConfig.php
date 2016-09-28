<?php

namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Controllers\CmfScaffoldApiController;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Exception\DbUtilsException;

class LoadModelAndScaffoldConfig {

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return \Closure
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function handle(Request $request, Closure $next) {
        $tableName = $request->route()->parameter('table_name');
        if (!empty($tableName)) {
            try {
                $model = CmfConfig::getInstance()->getModelByTableName($tableName);
                $cmfConfig = CmfConfig::getInstance();
                /** @var CmfScaffoldApiController $scaffoldApiControllerClass */
                $scaffoldApiControllerClass = $cmfConfig::cmf_scaffold_api_controller_class();
                $scaffoldApiControllerClass::setModel($model);
                $scaffoldApiControllerClass::setTableNameForRoutes($tableName);
                $customScaffoldConfig = $cmfConfig::getScaffoldConfig($model, $tableName);
                if ($customScaffoldConfig instanceof ScaffoldSectionConfig) {
                    $scaffoldApiControllerClass::setScaffoldConfig($customScaffoldConfig);
                } else if (!empty($customScaffoldConfig)) {
                    throw new \LogicException(
                        get_class($cmfConfig) . '::getCustomScaffoldSectionConfigForTable() must return '
                            . 'null or instance of ScaffoldSectionConfig class'
                    );
                }
            } catch (DbUtilsException $exc) {
                dpr($exc->getMessage());
            }
        }

        // if the model doesn't exist at all, redirect to 404
        if (empty($model)) {
            abort(404, 'Page not found');
        }

        return $next($request);
    }

}