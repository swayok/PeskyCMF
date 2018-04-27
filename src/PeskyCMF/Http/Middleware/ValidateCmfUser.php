<?php
namespace PeskyCMF\Http\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ValidateCmfUser {

    public function handle(Request $request, \Closure $next) {
        //get the admin check closure that should be supplied in the config
        /** @var CmfConfig $cmfConfig */
        $cmfConfig = CmfConfig::getPrimary();
        //if this is a simple false value, send the user to the login redirect
        $authResponse = $cmfConfig::getAuth()->check();
        if (!$authResponse) {
            $loginUrl = $cmfConfig::login_page_url();
            $currentUrl = $request->url();
            if ($request->ajax()) {
                \Session::put($cmfConfig::session_redirect_key(), $currentUrl);
                return response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED);
            } else {
                return redirect()->guest($loginUrl)->with($cmfConfig::session_redirect_key(), $currentUrl);
            }
        } else if (is_a($authResponse, JsonResponse::class) || is_a($authResponse, Response::class)) {
            return $authResponse;
        } else if (is_a($authResponse, RedirectResponse::class)) {
            $currentUrl = $request->url();
            /** @var RedirectResponse $authResponse */
            if ($request->ajax()) {
                \Session::put($cmfConfig::session_redirect_key(), $currentUrl);
                return response()->json(['redirect' => $authResponse->getTargetUrl()], HttpCode::UNAUTHORISED);
            } else {
                return $authResponse->with($cmfConfig::session_redirect_key(), $currentUrl);
            }
        }
        /** @var RecordInterface|Authenticatable $user */
        $user = $cmfConfig::getUser();
        \Event::fire(new CmfUserAuthenticated($user));

        $response = $next($request);
        if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
            $fallbackUrl = $cmfConfig::login_page_url();
            $message = $cmfConfig::transGeneral('.message.access_denied');
            $response = $request->ajax()
                ? CmfJsonResponse::create([], HttpCode::FORBIDDEN)
                    ->setMessage($message)
                    ->goBack($fallbackUrl)
                : cmfRedirectResponseWithMessage($fallbackUrl, $message);
        }
        return $response;
    }

}