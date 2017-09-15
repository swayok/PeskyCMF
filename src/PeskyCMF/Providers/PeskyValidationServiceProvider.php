<?php

namespace PeskyCMF\Providers;

use Illuminate\Validation\ValidationServiceProvider;
use PeskyCMF\Db\DatabasePresenceVerifier;
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
        $this->addCaseInsensitiveUniquenessValidator();
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

    protected function addCaseInsensitiveUniquenessValidator() {
        Validator::extend('unique_ceseinsensitive', function ($attribute, $value, $parameters) {
            $validator = Validator::make([$attribute => $value], [$attribute => 'unique:' . implode(',', $parameters)]);
            $verifier = new DatabasePresenceVerifier();
            $validator->setPresenceVerifier($verifier->enableCaseInsensitiveMode());
            return $validator->passes();
        });
        Validator::replacer('unique_ceseinsensitive', function ($message, $attribute, $rule, $parameters) {
            return trans('validation.unique', ['attribute' => $attribute]);
        });
    }

}