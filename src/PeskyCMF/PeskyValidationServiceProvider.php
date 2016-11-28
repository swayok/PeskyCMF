<?php

namespace PeskyCMF;

use Illuminate\Validation\ValidationServiceProvider;
use Validator;

class PeskyValidationServiceProvider extends ValidationServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \InvalidArgumentException
     */
    public function boot() {
        $this->addAlternativeExistsValidator();
    }

    protected function registerPresenceVerifier() {
        $this->app->singleton('validation.presence', function ($app) {
            return new \PeskyCMF\Db\DatabasePresenceVerifier();
        });
    }

    /**
     * Alternative 'exists' validator that uses default laravel's DatabasePresenceVerifier
     * Error message is: trans('validation.exists', ['attribute' => $attribute])
     */
    protected function addAlternativeExistsValidator() {
        Validator::extend('exists-eloquent', function ($attribute, $value, $parameters) {
            $validator = Validator::make([$attribute => $value], [$attribute => 'exists:' . implode(',', $parameters)]);
            $validator->setPresenceVerifier(new \Illuminate\Validation\DatabasePresenceVerifier(app('db')));
            return $validator->passes();
        });
        Validator::replacer('exists-eloquent', function ($message, $attribute, $rule, $parameters) {
            return trans('validation.exists', ['attribute' => $attribute]);
        });
    }

}