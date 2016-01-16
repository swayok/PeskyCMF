<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use Swayok\Utils\Set;

class ValidateData {

    protected $errorMessage;

    public function __construct() {
        $this->errorMessage = CmfConfig::getInstance()->cmf_base_dictionary_name() . '.error.invalid_data_received';
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
                } else {
                    $messages = Set::flatten($messages);
                }
            }
            $validator = \Validator::make($request->all(), $actionInfo['validate'], $messages);
            if ($validator->fails()) {
                return response()->json([
                    '_message' => trans($this->errorMessage),
                    'errors' => $this->fixErrorsKeys($validator->getMessageBag()->toArray())
                ], HttpCode::INVALID);
            }
        }

        return $next($request);
    }

    /**
     * Replace keys like 'some.column' by 'some[column]' to fit <input> names
     * @param array $errors
     * @return array
     */
    protected function fixErrorsKeys(array $errors) {
        foreach ($errors as $key => $messages) {
            if (strstr($key, '.') !== false) {
                $newKey = preg_replace('%^([^\]]+)\]%', '$1', str_replace('.', '][', $key) . ']');
                $errors[$newKey] = $messages;
                unset($errors[$key]);
            }
        }
        return $errors;
    }

}