<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Event\AdminAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;

class ValidateAdmin {

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        //get the admin check closure that should be supplied in the config
        /** @var CmfConfig $configs */
        $configs = CmfConfig::getPrimary();
        //if this is a simple false value, send the user to the login redirect
        $response = \Auth::guard()->check();
        if (!$response) {
            $loginUrl = route($configs::login_route());
            $currentsUrl = $request->url();
            if ($request->ajax()) {
                \Session::put(CmfConfig::getPrimary()->session_redirect_key(), $currentsUrl);
                return response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED);
            } else {
                return redirect()->guest($loginUrl)->with(CmfConfig::getPrimary()->session_redirect_key(), $currentsUrl);
            }
        } else if (is_a($response, 'Illuminate\Http\JsonResponse') || is_a($response, 'Illuminate\Http\Response')) {
            return $response;
        } else if (is_a($response, 'Illuminate\\Http\\RedirectResponse')) {
            $currentsUrl = $request->url();
            /** @var RedirectResponse $response */
            if ($request->ajax()) {
                \Session::put(CmfConfig::getPrimary()->session_redirect_key(), $currentsUrl);
                return response()->json(['redirect' => $response->getTargetUrl()], HttpCode::UNAUTHORISED);
            } else {
                return $response->with(CmfConfig::getPrimary()->session_redirect_key(), $currentsUrl);
            }
        }
        /** @var CmfDbRecord $user */
        $user = \Auth::guard()->user();
        \Event::fire(new AdminAuthenticated($user));

        $response = $next($request);
        if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
            $fallbackUrl = $configs::login_route();
            $message = cmfTransGeneral('.error.access_denied');
            $response = $request->ajax()
                ? CmfJsonResponse::create([], HttpCode::FORBIDDEN)
                    ->setMessage($message)
                    ->goBack($fallbackUrl)
                : cmfRedirectResponseWithMessage($fallbackUrl, $message);
        }
        return $response;
    }

}