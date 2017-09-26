<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ValidateCmfUser {

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
        $response = $configs::getAuth()->check();
        if (!$response) {
            $loginUrl = route($configs::login_route());
            $currentUrl = $request->url();
            if ($request->ajax()) {
                \Session::put(CmfConfig::getPrimary()->session_redirect_key(), $currentUrl);
                return response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED);
            } else {
                return redirect()->guest($loginUrl)->with(CmfConfig::getPrimary()->session_redirect_key(), $currentUrl);
            }
        } else if (is_a($response, JsonResponse::class) || is_a($response, Response::class)) {
            return $response;
        } else if (is_a($response, RedirectResponse::class)) {
            $currentUrl = $request->url();
            /** @var RedirectResponse $response */
            if ($request->ajax()) {
                \Session::put(CmfConfig::getPrimary()->session_redirect_key(), $currentUrl);
                return response()->json(['redirect' => $response->getTargetUrl()], HttpCode::UNAUTHORISED);
            } else {
                return $response->with(CmfConfig::getPrimary()->session_redirect_key(), $currentUrl);
            }
        }
        /** @var RecordInterface|Authenticatable $user */
        $user = $configs::getUser();
        \Event::fire(new AdminAuthenticated($user));

        $response = $next($request);
        if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
            $fallbackUrl = $configs::login_route();
            $message = $configs::transGeneral('.error.access_denied');
            $response = $request->ajax()
                ? CmfJsonResponse::create([], HttpCode::FORBIDDEN)
                    ->setMessage($message)
                    ->goBack($fallbackUrl)
                : cmfRedirectResponseWithMessage($fallbackUrl, $message);
        }
        return $response;
    }

}