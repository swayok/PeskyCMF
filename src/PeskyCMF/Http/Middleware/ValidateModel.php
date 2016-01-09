<?php

namespace PeskyCMF\Http\Middleware;

use App\Db\BaseDbModel;
use Closure;
use Illuminate\Http\Request;
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
                $modelClass = BaseDbModel::getFullModelClassByTableName($tableName);
                /** @var BaseDbModel $model */
                $model = $modelClass::getInstance();
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