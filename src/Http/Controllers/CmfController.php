<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\Contracts\ResetsPasswordsViaAccessKey;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\ORM\Record\RecordInterface;

abstract class CmfController extends Controller
{
    use DataValidationHelper;
    use AuthorizesRequests;

    protected CmfConfig $cmfConfig;
    protected Application $app;

    public function __construct(CmfConfig $cmfConfig, Application $app)
    {
        $this->cmfConfig = $cmfConfig;
        $this->app = $app;
    }

    public function getAuthGuard(): Guard
    {
        return $this->getCmfConfig()->getAuthGuard();
    }

    /**
     * @return Authenticatable|CmfAdmin|ResetsPasswordsViaAccessKey|RecordInterface
     * @noinspection PhpDocSignatureInspection
     */
    public function getUser(): RecordInterface
    {
        $user = $this->getCmfConfig()->getUser();
        if (!$user) {
            throw new \BadMethodCallException(
                'User is not authenticated.'
                . ' Use CmfController::isUserAuthenticated() to check auth status.'
            );
        }
        return $user;
    }

    public function isUserAuthenticated(): bool
    {
        return $this->getCmfConfig()->getUser() !== null;
    }

    public function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }
}
