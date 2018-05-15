<?php

namespace PeskyCMF\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\HttpCode;
use PeskyCMF\Listeners\CmfUserAuthenticatedEventListener;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;

class CmfAuthModule {

    use DataValidationHelper;

    private static $instance;

    protected $authPolicyName = 'CmfAccessPolicy';
    protected $emailColumnName;
    protected $passwordRevoceryEmailViewPath = 'cmf::emails.password_restore_instructions';

    final static public function getInstance() {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    protected function __construct() {}

    public function init() {
        $this->configureAuthorizationGatesAndPolicies();
        \Auth::shouldUse($this->getAuthGuardName());
        $this->addAuthEventListener();
    }

    /**
     * @return \PeskyCMF\Db\Admins\CmfAdmin|\Illuminate\Contracts\Auth\Authenticatable|\PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey|\App\Db\Admins\Admin|null
     */
    public function getUser() {
        return $this->getAuthGuard()->user();
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Auth\SessionGuard
     */
    public function getAuthGuard() {
        return \Auth::guard($this->getAuthGuardName());
    }

    /**
     * @param string $email
     * @return bool
     */
    public function loginOnceUsingEmail($email) {
        return $this->getAuthGuard()->once([$this->getUserEmailColumnName() => $email]);
    }

    /**
     * @return string
     */
    public function renderLoginPageView() {
        return view($this->getCmfConfig()->login_view())->render();
    }

    /**
     * @param Request $request
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function processUserLoginRequest(Request $request) {
        $userLoginColumn = $this->getCmfConfig()->user_login_column();
        $data = $this->validate($request, [
            $userLoginColumn => 'required' . ($userLoginColumn === 'email' ? '|email' : ''),
            'password' => 'required',
        ]);
        $credentials = [
            DbExpr::create("LOWER(`{$userLoginColumn}`) = LOWER(``" . trim($data[$userLoginColumn]) . '``)'),
            'password' => $data['password'],
        ];
        if (!$this->getAuthGuard()->attempt($credentials)) {
            return cmfJsonResponse(HttpCode::INVALID)
                ->setMessage(cmfTransCustom('.login_form.login_failed'));
        } else {
            return cmfJsonResponse()->setRedirect($this->getIntendedUrl());
        }
    }

    /**
     * @param Request $request
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function startPasswordRecoveryProcess(Request $request) {
        $data = $this->validate($request, [
            'email' => 'required|email',
        ]);
        $email = strtolower(trim($data['email']));
        if ($this->loginOnceUsingEmail($email)) {
            /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $user */
            $user = $this->getAuthGuard()->getLastAttempted();
            if (!method_exists($user, 'getPasswordRecoveryAccessKey')) {
                throw new \BadMethodCallException(
                    'Class ' . get_class($user) . ' does not support password recovery. You should add ' .
                    ResetsPasswordsViaAccessKey::class . ' trait to specified class to enable this functionality'
                );
            }
            $this->sendPasswordRecoveryInstructionsEmail($user);
        }

        return cmfJsonResponse()
            ->setMessage(cmfTransCustom('.forgot_password.instructions_sent'))
            ->setRedirect($this->getCmfConfig()->login_page_url());
    }

    /**
     * @param Request $request
     * @param $accessKey
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\OrmException
     */
    public function finishPasswordRecoveryProcess(Request $request, $accessKey) {
        $data = $this->validate($request, [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password',
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->getPrimaryKeyValue() !== $data['id']) {
            /** @var CmfDbRecord $user */
            $user
                ->begin()
                ->updateValue('password', $data['password'], false)
                ->commit();
            return cmfJsonResponse()
                ->setMessage(cmfTransCustom('.replace_password.password_replaced'))
                ->setForcedRedirect(static::getCmfConfig()->login_page_url());
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setForcedRedirect(static::getCmfConfig()->login_page_url());
        }
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function renderForgotPasswordPageView() {
        return view($this->getCmfConfig()->forgot_password_view())->render();
    }

    /**
     * @param $accessKey
     * @return \PeskyCMF\Http\CmfJsonResponse|string
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \Throwable
     */
    public function renderReplacePasswordPageView($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view($this->getCmfConfig()->replace_password_view(), [
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue(),
                'userLogin' => $user->getValue($this->getCmfConfig()->user_login_column())
            ])->render();
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect($this->getCmfConfig()->login_page_url());
        }
    }

    /**
     * @param ResetsPasswordsViaAccessKey|RecordInterface $user
     */
    protected function sendPasswordRecoveryInstructionsEmail(RecordInterface $user) {
        $subject = cmfTransCustom('.forgot_password.email_subject');
        $from = $this->getCmfConfig()->system_email_address();
        $to = $user->getValue($this->getUserEmailColumnName());
        \Mail::send(
            $this->getPasswordRecoveryEmailViewPath(),
            [
                'url' => cmfRoute('cmf_replace_password', [$user->getPasswordRecoveryAccessKey()], true),
                'user' => $user->toArrayWithoutFiles(),
            ],
            function (Message $message) use ($from, $to, $subject) {
                $message
                    ->from($from)
                    ->to($to)
                    ->subject($subject);
            }
        );
    }

    /**
     * @param $accessKey
     * @return bool|CmfDbRecord
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getUserFromPasswordRecoveryAccessKey($accessKey) {
        /** @var ResetsPasswordsViaAccessKey $userClass */
        $userClass = $this->getCmfConfig()->user_record_class();
        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    /**
     * @param RecordInterface $user
     * @return string
     * @throws \BadMethodCallException
     */
    protected function getUserEmailColumnName() {
        if (!$this->emailColumnName) {
            $usersTablestructure = $this->getCmfConfig()->users_table()->getTableStructure();
            $colName = null;
            if ($usersTablestructure::hasColumn('email')) {
                $this->emailColumnName = 'email';
            } else if ($usersTablestructure::getColumn($this->getCmfConfig()->user_login_column())->getType() === Column::TYPE_EMAIL) {
                $this->emailColumnName = $this->getCmfConfig()->user_login_column();
            } else {
                throw new \BadMethodCallException('There is no known email column to use');
            }
        }
        return $this->emailColumnName;
    }

    /**
     * @return CmfConfig
     */
    protected function getCmfConfig() {
        return CmfConfig::getPrimary();
    }

    /**
     * @return string
     */
    protected function getAuthGuardName() {
        return $this->getCmfConfig()->auth_guard_name();
    }

    /**
     * In this method you should place authorisation gates and policies according to Laravel's docs:
     * https://laravel.com/docs/5.4/authorization
     * Predefined authorisation tests are available for:
     * 1. Resources (scaffolds) - use
     *      Gate::resource('resource', 'AdminAccessPolicy', [
     *          'view' => 'view',
     *          'details' => 'details',
     *          'create' => 'create',
     *          'update' => 'update',
     *          'delete' => 'delete',
     *          'update_bulk' => 'update_bulk',
     *          'delete_bulk' => 'delete_bulk',
     *      ]);
     *      or Gate::define('resource.{ability}', \Closure) to provide rules for some resource.
     *      List of abilities used in scaffolds:
     *      - 'view' is used for routes named 'cmf_api_get_items' and 'cmf_api_get_templates',
     *      - 'details' => 'cmf_api_get_item',
     *      - 'create' => 'cmf_api_create_item',
     *      - 'update' => 'cmf_api_update_item'
     *      - 'update_bulk' => 'cmf_api_edit_bulk'
     *      - 'delete' => 'cmf_api_delete_item'
     *      - 'delete_bulk' => 'cmf_api_delete_bulk'
     *      - 'custom_page' => 'cmf_resource_custom_page'
     *      - 'custom_action' => 'cmf_api_resource_custom_action'
     *      - 'custom_page_for_item' => 'cmf_item_custom_page'
     *      - 'custom_action_for_item' => 'cmf_api_item_custom_action'
     *      For all abilities you will receive $tableName argument and RecordInterface $record or int $itemId argument
     *      for 'details', 'update' and 'delete' abilities.
     *      For KeyValueScaffoldConfig for 'update' ability you will receive $fkValue instead of $itemId/$record.
     *      For 'update_bulk' and 'delete_bulk' you will receive $conditions array.
     *      Note that $tableName passed to abilities is the name of the DB table used in routes and may differ from
     *      the real name of the table provided in TableStructure.
     *      For example: you have 2 resources named 'pages' and 'elements'. Both refer to PagesTable class but
     *      different ScaffoldConfig classes (PagesScaffoldConfig and ElementsScafoldConfig respectively).
     *      In this case $tableName will be 'pages' for PagesScaffoldConfig and 'elements' for ElementsScafoldConfig.
     *      Note: If you forbid 'view' ability - you will forbid everything else
     *      Note: there is no predefined authorization for routes based on 'cmf_item_custom_page'. You need to add it
     *      manually to controller's action that handles that custom page
     * 2. CMF Pages - use Gate::define('cmf_page', 'AdminAccessPolicy@cmf_page')
     *      Abilities will receive $pageName argument - it will contain the value of the {page} property in route
     *      called 'cmf_page' (url is '/{prefix}/page/{page}' by default)
     * 3. Admin profile update - Gate::define('profile.update', \Closure);
     *
     * For any other routes where you resolve authorisation by yourself - feel free to use any naming you want
     *
     * @param string $policyName
     */
    protected function configureAuthorizationGatesAndPolicies() {
        app()->singleton($this->authPolicyName, $this->getCmfConfig()->cmf_user_acceess_policy_class());
        \Gate::resource('resource', $this->authPolicyName, [
            'view' => 'view',
            'details' => 'details',
            'create' => 'create',
            'update' => 'update',
            'edit' => 'edit',
            'delete' => 'delete',
            'update_bulk' => 'update_bulk',
            'delete_bulk' => 'delete_bulk',
            'other' => 'others',
            'others' => 'others',
            'custom_page' => 'custom_page',
            'custom_action' => 'custom_action',
            'custom_page_for_item' => 'custom_page_for_item',
            'custom_action_for_item' => 'custom_action_for_item',
        ]);
        \Gate::define('cmf_page', $this->authPolicyName . '@cmf_page');
    }

    protected function addAuthEventListener() {
        \Event::listen(CmfUserAuthenticated::class, CmfUserAuthenticatedEventListener::class);
    }

    /**
     * @return string
     */
    protected function getPasswordRecoveryEmailViewPath() {
        return $this->passwordRevoceryEmailViewPath;
    }

    /**
     * @param string $url
     */
    public function saveIntendedUrl($url) {
        session()->put($this->getCmfConfig()->makeUtilityKey('intended_url'), $url);
    }

    /**
     * @return mixed|string
     */
    public function getIntendedUrl() {
        $intendedUrl = session()->pull($this->getCmfConfig()->makeUtilityKey('intended_url'), false);
        if (!$intendedUrl) {
            return $this->getCmfConfig()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/service/%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/([^/?]+?)/?(?:\?details=(\d)|$)%i', $intendedUrl, $matches)) {
                if (!empty($matches[3]) && $matches[3] === '1') {
                    return routeToCmfItemDetails($matches[1], $matches[2]);
                } else {
                    return routeToCmfItemEditForm($matches[1], $matches[2]);
                }
            } else if (preg_match('%/api/([^/]+?)%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/page/([^/]+)\.html$%i', $intendedUrl, $matches)) {
                return routeToCmfPage($matches[1]);
            } else {
                return $intendedUrl;
            }
        }
    }

}