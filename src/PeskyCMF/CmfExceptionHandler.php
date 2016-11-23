<?php

namespace PeskyCMF;

use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use LaravelExtendedErrors\ExceptionHandler;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\Exception\DbObjectValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmfExceptionHandler extends ExceptionHandler {

    protected function _convertExceptionToResponse(\Exception $exc) {
        if (request()->ajax()) {
            return $this->convertSomeExceptionsToJsonResponses($exc);
        } else {
            return $this->defaultConvertExceptionToResponse($exc);
        }
    }

    protected function convertSomeExceptionsToJsonResponses(\Exception $exc) {
        switch (get_class($exc)) {
            case DbObjectValidationException::class:
                /** @var DbObjectValidationException $exc */
                return new JsonResponse([
                    '_message' => $this->errorCodeToMessage('invalid_data_received'),
                    'errors' => $exc->getValidationErrors()
                ], HttpCode::INVALID);
            case NotFoundHttpException::class:
                /*return new JsonResponse([
                    '_message' => CmfConfig::transBase('.error.http404')
                ], HttpCode::NOT_FOUND);*/
            case HttpException::class:
                return $this->convertHttpExceptionToJsonResponse($exc);
            case TokenMismatchException::class:
                $message = $this->errorCodeToMessage('csrf_token_missmatch');
                $this->saveErrorMessageToSession($message);
                return new JsonResponse([
                    '_message' => $message,
                    'redirect' => 'reload',
                    'redirect_fallback' => $this->getStartPageUrl()
                ], HttpCode::INVALID);
            default:
                return $this->defaultConvertExceptionToResponse($exc);
        }
    }

    protected function defaultConvertExceptionToResponse(\Exception $exc) {
        switch (get_class($exc)) {
            case TokenMismatchException::class:
                $message = $this->errorCodeToMessage('csrf_token_missmatch');
                $this->saveErrorMessageToSession($message);
                return redirect($_SERVER['REQUEST_URI']);
        }
        return parent::_convertExceptionToResponse($exc);
    }

    protected function errorCodeToMessage($code, $parameters = []) {
        return cmfTransGeneral('.error.' . $code, $parameters);
    }

    protected function getStartPageUrl() {
        return '/';
    }

    protected function saveErrorMessageToSession($message) {

    }
}