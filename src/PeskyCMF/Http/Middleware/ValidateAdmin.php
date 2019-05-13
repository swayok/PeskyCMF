<?php
namespace PeskyCMF\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\Event\AdminAuthorised;
use PeskyCMF\Http\Controllers\CmfGeneralController;
use PeskyCMF\HttpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
        $response = false;
        if (\Auth::guard()->check()) {
            $response = $configs::isAuthorised($request);
        }
        if (!$response) {
            //if this is a simple false value, send the user to the login screen
            $backUrl = $request->getMethod() === 'GET' && !$request->ajax()
                ? $request->fullUrl()
                : $configs::home_page_url();
            $loginUrl = route($configs::login_route(), [CmfGeneralController::BACK_URL_PARAM => $backUrl]);
            if ($request->ajax()) {
                return response()->json(['redirect_with_reload' => $loginUrl], HttpCode::UNAUTHORISED);
            } else {
                return redirect()->guest($loginUrl);
            }
        } else if (is_a($response, JsonResponse::class) || is_a($response, Response::class)) {
            return $response;
        } else if (is_a($response, RedirectResponse::class)) {
            /** @var RedirectResponse $response */
            if ($request->ajax()) {
                return response()->json(['redirect' => $response->getTargetUrl()], HttpCode::UNAUTHORISED);
            } else {
                return $response;
            }
        }
        /** @var CmfDbObject $user */
        $user = \Auth::guard()->user();
        event(new AdminAuthorised($user));

        return $next($request);
    }

}