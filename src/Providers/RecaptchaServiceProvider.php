<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use Swayok\Utils\Curl;

class RecaptchaServiceProvider extends ServiceProvider
{
    public function boot(
        Request $request,
        ValidationFactoryContract $validator,
        SessionStore $sessionStore
    ): void {
        // validator usage: ['g-recaptcha-response' => 'recaptcha']
        // Note that 'required|string' may be omitted.
        // Error translation is: 'validation.recaptcha'
        $validator->extend('recaptcha', function ($attribute, $value) use ($request, $sessionStore) {
            if (empty($value)) {
                return false;
            }
            // accept duplicate submits for some time
            $isValid = $sessionStore->get($this->getCmfConfig()->urlPrefix() . '-recaptcha', null);
            if (
                !is_array($isValid)
                || empty($isValid['expires_at'])
                || empty($isValid['key'])
                || $isValid['key'] !== $value
                || $isValid['expires_at'] < time()
            ) {
                $isValid = static::validate(
                    $this->getCmfConfig()->recaptchaPrivateKey(),
                    $value,
                    $request->getClientIp()
                );
            }
            if (is_array($isValid)) {
                $isValid['key'] = $value;
                $isValid['expires_at'] = time() + 120;
                $sessionStore->put($this->getCmfConfig()->urlPrefix() . '-recaptcha', $isValid);
                return true;
            }
            return false;
        });
    }

    public function register(): void
    {
    }

    /**
     * @param string      $secret - secret key for recaptcha
     * @param string      $answer - user's answer
     * @param string|null $clientIp
     * @return array|null
     * - null: invalid or failed to validate
     * - array: ['success' => bool, 'challenge_ts' => string, 'host_name' => string]
     */
    public static function validate(
        string $secret,
        string $answer,
        ?string $clientIp = null
    ): ?array {
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
        }

        $json = json_decode($curlResponse['data'], true);
        if (empty($json) || empty($json['success']) || !empty($json['error-codes'])) {
            return null;
        }
        return $json;
    }

    private function getCmfConfig(): CmfConfig
    {
        return $this->app->make(CmfConfig::class);
    }
}
