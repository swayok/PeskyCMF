<?php

declare(strict_types=1);

namespace PeskyCMF\Auth\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;

class CmfAuth
{
    
    public function handle(Request $request, \Closure $next)
    {
        $cmfConfig = CmfConfig::getPrimary();
        $loginUrl = $cmfConfig::getAuthModule()->getLoginPageUrl();
        if (!$cmfConfig::getAuthGuard()->check()) {
            $cmfConfig::getAuthModule()->saveIntendedUrl($request->url());
            return $request->ajax()
                ? CmfJsonResponse::create(HttpCode::UNAUTHORISED)->setForcedRedirect($loginUrl)
                : new RedirectResponse($loginUrl);
        } else {
            /** @var RecordInterface|Authenticatable $user */
            $cmfConfig->getLaravelApp()->make('events')->dispatch(new CmfUserAuthenticated($cmfConfig::getUser()));
            
            $response = $next($request);
            if ($response->getStatusCode() === HttpCode::FORBIDDEN && stripos($response->getContent(), 'unauthorized') !== false) {
                $message = $cmfConfig::transGeneral('.message.access_denied');
                $response = $request->ajax()
                    ? CmfJsonResponse::create(HttpCode::FORBIDDEN)->setMessage($message)->goBack($loginUrl)
                    : (new RedirectResponse($loginUrl))->with(
                        $cmfConfig::session_message_key(),
                        [
                            'message' => $message,
                            'type' => CmfJsonResponse::MESSAGE_TYPE_INFO,
                        ]
                    );
            }
            return $response;
        }
    }
    
}