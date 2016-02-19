<?php

namespace PeskyCMF;

use Illuminate\Validation\ValidationServiceProvider;
use PeskyCMF\Db\DatabasePresenceVerifier;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Validator;

class PeskyValidationServiceProvider extends ValidationServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        // image validation: 'img', 'img:jpeg,png'
        Validator::extend('img', function ($attribute, $value, $parameters) {
            $file = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            if (empty($parameters)) {
                $validators = 'required|image';
            } else {
                $validators =  'required|mimes:' . (is_array($parameters) ? implode(',', $parameters) : $parameters);
            }
            $isValid = Validator::make(
                [$attribute => $file],
                [$attribute => $validators]
            )->passes();
            return $isValid;
        });
        // file validation: 'file:jpeg,png'
        Validator::extend('file', function ($attribute, $value, $parameters) {
            if (count($parameters) < 1) {
                throw new \InvalidArgumentException("Validation rule file requires at least 1 parameter.");
            }
            $file = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            $isValid = Validator::make(
                [$attribute => $file],
                [$attribute => 'required|mimes:' . (is_array($parameters) ? implode(',', $parameters) : $parameters)]
            )->passes();
            return $isValid;
        });
        // file extension validation: 'ext', 'ext:min', 'ext:min,max'
        Validator::extend('ext', function ($attribute, $value, $parameters) {
            if (isset($parameters[0]) && (!is_numeric($parameters[0]) || intval($parameters) < 1)) {
                throw new \InvalidArgumentException("Validation rule ext requires that 1st parameter (min) must be a positive number > 0.");
            }
            $min = isset($parameters[0]) && is_numeric($parameters[0]) ? intval($parameters[0]) : 1;
            $max = isset($parameters[1]) && is_numeric($parameters[1]) ? intval($parameters[1]) : '';
            return (
                is_array($value)
                && !empty($value['name'])
                && preg_match('%\.[a-zA-Z0-9]{' . $min . ',' . $max . '}$%i', $value['name'])
            );
        });
        // file size validation: 'filesize:min' 'filesize:min,max', max = 0 means no limitation
        Validator::extend('filesize', function ($attribute, $value, $parameters) {
            if (count($parameters) < 1) {
                throw new \InvalidArgumentException("Validation rule filesize requires at least 1 parameter.");
            }
            if (!is_numeric($parameters[0]) || intval($parameters) < 0) {
                throw new \InvalidArgumentException("Validation rule filesize requires that 1st parameter (min) must be a positive number or 0.");
            }
            if (!empty($parameters[1]) && (!is_numeric($parameters[1]) || intval($parameters) < 0)) {
                throw new \InvalidArgumentException("Validation rule filesize requires that 2nd parameter (max) must be a positive number or 0.");
            }
            $min = intval($parameters[0]);
            $max = empty($parameters[1]) ? 0 : intval($parameters[1]);
            if ($max > 0 && $min > $max) {
                throw new \InvalidArgumentException("Validation rule filesize requires that 2nd parameter (max) must be greater then 1st parameter (min) or 0.");
            }
            if (!is_array($value) || empty($value['size'])) {
                return false;
            }
            return (
                $value['size'] / 1024 >= $min
                && ($max === 0 || $value['size'] / 1024 <= $max)
            );
        });

        // messages for validators
        $replacer = function ($message, $attribute, $rule, $parameters) {
            return str_replace(':values', implode(', ', $parameters), $message);
        };
        Validator::replacer('img', $replacer);
        Validator::replacer('file', $replacer);
        Validator::replacer('ext', function ($message, $attribute, $rule, $parameters) {
            return str_replace(
                [':min', ':max'],
                [
                    isset($parameters[0]) && is_numeric($parameters[0]) ? intval($parameters[0]) : 1,
                    isset($parameters[1]) && is_numeric($parameters[1]) ? intval($parameters[1]) : ''
                ],
                $message
            );
        });
        Validator::replacer('filesize', function ($message, $attribute, $rule, $parameters) {
            $min = intval($parameters[0]);
            $max = intval($parameters[1]);
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

    protected function registerPresenceVerifier() {
        $this->app->singleton('validation.presence', function ($app) {
            return new DatabasePresenceVerifier();
        });
    }


}