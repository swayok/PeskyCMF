<?php

namespace PeskyCMF\Traits;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use Swayok\Utils\Set;

trait DataValidationHelper {

    protected function validate($data, $rules, $messages = array(), $customAttributes = array()) {
        $messages = Set::flatten($messages);
        $validator = \Validator::make($data, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            return $this->sendValidationErrorsResponse($validator->getMessageBag()->toArray());
        }
        return true;
    }

    protected function getValidationErrorsResponseMessage() {
        return CmfConfig::transBase('.error.invalid_data_received');
    }

    protected function sendValidationErrorsResponse(array $errors) {
        abort(
            HttpCode::INVALID,
            json_encode($this->prepareDataForValidationErrorsResponse($errors), JSON_UNESCAPED_UNICODE)
        );
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
                $newKey = preg_replace('%^([^\]]+)\]%', '$1', str_replace('.', '][', $key) . ']');
                $errors[$newKey] = $messages;
                unset($errors[$key]);
            }
        }
        return $errors;
    }

}