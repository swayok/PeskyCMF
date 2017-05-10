<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Exception\InvalidDataException;
use Swayok\Utils\StringUtils;

class AjaxOnly {

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
     *      'route' => 'cmf_login',
     *      'params' => [] //< optional, can be array or boolean (by default === true: pass params from original url)
     *  ],
     *  ...
     * ]
     * If 'params' === true - all params retrieved from original URL will be passed to fallback route
     * If 'params' === false - all params retrieved from original URL will be passed to fallback route
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle(Request $request, Closure $next) {
        if (!$request->ajax()) {
            // maybe there is a fallback?
            $fallback = array_get($request->route()->getAction(), 'fallback', []);
            if (!empty($fallback) && is_string($fallback)) {
                return new RedirectResponse(StringUtils::insert(
                    $fallback,
                    $request->route()->parameters(),
                    ['before' => '{', 'after' => '}']
                ));
            } else if (!empty($fallback['route'])) {
                $params = array_get($fallback, 'params', true);
                if ($params === true) {
                    $params = $request->route()->parameters();
                } else if ($params instanceof \Closure) {
                    $params = call_user_func($params, $request->route()->parameters());
                } else if ($params === false || !is_array($params)) {
                    $params = [];
                }
                return new RedirectResponse(route($fallback['route'], $params));
            } else {
                abort(HttpCode::FORBIDDEN, 'Only ajax requests');
            }
        }
        try {
            return $next($request);
        } catch (InvalidDataException $exc) {
            return $this->sendValidationErrorsResponse($exc->getErrors(true));
            /*return new JsonResponse([
                '_message' => trans(cmfTransGeneral('.error.invalid_data_received')),
                'errors' => $exc->getErrors()
            ], HttpCode::INVALID);*/
        }
    }

}