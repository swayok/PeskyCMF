<?php

namespace PeskyCMF\Providers;

use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\Curl;

class RecaptchaServiceProvider extends ServiceProvider {

    public function boot() {
        \Validator::extend('recaptcha', function ($attribute, $value, $parameters) {
            if (empty($value)) {
                return false;
            }
            // accept duplicate submits for some time
            $cmfConfig = CmfConfig::getPrimary();
            $isValid = \Session::get($cmfConfig::url_prefix() . '-recaptcha', null);
            if (
                $isValid === null
                || !is_array($isValid)
                || empty($isValid['expires_at'])
                || empty($isValid['key'])
                || $isValid['key'] !== $value
                || $isValid['expires_at'] < time()
            ) {
                $isValid = static::valdate($cmfConfig::recaptcha_private_key(), $value, request()->getClientIp());
            }
            if (!empty($isValid) && is_array($isValid)) {
                $isValid['key'] = $value;
                $isValid['expires_at'] = time() + 120;
                \Session::put($cmfConfig::url_prefix() . '-recaptcha', $isValid);
                return true;
            }
            return false;
        });
    }

    public function register() {

    }

    /**
     * @param string $secret - secret key for recaptcha
     * @param string $answer - user's answer
     * @param string|null $clientIp
     * @return array|bool - false: invalid or failed to validate
     * - false: invalid or failed to validate
     * - array: ['success' => bool, 'challenge_ts' => string, 'host_name' => string]
     */
    static public function valdate($secret, $answer, $clientIp = null) {
        $data = [
            'secret' => $secret,
            'response' => $answer,
        ];
        if (!empty($clientIp)) {
            $data['remoteip'] = $clientIp;
        }
        $curlResponse = Curl::curlExec('https://www.google.com/recaptcha/api/siteverify', $data);
        if (!Curl::isValidResponse($curlResponse)) {
            return false;
        } else {
            $json = json_decode($curlResponse['data'], true);
            if (empty($json) || empty($json['success']) || !empty($json['error-codes'])) {
                return false;
            }
            return $json;
        }
    }
}