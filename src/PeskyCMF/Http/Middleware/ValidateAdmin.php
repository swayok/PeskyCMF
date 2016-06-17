<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\Event\AdminAuthorised;
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
        $configs = CmfConfig::getInstance();
        //if this is a simple false value, send the user to the login redirect
        $response = false;
        if (\Auth::guard()->check()) {
            $response = $configs->isAuthorised($request);
        }
        if (!$response) {
            $loginUrl = route($configs->login_route());
            $currentsUrl = $request->url();
            if ($request->ajax()) {
                \Session::set(CmfConfig::getInstance()->session_redirect_key(), $currentsUrl);
                return response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED);
            } else {
                return redirect()->guest($loginUrl)->with(CmfConfig::getInstance()->session_redirect_key(), $currentsUrl);
            }
        } else if (is_a($response, 'Illuminate\Http\JsonResponse') || is_a($response, 'Illuminate\Http\Response')) {
            return $response;
        } else if (is_a($response, 'Illuminate\\Http\\RedirectResponse')) {
            $currentsUrl = $request->url();
            /** @var RedirectResponse $response */
            if ($request->ajax()) {
                \Session::set(CmfConfig::getInstance()->session_redirect_key(), $currentsUrl);
                return response()->json(['redirect' => $response->getTargetUrl()], HttpCode::UNAUTHORISED);
            } else {
                return $response->with(CmfConfig::getInstance()->session_redirect_key(), $currentsUrl);
            }
        }
        /** @var CmfDbObject $user */
        $user = \Auth::guard()->user();
        \Event::fire(new AdminAuthorised($user));

        return $next($request);
    }

}