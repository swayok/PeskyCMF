<?php

namespace PeskyCMF\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\HttpCode;
use PeskyCMF\Listeners\CmfUserAuthenticatedEventListener;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use Swayok\Utils\Set;

class CmfAuthModule {

    use DataValidationHelper,
        AuthorizesRequests;

    protected $cmfConfig;
    protected $authPolicyName = 'CmfAccessPolicy';
    protected $emailColumnName;
    protected $authGuard;
    protected $originalUserFromLoginAsActionSessionKey = '__original_user';
    // page views
    protected $userLoginPageViewPath = 'cmf::ui.login';
    protected $forgotPasswordPageViewPath = 'cmf::ui.forgot_password';
    protected $replacePasswordPageViewPath = 'cmf::ui.replace_password';
    protected $userProfilePageViewPath = 'cmf::page.profile';
    // emails views
    protected $passwordRevoceryEmailViewPath = 'cmf::emails.password_restore_instructions';
    // html elements
    protected $defaultLoginPageLogo = '<img src="/packages/cmf/img/peskycmf-logo-black.svg" width="340" alt=" " class="mb15">';

    public function __construct(CmfConfig $cmfConfig) {
        $this->cmfConfig = $cmfConfig;
    }

    public function init() {
        $this->configureAuthorizationGatesAndPolicies();
        $authGuardName = $this->getAuthGuardName();
        \Auth::shouldUse($authGuardName);
        $this->authGuard = \Auth::guard($authGuardName);
        $this->listenForUserAuthenticationEvents();
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
        return $this->authGuard ?: \Auth::guard($this->getAuthGuardName());
    }

    /**
     * @return string|RecordInterface
     */
    public function getUserRecordClass() {
        return $this->getCmfConfig()->config('auth.user_record_class', function () {
            throw new \UnexpectedValueException('You need to provide a DB Record class for users');
        });
    }

    /**
     * @return \PeskyORM\ORM\TableInterface|\PeskyCMF\Db\Admins\CmfAdminsTable|\App\Db\Admins\AdminsTable
     */
    public function getUsersTable() {
        $recordClass = $this->getUserRecordClass(); //< do not merge with next line!!!
        return $recordClass::getTable();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function loginOnceUsingEmail($email) {
        return $this->getAuthGuard()->once([$this->getUserEmailColumnName() => $email]);
    }

    /**
     * Logo image for login and restore password pages
     * @return string
     */
    public function getLoginPageLogo() {
        return $this->getCmfConfig()->config('auth.login_logo') ?: $this->defaultLoginPageLogo;
    }

    /**
     * @return string
     */
    public function renderUserLoginPageView() {
        if ($this->getUser()) {
            return cmfJsonResponse(HttpCode::MOVED_TEMPORARILY)
                ->setForcedRedirect($this->getIntendedUrl());
        }
        return view($this->userLoginPageViewPath, ['authModule' => $this])->render();
    }

    /**
     * To be implemented manually
     * @return \PeskyCMF\Http\CmfJsonResponse
     */
    public function renderUserRegistrationPageView() {
        return cmfJsonResponse(HttpCode::NOT_FOUND);
    }

    /**
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getDataForUserProfileForm() {
        /** @var CmfAdmin $admin */
        $admin = $this->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        $adminData = $admin->toArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = $admin->role;
            if ($admin::hasColumn('is_superadmin') && $admin->is_superadmin) {
                $role = 'superadmin';
            }
            $adminData['role'] = cmfTransCustom('.admins.role.' . $role);
        }
        return $adminData;
    }

    /**
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function renderUserProfilePageView() {
        $user = $this->getUser();
        $this->authorize('resource.details', ['cmf_profile', $user]);
        return view($this->userProfilePageViewPath, [
            'authModule' => $this,
            'user' => $user,
            'canSubmit' => \Gate::allows('resource.update', ['cmf_profile', $user])
        ])->render();
    }

    /**
     * @param Request $request
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \PeskyORM\Exception\DbException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\OrmException
     */
    public function processUserProfileUpdateRequest(Request $request) {
        /** @var \PeskyCMF\Db\Admins\CmfAdmin $user */
        $user = $this->getUser();
        $this->authorize('resource.update', ['cmf_profile', $user]);
        $updatesOrResponse = $this->validateAndGetUserProfileUpdates($request, $user);
        if (!is_array($updatesOrResponse)) {
            return $updatesOrResponse;
        } else {
            $user
                ->begin()
                ->updateValues($updatesOrResponse);
            if (!empty(trim($request->input('new_password')))) {
                $user->setPassword($request->input('new_password'));
            }
            $user->commit();
            return cmfJsonResponse()
                ->setData(['_reload_user' => true])
                ->setMessage(cmfTransCustom('page.profile.saved'))
                ->reloadPage();
        }
    }

    /**
     * @return string
     */
    public function getAccessPolicyClassName() {
        return $this->getCmfConfig()->config('auth.acceess_policy_class') ?: CmfAccessPolicy::class;
    }

    /**
     * @param Request $request
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function processUserLoginRequest(Request $request) {
        $userLoginColumn = $this->getUserLoginColumnName();
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
     * To be implemented manually
     * @param Request $request
     * @return \PeskyCMF\Http\CmfJsonResponse
     */
    public function processUserRegistrationRequest(Request $request) {
        return cmfJsonResponse(HttpCode::NOT_FOUND);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\PeskyCMF\Http\CmfJsonResponse
     */
    public function processUserLogoutRequest() {
        $loginPageUrl = $this->getLoginPageUrl(true);
        if (\Session::has($this->originalUserFromLoginAsActionSessionKey)) {
            // logout to original account after 'login_as'
            $userInfo = \Session::pull($this->originalUserFromLoginAsActionSessionKey);
            $user = $this->getAuthGuard()->getProvider()->retrieveByToken(
                array_get($userInfo, 'id', -1),
                array_get($userInfo, 'token', -1)
            );
            if ($user) {
                // Warning: do not use Auth->login($user) - it will fail to login previous user
                $this->getAuthGuard()->loginUsingId($user->getAuthIdentifier(), false);
                $redirectTo = array_get($userInfo, 'url') ?: $loginPageUrl;
                return request()->ajax()
                    ? cmfJsonResponse()->setForcedRedirect($redirectTo)
                    : \Redirect::to($redirectTo);
            }
        }
        $this->logoutCurrentUser();
        return request()->ajax()
            ? cmfJsonResponse()->setForcedRedirect($loginPageUrl)
            : \Redirect::to($loginPageUrl);
    }

    /**
     * Logout current user, invalidate session and reset locale
     */
    public function logoutCurrentUser() {
        $this->getAuthGuard()->logout();
        \Session::remove($this->originalUserFromLoginAsActionSessionKey);
        \Session::invalidate();
        $this->getCmfConfig()->resetLocale();
    }

    /**
     * @param $otherUserId
     * @return \PeskyCMF\Http\CmfJsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function processLoginAsOtherUserRequest($otherUserId) {
        $this->authorize('cmf_page', ['login_as']);
        /** @var Authenticatable|RecordInterface $currentUser */
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getAuthIdentifier();
        if ($currentUserId === $otherUserId || $currentUserId === (int)$otherUserId) {
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.same_user'));
        }
        $token = $currentUser->getRememberToken();
        if (!$token) {
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.no_auth_token'));
        }
        /** @var \PeskyCMF\Db\Admins\CmfAdmin|RecordInterface $otherUser */
        $otherUser = $this->getAuthGuard()->loginUsingId($otherUserId);
        if (!is_object($otherUser)) {
            // Warning: do not use Auth->login($currentUser) - it might fail
            $this->getAuthGuard()->loginUsingId($currentUserId, false);
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.fail', ['id' => $otherUserId]));
        }
        \Session::put([
            $this->originalUserFromLoginAsActionSessionKey => [
                'id' => $currentUserId,
                'token' => $token,
                'url' => \URL::previous($this->getCmfConfig()->home_page_url(true)),
            ],
            $this->getCmfConfig()->session_message_key() => cmfTransCustom(
                'admins.login_as.success',
                ['user' => $otherUser->getValue($this->getUserLoginColumnName())]
            )
        ]);
        return cmfJsonResponse()
            ->setRedirect($this->getCmfConfig()->home_page_url());
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
            ->setRedirect($this->getLoginPageUrl());
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
        $this->logoutCurrentUser(); //< to prevent usage of remembered user's session
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->getPrimaryKeyValue() !== $data['id']) {
            /** @var CmfDbRecord $user */
            $user
                ->begin()
                ->updateValue('password', $data['password'], false)
                ->commit();
            return cmfJsonResponse()
                ->setMessage(cmfTransCustom('.replace_password.password_replaced'))
                ->setForcedRedirect($this->getLoginPageUrl());
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setForcedRedirect($this->getLoginPageUrl());
        }
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function renderForgotPasswordPageView() {
        return view($this->forgotPasswordPageViewPath, ['authModule' => $this])->render();
    }

    /**
     * @param $accessKey
     * @return \PeskyCMF\Http\CmfJsonResponse|string
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \Throwable
     */
    public function renderReplaceUserPasswordPageView($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view($this->replacePasswordPageViewPath, [
                'authModule' => $this,
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue(),
                'userLogin' => $user->getValue($this->getUserLoginColumnName())
            ])->render();
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect($this->getLoginPageUrl());
        }
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getLoginPageUrl($absolute = false) {
        return route($this->getCmfConfig()->getRouteName('cmf_login'), [], $absolute);
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getLogoutPageUrl($absolute = false) {
        return route($this->getCmfConfig()->getRouteName('cmf_logout'), [], $absolute);
    }

    /**
     * Enable/disable password restore link in login form
     * @return bool
     */
    public function isPasswordRestoreAllowed() {
        return $this->getCmfConfig()->config('auth.is_password_restore_allowed', true);
    }

    /**
     * @return string
     */
    public function getUserLoginColumnName() {
        return $this->getCmfConfig()->config('auth.user_login_column') ?: 'email';
    }

    /**
     * List of roles for CMF section's user
     * @return array
     */
    public function getUserRolesList() {
        return $this->getCmfConfig()->config('auth.roles', ['user']);
    }

    /**
     * @return mixed
     */
    public function getDefaultUserRole() {
        return $this->getCmfConfig()->config('auth.default_role', function () {
            $roles = $this->getUserRolesList();
            return count($roles) ? array_values($roles)[0] : 'user';
        });
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
                'cmfConfigClass' => get_class($this->getCmfConfig())
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
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getUserFromPasswordRecoveryAccessKey($accessKey) {
        /** @var ResetsPasswordsViaAccessKey $userClass */
        $userClass = $this->getUserRecordClass();
        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    /**
     * @param RecordInterface $user
     * @return string
     * @throws \BadMethodCallException
     */
    protected function getUserEmailColumnName() {
        if (!$this->emailColumnName) {
            $usersTableStructure = $this->getUsersTable()->getTableStructure();
            $colName = null;
            if ($usersTableStructure::hasColumn('email')) {
                $this->emailColumnName = 'email';
            } else {
                $userLoginColumn = $this->getUserLoginColumnName();
                if ($usersTableStructure::getColumn($userLoginColumn)->getType() === Column::TYPE_EMAIL) {
                    $this->emailColumnName = $userLoginColumn;
                } else {
                    throw new \BadMethodCallException('There is no known email column to use');
                }
            }
        }
        return $this->emailColumnName;
    }

    /**
     * @return CmfConfig
     */
    public function getCmfConfig() {
        return $this->cmfConfig;
    }

    /**
     * @return string
     */
    protected function getAuthGuardName() {
        return $this->getCmfConfig()->config('auth.guard.name', function () {
            $config = $this->getCmfConfig()->config('auth.guard');
            return is_string($config) ? $config : 'admin';
        });
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
        app()->singleton($this->authPolicyName, $this->getAccessPolicyClassName());
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

    protected function listenForUserAuthenticationEvents() {
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

    /**
     * @param Request $request
     * @param RecordInterface|Authenticatable $admin
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function validateAndGetUserProfileUpdates(Request $request, RecordInterface $admin) {
        $validationRules = [
            'old_password' => 'required',
            'new_password' => 'nullable|min:6',
        ];
        $columnsToUpdate = [];
        if ($admin::hasColumn('language')) {
            $validationRules['language'] = 'required|in:' . implode(',', $this->getCmfConfig()->locales());
            $columnsToUpdate[] = 'language';
        }
        if ($admin::hasColumn('name')) {
            $validationRules['name'] = 'nullable|max:200';
            $columnsToUpdate[] = 'name';
        }
        if ($admin::hasColumn('timezone')) {
            $validationRules['timezone'] = 'nullable|exists:pg_timezone_names,name';
            $columnsToUpdate[] = 'timezone';
        }
        $usersTable = $this->getUsersTable()->getName();
        $userLoginCol = $this->getUserLoginColumnName();
        if ($admin::hasColumn('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:$usersTable,email,{$admin->getAuthIdentifier()},id";
            } else {
                $validationRules['email'] = 'nullable|email';
            }
            $columnsToUpdate[] = 'email';
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|regex:%^[a-zA-Z0-9_@.-]+$%is|min:4|unique:$usersTable,$userLoginCol,{$admin->getAuthIdentifier()},id";
            $columnsToUpdate[] = $userLoginCol;
        }
        foreach ($this->getCustomUserProfileFieldsAndValidators() as $columnName => $rules) {
            if (is_int($columnName)) {
                $columnName = $rules;
            } else {
                $validationRules[$columnName] = $rules;
            }
            $columnsToUpdate[] = $columnName;
        }
        $validator = \Validator::make(
            $request->all(),
            $validationRules,
            Set::flatten(cmfTransCustom('.page.profile.errors'))
        );
        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
        } else if (method_exists($admin, 'checkPassword')) {
            if (!$admin->checkPassword($request->input('old_password'))) {
                $errors['old_password'] = cmfTransCustom('.page.profile.errors.old_password.match');
            }
        } else if (!\Hash::check($request->input('old_password'), $admin->getAuthPassword())) {
            $errors['old_password'] = cmfTransCustom('.page.profile.errors.old_password.match');
        }
        if (count($errors) > 0) {
            return $this->makeValidationErrorsJsonResponse($errors);
        }

        return $request->only($columnsToUpdate);
    }

    /**
     * Additional user profile fields and validators
     * Format: ['filed1' => 'validation rules', 'field2', ...]
     * @return array
     */
    protected function getCustomUserProfileFieldsAndValidators() {
        return [];
    }

}