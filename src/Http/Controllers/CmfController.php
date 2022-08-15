<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Traits\DataValidationHelper;

class CmfController extends Controller {

    use DataValidationHelper,
        AuthorizesRequests;

    public static function getAuthGuard() {
        return static::getCmfConfig()->getAuthGuard();
    }

    /**
     * @return \App\Db\Admins\Admin|\Illuminate\Contracts\Auth\Authenticatable|\PeskyCMF\Db\Admins\CmfAdmin|\PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey
     */
    public static function getUser() {
        $user = static::getCmfConfig()->getUser();
        if (empty($user)) {
            throw new \BadMethodCallException(
                'User is not authenticated. Use CmfController::isUserAuthenticated() to check auth status.'
            );
        }
        return $user;
    }

    public static function isUserAuthenticated(): bool {
        return static::getCmfConfig()->getUser() !== null;
    }

    /**
     * @return CmfConfig
     */
    public static function getCmfConfig() {
        return CmfConfig::getPrimary();
    }
}