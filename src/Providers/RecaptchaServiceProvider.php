<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\Curl;

class RecaptchaServiceProvider extends ServiceProvider
{
    
    protected function getValidator(): ValidationFactoryContract
    {
        return $this->app->make('validator');
    }
    
    protected function getSessionStore(): Store
    {
        return $this->app->make('session.store');
    }
    
    protected function getRequest(): Request
    {
        return $this->app->make('request');
    }
    
    public function boot()
    {
        // validator usage: ['g-recaptcha-response' => 'recaptcha']
        // Note that 'required|string' may be omitted.
        // Error translation is: 'validation.recaptcha'
        $this->getValidator()->extend('recaptcha', function ($attribute, $value, $parameters) {
            if (empty($value)) {
                return false;
            }
            // accept duplicate submits for some time
            $cmfConfig = CmfConfig::getPrimary();
            $isValid = $this->getSessionStore()->get($cmfConfig::url_prefix() . '-recaptcha', null);
            if (
                !is_array($isValid)
                || empty($isValid['expires_at'])
                || empty($isValid['key'])
                || $isValid['key'] !== $value
                || $isValid['expires_at'] < time()
            ) {
                $isValid = static::validate($cmfConfig::recaptcha_private_key(), $value, $this->getRequest()->getClientIp());
            }
            if (is_array($isValid)) {
                $isValid['key'] = $value;
                $isValid['expires_at'] = time() + 120;
                $this->getSessionStore()->put($cmfConfig::url_prefix() . '-recaptcha', $isValid);
                return true;
            }
            return false;
        });
    }
    
    public function register()
    {
    }
    
    /**
     * @param string $secret - secret key for recaptcha
     * @param string $answer - user's answer
     * @param string|null $clientIp
     * @return array|null
     * - null: invalid or failed to validate
     * - array: ['success' => bool, 'challenge_ts' => string, 'host_name' => string]
     */
    public static function validate(string $secret, string $answer, ?string $clientIp = null): ?array
    {
        $data = [
            'secret' => $secret,
            'response' => $answer,
        ];
        if (!empty($clientIp)) {
            $data['remoteip'] = $clientIp;
        }
        $curlResponse = Curl::curlExec('https://www.google.com/recaptcha/api/siteverify', $data);
        if (!Curl::isValidResponse($curlResponse)) {
            return null;
        } else {
            $json = json_decode($curlResponse['data'], true);
            if (empty($json) || empty($json['success']) || !empty($json['error-codes'])) {
                return null;
            }
            return $json;
        }
    }
}