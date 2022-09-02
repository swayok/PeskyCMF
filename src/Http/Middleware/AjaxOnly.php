<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use PeskyCMF\HttpCode;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Exception\InvalidDataException;
use Swayok\Utils\StringUtils;

class AjaxOnly
{
    
    use DataValidationHelper;
    
    /**
     * Request must be done via ajax
     * You can specify a fallback url OR 'route' with optional 'params' via 'fallback' key in route config:
     * Example:
     * Route::get('forgot_password/{param}', [
     *  'middleware' => AjaxOnly::class,
     *  'fallback' => '/some/url'
     *  // or
     *  'fallback' => '/some/url/{param}'
     *  // or
     *  'fallback' => [
     *      'route' => $routeNamePrefix . 'cmf_login',
     *      'use_params' => bool //< optional, default === true: pass all params from original url to fallback url,
     *      'add_params' => [] //< optional, additional params to pass to fallback route
     *  ],
     *  ...
     * ]
     * If 'params' === true - all params retrieved from original URL will be passed to fallback route
     * If 'params' === false - params retrieved from original URL will not be passed to fallback route
     *
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->ajax()) {
            $route = $request->route();
            // maybe there is a fallback?
            $fallback = Arr::get($route->getAction(), 'fallback', []);
            if (!empty($fallback) && is_string($fallback)) {
                return new RedirectResponse(
                    StringUtils::insert(
                        $fallback,
                        $route->parameters(),
                        ['before' => '{', 'after' => '}']
                    )
                );
            } elseif (!empty($fallback['route'])) {
                $passParams = (bool)Arr::get($fallback, 'use_params', true);
                $params = [];
                if ($passParams === true) {
                    $params = $route->parameters();
                }
                $addParams = Arr::get($fallback, 'add_params', true);
                if (is_array($addParams)) {
                    $params = array_merge($params, $addParams);
                }
                return new RedirectResponse(route($fallback['route'], $params));
            } else {
                abort(HttpCode::FORBIDDEN, 'Only ajax requests');
            }
        }
        try {
            return $next($request);
        } catch (InvalidDataException $exc) {
            return $this->makeValidationErrorsJsonResponse($exc->getErrors(true));
        }
    }
    
}