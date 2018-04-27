<?php

namespace PeskyCMF\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use PeskyCMF\HttpCode;
use Swayok\Utils\Set;

trait DataValidationHelper {

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory() {
        return app(Factory::class);
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
    public function validate($dataOrRequest, array $rules, array $messages = [], array $customAttributes = []) {
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
    public function validateAndReturnErrors($dataOrRequest, array $rules, array $messages = [], array $customAttributes = []) {
        $messages = Set::flatten($messages);
        if ($dataOrRequest instanceof Request) {
            $dataOrRequest = $dataOrRequest->all();
        }
        $validator = $this->getValidationFactory()->make($dataOrRequest, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            return $validator->getMessageBag()->toArray();
        }

        return [];
    }

    /**
     * Get the request input based on the given validation rules.
     *
     * @param array|Request $data
     * @param array $rules
     * @return array
     */
    protected function extractInputFromRules($data, array $rules) {
        $keys = collect($rules)->keys()->map(function ($rule) {
            return explode('.', $rule)[0];
        });
        if (!($data instanceof Request)) {
            $data = collect($data);
        }
        return $data->only($keys)->unique()->toArray();
    }

    protected function getValidationErrorsResponseMessage() {
        return cmfTransGeneral('.message.invalid_data_received');
    }

    /**
     * @param array $errors
     * @return Response
     */
    public function makeValidationErrorsJsonResponse(array $errors) {
        return response()->json($this->prepareDataForValidationErrorsResponse($errors), HttpCode::CANNOT_PROCESS);
    }

    /**
     * @param array $errors
     * @throws ValidationException
     */
    public function throwValidationErrorsResponse(array $errors) {
        throw new ValidationException(\Validator::make([], []), $this->makeValidationErrorsJsonResponse($errors));
    }

    public function prepareDataForValidationErrorsResponse(array $errors) {
        $message = array_get($errors, '_message', $this->getValidationErrorsResponseMessage());
        unset($errors['_message']);

        return [
            '_message' => $message,
            'errors' => static::fixValidationErrorsKeys($errors),
        ];
    }

    /**
     * Replace keys like 'some.column' by 'some[column]' to fit <input> names
     * @param array $errors
     * @return array
     */
    static public function fixValidationErrorsKeys(array $errors) {
        foreach ($errors as $key => $messages) {
            if (strpos($key, '.') !== false) {
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