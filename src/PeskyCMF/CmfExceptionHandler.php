<?php

namespace PeskyCMF;

use Illuminate\Http\JsonResponse;
use LaravelExtendedErrors\ExceptionHandler;
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
                    '_message' => trans(ApiConstants::ERROR_INVALID_DATA),
                    'errors' => $exc->getValidationErrors()
                ], HttpCode::INVALID);
            case NotFoundHttpException::class:
                return new JsonResponse([], HttpCode::NOT_FOUND);
            case HttpException::class:
                /** @var HttpException $exc */
                $data = json_decode($exc->getMessage(), true);
                if ($data === false) {
                    $data = ['_message' => $exc->getMessage()];
                }
                return new JsonResponse($data, $exc->getStatusCode());
            default:
                return $this->defaultConvertExceptionToResponse($exc);
        }
    }

    protected function defaultConvertExceptionToResponse(\Exception $exc) {
        return parent::convertExceptionToResponse($exc);
    }
}