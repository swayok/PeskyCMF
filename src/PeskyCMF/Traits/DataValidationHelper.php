<?php

namespace PeskyCMF\Traits;

use Illuminate\Validation\ValidationException;
use PeskyCMF\HttpCode;
use Swayok\Utils\Set;

trait DataValidationHelper {

    /**
     * Validate data and send Validation Errors Response (abort(HttpCode::INVALID)) if it is invalid
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return bool
     */
    protected function validate(array $data, array $rules, array $messages = array(), array $customAttributes = array()) {
        $errors = $this->validateWithoutHalt($data, $rules, $messages, $customAttributes);
        return $errors === true ? true : $this->sendValidationErrorsResponse($errors);
    }

    /**
     * Validate data and returm errors array if it is invalid
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array|bool
     */
    protected function validateWithoutHalt(array $data, array $rules, array $messages = array(), array $customAttributes = array()) {
        $messages = Set::flatten($messages);
        $validator = \Validator::make($data, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            return $validator->getMessageBag()->toArray();
        }
        return true;
    }

    protected function getValidationErrorsResponseMessage() {
        return cmfTransGeneral('.error.invalid_data_received');
    }

    protected function sendValidationErrorsResponse(array $errors) {
        throw ValidationException::withMessages($errors);
        return true;
    }

    protected function prepareDataForValidationErrorsResponse(array $errors) {
        $message = array_get($errors, '_message', $this->getValidationErrorsResponseMessage());
        unset($errors['_message']);
        return [
            '_message' => $message,
            'errors' => static::fixValidationErrorsKeys($errors)
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