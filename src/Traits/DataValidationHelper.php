<?php

declare(strict_types=1);

namespace PeskyCMF\Traits;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use Swayok\Utils\Set;

trait DataValidationHelper
{
    
    protected ?ValidationFactoryContract $validator = null;
    
    protected function getValidator(): ValidationFactoryContract
    {
        if (!$this->validator) {
            $app = $this->app ?? app();
            $this->validator = $app->make(ValidationFactoryContract::class);
        }
        return $this->validator;
    }
    
    /**
     * Validate data and throw ValidationException if it is invalid or return validated data
     * @param array|Request $dataOrRequest
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array - data from request filtered by rules keys
     * @throws ValidationException
     */
    public function validate(array|Request $dataOrRequest, array $rules, array $messages = [], array $customAttributes = []): array
    {
        $errors = $this->validateAndReturnErrors($dataOrRequest, $rules, $messages, $customAttributes);
        if (!empty($errors)) {
            $this->throwValidationErrorsResponse($errors);
        }
        
        return $this->extractInputFromRules($dataOrRequest, $rules);
    }
    
    /**
     * Validate data and returm errors array if it is invalid
     * @param array|Request $dataOrRequest
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array - errors
     */
    public function validateAndReturnErrors(
        array|Request $dataOrRequest,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): array {
        $messages = Set::flatten($messages);
        if ($dataOrRequest instanceof Request) {
            $dataOrRequest = $dataOrRequest->all();
        }
        $validator = $this->getValidator()->make($dataOrRequest, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            return $validator->getMessageBag()->toArray();
        }
        
        return [];
    }
    
    /**
     * Get the request input based on the given validation rules.
     */
    protected function extractInputFromRules(array|Collection|Request $data, array $rules): array
    {
        $keys = (new Collection($rules))->keys()
            ->map(function ($rule) {
                return explode('.', $rule)[0];
            })
            ->unique()
            ->toArray();
        if (!($data instanceof Request)) {
            $data = new Collection($data);
        }
        return $data->only($keys);
    }
    
    protected function getValidationErrorsResponseMessage(): string
    {
        return $this->getCmfConfig()->transGeneral('message.invalid_data_received');
    }
    
    public function makeValidationErrorsJsonResponse(array $errors): JsonResponse
    {
        return new JsonResponse($this->prepareDataForValidationErrorsResponse($errors), HttpCode::CANNOT_PROCESS);
    }
    
    public function throwValidationErrorsResponse(array $errors): void
    {
        abort($this->makeValidationErrorsJsonResponse($errors));
    }
    
    public function prepareDataForValidationErrorsResponse(array $errors): array
    {
        $message = Arr::get($errors, CmfJsonResponse::$messageKey, $this->getValidationErrorsResponseMessage());
        unset($errors[CmfJsonResponse::$messageKey]);
        
        return [
            CmfJsonResponse::$messageKey => $message,
            CmfJsonResponse::$errorsKey => static::fixValidationErrorsKeys($errors),
        ];
    }
    
    /**
     * Replace keys like 'some.column' by 'some[column]' to fit <input> names
     */
    public static function fixValidationErrorsKeys(array $errors): array
    {
        foreach ($errors as $key => $messages) {
            if (str_contains($key, '.')) {
                /** @noinspection RegExpRedundantEscape */
                $newKey = preg_replace(
                    ['%^([^\]]+)\]%', '%\[\]\]%'],
                    ['$1', '][]'],
                    str_replace('.', '][', $key) . ']'
                );
                $errors[$newKey] = $messages;
                unset($errors[$key]);
            }
        }
        
        return $errors;
    }
    
}