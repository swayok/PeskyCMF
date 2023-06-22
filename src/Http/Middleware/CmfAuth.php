<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\Record\RecordInterface;

class CmfAuth
{
    protected CmfConfig $cmfConfig;

    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
    }

    public function handle(Request $request, \Closure $next)
    {
        $loginUrl = $this->cmfConfig->getAuthModule()->getLoginPageUrl();
        if (!$this->cmfConfig->getAuthGuard()->check()) {
            $this->cmfConfig->getAuthModule()->saveIntendedUrl($request->url());
            return $request->ajax()
                ? CmfJsonResponse::create(HttpCode::UNAUTHORISED)->setForcedRedirect($loginUrl)
                : new RedirectResponse($loginUrl);
        }

        /** @var RecordInterface $user */
        $user = $this->cmfConfig->getUser();
        /** @var RecordInterface|Authenticatable $user */
        $this->cmfConfig->getLaravelApp()->make('events')
            ->dispatch(new CmfUserAuthenticated($user, $this->cmfConfig));

        $response = $next($request);
        if (
            $response->getStatusCode() === HttpCode::FORBIDDEN
            && stripos($response->getContent(), 'unauthorized') !== false
        ) {
            $message = $this->cmfConfig->transGeneral('message.access_denied');
            $response = $request->ajax()
                ? CmfJsonResponse::create(HttpCode::FORBIDDEN)->setMessage($message)->goBack($loginUrl)
                : (new RedirectResponse($loginUrl))->with(
                    $this->cmfConfig->sessionMessageKey(),
                    [
                        'message' => $message,
                        'type' => CmfJsonResponse::MESSAGE_TYPE_INFO,
                    ]
                );
        }
        return $response;
    }
}
