<?php

namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Http\Controllers\CmfScaffoldApiController;
use PeskyORM\Exception\DbUtilsException;

class ValidateModel {

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        $tableName = $request->route()->parameter('table_name');
        if (!empty($tableName)) {
            try {
                /** @var CmfDbModel $model */
                $model = call_user_func(
                    [CmfConfig::getInstance()->base_db_model_class(), 'getModelByTableName'],
                    $tableName
                );
                CmfScaffoldApiController::setModel($model);
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