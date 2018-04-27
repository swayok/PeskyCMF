<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PeskyCMF\Traits\DataValidationHelper;

class ValidateData {

    use DataValidationHelper;

    protected $errorMessage;

    protected function getValidationErrorsResponseMessage() {
        return isset($this->errorMessage)
            ? trans($this->errorMessage)
            : cmfTransGeneral('.message.invalid_data_received');
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
            $errors = $this->validateAndReturnErrors($request, $actionInfo['validate'], $messages);
            if (!empty($errors)) {
                return $this->makeValidationErrorsJsonResponse($errors);
            }
        }
        return $next($request);
    }

}