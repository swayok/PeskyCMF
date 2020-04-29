<?php

namespace PeskyCMF;

use Illuminate\Validation\ValidationServiceProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;

class PeskyCmfValidationServiceProvider extends ValidationServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \InvalidArgumentException
     */
    public function boot() {
        $this->addImgValidator();
        $this->addFileValidator();
        $this->addFileExtensionValidator();
        $this->getFileSizeValidator();
        $this->addAlternativeExistsValidator();
    }

    /**
     * Image validation: 'img', 'img:jpeg,png'
     */
    protected function addImgValidator() {
        Validator::extend('img', function ($attribute, $value, $parameters) {
            if (!($value instanceof UploadedFile)) {
                $value = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            }
            $validators = 'required|image';
            if (!empty($parameters)) {
                $validators .= '|mimes:' . (is_array($parameters) ? implode(',', $parameters) : $parameters);
            }
            $isValid = Validator::make(
                [$attribute => $value],
                [$attribute => $validators]
            )->passes();
            return $isValid;
        });
        Validator::replacer('img', $this->getDefaultReplacer());
    }

    /**
     * File validation: 'filetype:jpeg,png'
     */
    protected function addFileValidator() {
        Validator::extend('filetype', function ($attribute, $value, $parameters) {
            if (count($parameters) < 1) {
                throw new \InvalidArgumentException('Validation rule file requires at least 1 parameter.');
            }
            if (!($value instanceof UploadedFile)) {
                $value = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            }
            $isValid = Validator::make(
                [$attribute => $value],
                [$attribute => 'required|mimes:' . (is_array($parameters) ? implode(',', $parameters) : $parameters)]
            )->passes();
            return $isValid;
        });
        Validator::replacer('filetype', $this->getDefaultReplacer());
    }

    /**
     * File extension validation: 'ext', 'ext:min', 'ext:min,max'
     */
    protected function addFileExtensionValidator() {
        Validator::extend('ext', function ($attribute, $value, $parameters) {
            if (isset($parameters[0]) && (!is_numeric($parameters[0]) || (int)$parameters < 1)) {
                throw new \InvalidArgumentException('Validation rule ext requires that 1st parameter (min) must be a positive number > 0.');
            }
            $min = isset($parameters[0]) && is_numeric($parameters[0]) ? (int)$parameters[0] : 1;
            $max = isset($parameters[1]) && is_numeric($parameters[1]) ? (int)$parameters[1] : '';
            if ($value instanceof UploadedFile) {
                $extLen = strlen($value->getClientOriginalExtension());
                return ($extLen >= $min && $extLen <= $max);
            } else if (is_array($value) && !empty($value['name'])) {
                return preg_match('%\.[a-zA-Z0-9]{' . $min . ',' . $max . '}$%i', $value['name']);
            }
            return false;
        });
        Validator::replacer('ext', function ($message, $attribute, $rule, $parameters) {
            return str_replace(
                [':min', ':max'],
                [
                    isset($parameters[0]) && is_numeric($parameters[0]) ? (int)$parameters[0] : 1,
                    isset($parameters[1]) && is_numeric($parameters[1]) ? (int)$parameters[1] : ''
                ],
                $message
            );
        });
    }

    /**
     * File size validation: 'filesize:min' 'filesize:min,max', max = 0 means no limitation
     */
    protected function getFileSizeValidator() {
        Validator::extend('filesize', function ($attribute, $value, $parameters) {
            $this->requireParameterCount(1, $parameters, 'filesize');
            if (!is_numeric($parameters[0]) || (int)$parameters < 0) {
                throw new \InvalidArgumentException('Validation rule filesize requires that 1st parameter (min) must be a positive number or 0.');
            }
            if (!empty($parameters[1]) && (!is_numeric($parameters[1]) || (int)$parameters < 0)) {
                throw new \InvalidArgumentException('Validation rule filesize requires that 2nd parameter (max) must be a positive number or 0.');
            }
            $min = (int)$parameters[0];
            $max = empty($parameters[1]) ? 0 : (int)$parameters[1];
            if ($max > 0 && $min > $max) {
                throw new \InvalidArgumentException('Validation rule filesize requires that 2nd parameter (max) must be greater then 1st parameter (min) or 0.');
            }
            if (!($value instanceof UploadedFile)) {
                if (!is_array($value)) {
                    return false;
                }
                $value = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            }
            if (!$value->getSize()) {
                return false;
            }
            return (
                $value->getSize() / 1024 >= $min
                && ($max === 0 || $value->getSize() / 1024 <= $max)
            );
        });
        Validator::replacer('filesize', function ($message, $attribute, $rule, $parameters) {
            $min = (int)$parameters[0];
            $max = (int)$parameters[1];
            if (is_array($message)) {
                if ($min > 0 && $max > 0) {
                    $message = isset($message['range']) ? $message['range'] : 'range';
                } else if ($min === 0) {
                    $message = isset($message['max']) ? $message['max'] : 'max';
                } else if ($max === 0) {
                    $message = isset($message['min']) ? $message['min'] : 'min';
                }
            }
            return str_replace([':min', ':max'], [$min, $max], $message);
        });
    }

    /**
     * Alternative 'exists' validator that uses default laravel's DatabasePresenceVerifier
     * Error message is: trans('validation.exists', ['attribute' => $attribute])
     */
    protected function addAlternativeExistsValidator() {
        Validator::extend('exists2', function ($attribute, $value, $parameters) {
            $validator = Validator::make([$attribute => $value], [$attribute => 'exists:' . implode(',', $parameters)]);
            $validator->setPresenceVerifier(new \Illuminate\Validation\DatabasePresenceVerifier(app('db')));
            return $validator->passes();
        });
        Validator::replacer('exists2', function ($message, $attribute, $rule, $parameters) {
            return trans('validation.exists', ['attribute' => $attribute]);
        });
    }

    protected function requireParameterCount($count, $parameters, $rule) {
        if (count($parameters) < $count) {
            throw new \InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
        }
    }

    protected function getDefaultReplacer() {
        return function ($message, $attribute, $rule, $parameters) {
            return str_replace(':values', implode(', ', $parameters), $message);
        };
    }




}