<?php
namespace PeskyCMF\Http\Middleware;

use App\AppConstants;
use Closure;
use Illuminate\Http\Request;
use PeskyCMF\HttpCode;
use PeskyORM\Exception\DbObjectValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AjaxOnly {

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if (!$request->ajax()) {
            abort(HttpCode::FORBIDDEN, 'Only ajax requests');
        }
        try {
            return $next($request);
        } catch (DbObjectValidationException $exc) {
            return new JsonResponse([
                '_message' => trans(AppConstants::ERROR_INVALID_DATA),
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