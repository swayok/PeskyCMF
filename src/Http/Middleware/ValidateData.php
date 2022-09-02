<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use PeskyCMF\Traits\DataValidationHelper;

class ValidateData
{
    
    use DataValidationHelper;
    
    protected Application $app;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /* override this to chnage error message
    protected function getValidationErrorsResponseMessage()
    {
        return 'validation error'
    }
    */
    
    public function handle(Request $request, Closure $next): mixed
    {
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