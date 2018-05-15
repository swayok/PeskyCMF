<?php
namespace PeskyCMF\Auth\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;

class CmfAuth {

    public function handle(Request $request, \Closure $next) {
        /** @var CmfConfig $cmfConfig */
        $cmfConfig = CmfConfig::getPrimary();
        if (!$cmfConfig::getAuth()->check()) {
            $loginUrl = $cmfConfig::login_page_url();
            \Session::put($cmfConfig::session_redirect_key(), $request->url());
            return $request->ajax()
                ? response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED)
                : redirect($loginUrl);
        } else {
            /** @var RecordInterface|Authenticatable $user */
            \Event::fire(new CmfUserAuthenticated($cmfConfig::getUser()));

            $response = $next($request);
            if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
                $fallbackUrl = $cmfConfig::login_page_url();
                $message = $cmfConfig::transGeneral('.message.access_denied');
                $response = $request->ajax()
                    ? cmfJsonResponse(HttpCode::FORBIDDEN)->setMessage($message)->goBack($fallbackUrl)
                    : cmfRedirectResponseWithMessage($fallbackUrl, $message);
            }
            return $response;
        }
    }

}