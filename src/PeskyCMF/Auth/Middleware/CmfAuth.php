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
        $loginUrl = $cmfConfig::getAuthModule()->getLoginPageUrl();
        if (!$cmfConfig::getAuthGuard()->check()) {
            $cmfConfig::getAuthModule()->saveIntendedUrl($request->url());
            return $request->ajax()
                ? cmfJsonResponse(HttpCode::UNAUTHORISED)->setForcedRedirect($loginUrl)
                : redirect($loginUrl);
        } else {
            /** @var RecordInterface|Authenticatable $user */
            \Event::fire(new CmfUserAuthenticated($cmfConfig::getUser()));

            $response = $next($request);
            if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
                $message = $cmfConfig::transGeneral('.message.access_denied');
                $response = $request->ajax()
                    ? cmfJsonResponse(HttpCode::FORBIDDEN)->setMessage($message)->goBack($loginUrl)
                    : cmfRedirectResponseWithMessage($loginUrl, $message);
            }
            return $response;
        }
    }

}