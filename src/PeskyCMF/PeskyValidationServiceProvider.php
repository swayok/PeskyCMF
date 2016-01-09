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
        Validator::extend('img', function ($attribute, $value, $parameters) {
            $file = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            $isValid = Validator::make([$attribute => $file], [$attribute => 'required|image'])->passes();
            return $isValid;
        });
        Validator::extend('file', function ($attribute, $value, $parameters) {
            $file = new UploadedFile($value['tmp_name'], $value['name'], $value['type'], $value['size'], $value['error']);
            $isValid = Validator::make(
                [$attribute => $file],
                [$attribute => 'required|mimes:' . (is_array($parameters) ? implode(',', $parameters) : $parameters)]
            )->passes();
            return $isValid;
        });
        Validator::extend('ext', function ($attribute, $value, $parameters) {
            $min = isset($parameters[0]) && is_numeric($parameters[0]) ? intval($parameters[0]) : 1;
            $max = isset($parameters[1]) && is_numeric($parameters[1]) ? intval($parameters[1]) : '';
            return (
                is_array($value)
                && !empty($value['name'])
                && preg_match('%\.[a-zA-Z0-9]{' . $min . ',' . $max . '}$%i', $value['name'])
            );
        });
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
    }

    protected function registerPresenceVerifier() {
        $this->app->singleton('validation.presence', function ($app) {
            return new DatabasePresenceVerifier();
        });
    }


}