<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use PeskyORM\Exception\DbObjectValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AjaxOnly {

    /**
     * Request must be done via ajax
     * You can specify a fallback url OR 'route' with optional 'params' via 'fallback' key in route config:
     * Example:
     * Route::get('forgot_password', [
     *  'middleware' => AjaxOnly::class,
     *  'fallback' => '/some/url'
     *  //< or
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
                return new RedirectResponse($fallback);
            } else if (!empty($fallback['route'])) {
                $params = array_get($fallback, 'params', true);
                if ($params === true) {
                    $params = $request->route()->parameters();
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
        } catch (DbObjectValidationException $exc) {
            return new JsonResponse([
                '_message' => trans(CmfConfig::transBase('.error.invalid_data_received')),
                'errors' => $exc->getValidationErrors()
            ], HttpCode::INVALID);
        } catch (HttpException $exc) {
            if ($exc->getStatusCode() === HttpCode::INVALID) {
                $data = json_decode($exc->getMessage(), true);
                if (!empty($data) && !empty($data['_message'])) {
                    return new JsonResponse([
                        '_message' => $data['_message'],
                        'errors' => empty($data['errors']) ? [] : $data['errors']
                    ], HttpCode::INVALID);
                }
            }
            throw $exc;
        }

    }

}