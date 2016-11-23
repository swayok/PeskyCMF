<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use PeskyCMF\Traits\DataValidationHelper;

class ValidateData {

    use DataValidationHelper;

    protected $errorMessage;

    protected function getValidationErrorsResponseMessage() {
        return isset($this->errorMessage)
            ? trans($this->errorMessage)
            : cmfTransGeneral('.error.invalid_data_received');
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        /** @var Route $route */
        $route = $request->route();
        $actionInfo = $route->getAction();
        if (!empty($actionInfo['validate'])) {
            $messages = [];
            if (!empty($actionInfo['validation_messages'])) {
                $messages = trans($actionInfo['validation_messages']);
                if (!is_array($messages)) {
                    $messages = [];
                }
            }
            $response = $this->validate($request->all(), $actionInfo['validate'], $messages);
            if ($response !== true) {
                return $response;
            }
        }
        return $next($request);
    }

    protected function sendValidationErrorsResponse($errors) {
        return response()->json($this->prepareDataForValidationErrorsResponse($errors), HttpCode::INVALID);
    }

}