<?php

declare(strict_types=1);

namespace PeskyCMF\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Message;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Arr;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\Admins\CmfAdminsTable;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\Event\CmfUserAuthenticated;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Listeners\CmfUserAuthenticatedEventListener;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\Set;

class CmfAuthModule
{
    
    use DataValidationHelper;
    use AuthorizesRequests;
    
    protected CmfConfig $cmfConfig;
    protected ?string $emailColumnName = null;
    
    protected string $originalUserFromLoginAsActionSessionKey = '__original_user';
    // page views
    protected string $userLoginPageViewPath = 'cmf::ui.login';
    protected string $forgotPasswordPageViewPath = 'cmf::ui.forgot_password';
    protected string $replacePasswordPageViewPath = 'cmf::ui.replace_password';
    protected string $userProfilePageViewPath = 'cmf::page.profile';
    protected string $registrationPageViewPath = 'cmf::ui.registration';
    // emails views
    protected string $passwordRevoceryEmailViewPath = 'cmf::emails.password_restore_instructions';
    // html elements
    protected string $defaultLoginPageLogo = '<img src="/packages/cmf/raw/img/peskycmf-logo-black.svg" width="340" alt=" " class="mb15">';
    
    protected ?Guard $authGuard = null;
    protected Application $app;
    protected SessionStore $sessionStore;
    protected AuthManager $authManager;
    protected Mailer $mailer;
    protected GateContract $authGate;
    protected Dispatcher $eventsDispatcher;
    
    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
        $this->app = $cmfConfig->getLaravelApp();
        $this->sessionStore = $this->app->make('session.store');
        $this->authManager = $this->app->make('auth');
        $this->mailer = $this->app->make('mailer');
        $this->authGate = $this->app->make(GateContract::class);
        $this->eventsDispatcher = $this->app->make('events');
        $this->validator = $this->app->make('validator');
    }
    
    protected function getLaravelApp(): Application
    {
        return $this->app;
    }
    
    protected function getSessionStore(): SessionStore
    {
        return $this->sessionStore;
    }
    
    protected function getAuthManager(): AuthManager
    {
        return $this->authManager;
    }
    
    protected function getMailer(): Mailer
    {
        return $this->mailer;
    }
    
    public function getAuthGate(): GateContract
    {
        return $this->authGate;
    }
    
    protected function getEventsDispatcher(): Dispatcher
    {
        return $this->eventsDispatcher;
    }
    
    protected function getValidator(): ValidationFactoryContract
    {
        return $this->validator;
    }
    
    public function init(): void
    {
        $this->configureAuthorizationGatesAndPolicies();
        $authGuardName = $this->getAuthGuardName();
        $this->getAuthManager()->shouldUse($authGuardName);
        $this->authGuard = $this->getAuthManager()->guard($authGuardName);
        $this->listenForUserAuthenticationEvents();
    }
    
    public function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }
    
    protected function getAuthGuardName(): string
    {
        return $this->getCmfConfig()->config('auth.guard.name', function () {
            $config = $this->getCmfConfig()->config('auth.guard');
            return is_string($config) ? $config : 'admin';
        });
    }
    
    /**
     * @return CmfAdmin|RecordInterface|Authenticatable
     */
    public function getUser(): RecordInterface
    {
        return $this->getAuthGuard()->user();
    }
    
    public function hasUser(): bool
    {
        return (bool)$this->getAuthGuard()->user();
    }
    
    /**
     * @return Guard|StatefulGuard|SessionGuard
     */
    public function getAuthGuard(): Guard
    {
        return $this->authGuard ?: $this->getAuthManager()->guard($this->getAuthGuardName());
    }
    
    /**
     * @return string|RecordInterface
     */
    public function getUserRecordClass(): ?string
    {
        return $this->getCmfConfig()->config('auth.user_record_class', function () {
            throw new \UnexpectedValueException('You need to provide a DB Record class for users');
        });
    }
    
    /**
     * @return TableInterface|CmfAdminsTable
     */
    public function getUsersTable(): TableInterface
    {
        $recordClass = $this->getUserRecordClass(); //< do not merge with next line!!!
        return $recordClass::getTable();
    }
    
    public function loginOnceUsingEmail(string $email): bool
    {
        return $this->getAuthGuard()->once([$this->getUserEmailColumnName() => $email]);
    }
    
    public function getLoginPageLogo(): string
    {
        return $this->getCmfConfig()->config('auth.login_logo') ?: $this->defaultLoginPageLogo;
    }
    
    public function renderUserLoginPageView(): string
    {
        if ($this->hasUser()) {
            return view('cmf::ui.redirect', ['url' => $this->getIntendedUrl()])->render();
        }
        return view($this->userLoginPageViewPath, ['authModule' => $this])->render();
    }
    
    /**
     * Enable/disable password restore link in login form
     */
    public function isRegistrationAllowed(): bool
    {
        return $this->getCmfConfig()->config('auth.is_registration_allowed', true);
    }
    
    public function renderUserRegistrationPageView(): string
    {
        if (!$this->isRegistrationAllowed()) {
            abort(new JsonResponse([], HttpCode::NOT_FOUND));
        }
        return view($this->registrationPageViewPath, [
            'authModule' => $this,
        ])->render();
    }
    
    public function processUserRegistrationRequest(Request $request): JsonResponse
    {
        if (!$this->isRegistrationAllowed()) {
            return new JsonResponse([], HttpCode::NOT_FOUND);
        }
        $data = $this->validateAndGetDataForRegistration($request);
        $user = $this->getUsersTable()->newRecord();
        $user->fromData($data, false);
        $this->addCustomRegistrationData($user);
        $user->save();
        $this->afterRegistration($user);
        $this->getAuthGuard()->login($user, true);
        return CmfJsonResponse::create()
            ->setForcedRedirect($this->getCmfConfig()->home_page_url());
    }
    
    protected function validateAndGetDataForRegistration(Request $request): array
    {
        $validationRules = [
            'password' => 'required|string|min:6|confirmed',
        ];
        $tableStricture = $this->getUsersTable()->getTableStructure();
        if ($tableStricture::hasColumn('name')) {
            $validationRules['name'] = 'nullable|max:200';
        }
        $usersTable = $tableStricture::getTableName();
        $userLoginCol = $this->getUserLoginColumnName();
        if ($tableStricture::hasColumn('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:{$usersTable},email";
            } else {
                $validationRules['email'] = 'nullable|email';
            }
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|regex:%^[a-zA-Z0-9_@.-]+$%is|min:4|unique:$usersTable,$userLoginCol";
        }
        $columnsToSave = array_keys($validationRules);
        if ($this->isRecaptchaAvailable()) {
            $validationRules['g-recaptcha-response'] = 'recaptcha';
        }
        foreach ($this->getCustomRegistrationFieldsAndValidators() as $columnName => $rules) {
            if (is_int($columnName)) {
                $columnName = $rules;
            } else {
                $validationRules[$columnName] = $rules;
            }
            $columnsToSave[] = $columnName;
        }
        $this->validate(
            $request,
            $validationRules,
            Set::flatten((array)$this->getCmfConfig()->transCustom('.registration_form.errors'))
        );
        return $request->only($columnsToSave);
    }
    
    public function getDataForUserProfileForm(): array
    {
        $admin = $this->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        $adminData = $admin->toArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = $admin->role;
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($admin::hasColumn('is_superadmin') && $admin->is_superadmin) {
                $role = 'superadmin';
            }
            $adminData['role'] = $this->getCmfConfig()->transCustom('.admins.role.' . $role);
        }
        return $adminData;
    }
    
    public function renderUserProfilePageView(): string
    {
        $user = $this->getUser();
        $this->authorize('resource.details', ['cmf_profile', $user]);
        return view($this->userProfilePageViewPath, [
            'authModule' => $this,
            'user' => $user,
            'canSubmit' => $this->getAuthGate()->allows('resource.update', ['cmf_profile', $user]),
        ])->render();
    }
    
    public function processUserProfileUpdateRequest(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $this->authorize('resource.update', ['cmf_profile', $user]);
        $updates = $this->validateAndGetUserProfileUpdates($request, $user);
        $oldEmail = $user::hasColumn('email') ? $user->email : null;
        $user
            ->begin()
            ->updateValues($updates, false, false);
        if (!empty(trim($request->input('new_password')))) {
            $user->setPassword($request->input('new_password'));
        }
        $user->commit();
        if (
            isset($updates['email'])
            && $user::hasColumn('email')
            && strtolower(trim($oldEmail)) !== strtolower(trim($updates['email']))
        ) {
            $this->onUserEmailAddressChange($user, $oldEmail);
        }
        return CmfJsonResponse::create()
            ->setData(['_reload_user' => true])
            ->setMessage($this->getCmfConfig()->transCustom('page.profile.saved'))
            ->reloadPage();
    }
    
    public function getAccessPolicyClassName(): string
    {
        return $this->getCmfConfig()->config('auth.acceess_policy_class') ?: CmfAccessPolicy::class;
    }
    
    public function processUserLoginRequest(Request $request): JsonResponse
    {
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
            return CmfJsonResponse::create(HttpCode::INVALID)
                ->setMessage($this->getCmfConfig()->transCustom('.login_form.login_failed'));
        } else {
            return CmfJsonResponse::create()->setRedirect($this->getIntendedUrl());
        }
    }
    
    /**
     * @return RedirectResponse|JsonResponse
     */
    public function processUserLogoutRequest(Request $request): Response
    {
        $loginPageUrl = $this->getLoginPageUrl(true);
        if ($this->getSessionStore()->has($this->originalUserFromLoginAsActionSessionKey)) {
            // logout to original account after 'login_as'
            $userInfo = $this->getSessionStore()->pull($this->originalUserFromLoginAsActionSessionKey);
            $user = $this->getAuthGuard()->getProvider()->retrieveByToken(
                Arr::get($userInfo, 'id', -1),
                Arr::get($userInfo, 'token', -1)
            );
            if ($user) {
                // Warning: do not use Auth->login($user) - it will fail to login previous user
                $this->getAuthGuard()->loginUsingId($user->getAuthIdentifier(), false);
                $redirectTo = Arr::get($userInfo, 'url') ?: $loginPageUrl;
                return $request->ajax()
                    ? CmfJsonResponse::create()->setForcedRedirect($redirectTo)
                    : new RedirectResponse($redirectTo);
            }
        }
        $this->logoutCurrentUser();
        return $request->ajax()
            ? CmfJsonResponse::create()->setForcedRedirect($loginPageUrl)
            : new RedirectResponse($loginPageUrl);
    }
    
    /**
     * Logout current user, invalidate session and reset locale
     */
    public function logoutCurrentUser(): void
    {
        $this->getAuthGuard()->logout();
        $this->getSessionStore()->flush();
        $this->getCmfConfig()->detectLocale();
    }
    
    /**
     * @param int|string $otherUserId
     */
    public function processLoginAsOtherUserRequest($otherUserId): JsonResponse
    {
        $this->authorize('cmf_page', ['login_as']);
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getAuthIdentifier();
        if ($currentUserId === $otherUserId || $currentUserId === (int)$otherUserId) {
            return CmfJsonResponse::create(HttpCode::CANNOT_PROCESS)
                ->setMessage($this->getCmfConfig()->transCustom('admins.login_as.same_user'));
        }
        $token = $currentUser->getRememberToken();
        if (!$token) {
            return CmfJsonResponse::create(HttpCode::CANNOT_PROCESS)
                ->setMessage($this->getCmfConfig()->transCustom('admins.login_as.no_auth_token'));
        }
        /** @var CmfAdmin|RecordInterface $otherUser */
        $otherUser = $this->getAuthGuard()->loginUsingId($otherUserId);
        if (!is_object($otherUser)) {
            // Warning: do not use Auth->login($currentUser) - it might fail
            $this->getAuthGuard()->loginUsingId($currentUserId, false);
            return CmfJsonResponse::create(HttpCode::CANNOT_PROCESS)
                ->setMessage($this->getCmfConfig()->transCustom('admins.login_as.fail', ['id' => $otherUserId]));
        }
        $this->getSessionStore()->put([
            $this->originalUserFromLoginAsActionSessionKey => [
                'id' => $currentUserId,
                'token' => $token,
                'url' => $this->getLaravelApp()->make('url')->previous($this->getCmfConfig()->home_page_url(true)),
            ],
            $this->getCmfConfig()->session_message_key() => $this->getCmfConfig()->transCustom(
                'admins.login_as.success',
                ['user' => $otherUser->getValue($this->getUserLoginColumnName())]
            ),
        ]);
        return CmfJsonResponse::create()
            ->setRedirect($this->getCmfConfig()->home_page_url());
    }
    
    public function startPasswordRecoveryProcess(Request $request): JsonResponse
    {
        if (!$this->isPasswordRestoreAllowed()) {
            return new JsonResponse([], HttpCode::NOT_FOUND);
        }
        $validators = [
            'email' => 'required|email',
        ];
        if ($this->isRecaptchaAvailable()) {
            $validators['g-recaptcha-response'] = 'recaptcha';
        }
        $data = $this->validate($request, $validators);
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
        
        return CmfJsonResponse::create()
            ->setMessage($this->getCmfConfig()->transCustom('.forgot_password.instructions_sent'))
            ->setRedirect($this->getLoginPageUrl());
    }
    
    public function finishPasswordRecoveryProcess(Request $request, string $accessKey): JsonResponse
    {
        if (!$this->isPasswordRestoreAllowed()) {
            return new JsonResponse([], HttpCode::NOT_FOUND);
        }
        $data = $this->validate($request, [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password',
        ]);
        $this->logoutCurrentUser(); //< to prevent usage of remembered user's session
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if ($user && $user->getPrimaryKeyValue() !== $data['id']) {
            /** @var CmfDbRecord $user */
            $user
                ->begin()
                ->updateValue('password', $data['password'], false)
                ->commit();
            return CmfJsonResponse::create()
                ->setMessage($this->getCmfConfig()->transCustom('.replace_password.password_replaced'))
                ->setForcedRedirect($this->getLoginPageUrl());
        } else {
            return CmfJsonResponse::create(HttpCode::FORBIDDEN)
                ->setMessage($this->getCmfConfig()->transCustom('.replace_password.invalid_access_key'))
                ->setForcedRedirect($this->getLoginPageUrl());
        }
    }
    
    public function renderForgotPasswordPageView(): string
    {
        return view($this->forgotPasswordPageViewPath, ['authModule' => $this])->render();
    }
    
    public function renderReplaceUserPasswordPageView(string $accessKey): string
    {
        if (!$this->isPasswordRestoreAllowed()) {
            abort(new JsonResponse([], HttpCode::NOT_FOUND));
        }
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if ($user) {
            return view($this->replacePasswordPageViewPath, [
                'authModule' => $this,
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue(),
                'userLogin' => $user->getValue($this->getUserLoginColumnName()),
            ])->render();
        } else {
            abort(
                CmfJsonResponse::create(HttpCode::FORBIDDEN)
                    ->setMessage($this->getCmfConfig()->transCustom('.replace_password.invalid_access_key'))
                    ->setRedirect($this->getLoginPageUrl())
            );
        }
    }
    
    public function getLoginPageUrl(bool $absolute = false): string
    {
        return route($this->getCmfConfig()->getRouteName('cmf_login'), [], $absolute);
    }
    
    public function getLogoutPageUrl(bool $absolute = false): string
    {
        return route($this->getCmfConfig()->getRouteName('cmf_logout'), [], $absolute);
    }
    
    /**
     * Enable/disable password restore link in login form
     */
    public function isPasswordRestoreAllowed(): bool
    {
        return $this->getCmfConfig()->config('auth.is_password_restore_allowed', true);
    }
    
    public function getUserLoginColumnName(): string
    {
        return $this->getCmfConfig()->config('auth.user_login_column') ?: 'email';
    }
    
    /**
     * List of roles for CMF section's user
     */
    public function getUserRolesList(): array
    {
        return $this->getCmfConfig()->config('auth.roles', ['user']);
    }
    
    public function getDefaultUserRole(): string
    {
        return $this->getCmfConfig()->config('auth.default_role', function () {
            $roles = $this->getUserRolesList();
            return count($roles) ? array_values($roles)[0] : 'user';
        });
    }
    
    /**
     * @param ResetsPasswordsViaAccessKey|RecordInterface $user
     */
    protected function sendPasswordRecoveryInstructionsEmail(RecordInterface $user): void
    {
        $subject = $this->getCmfConfig()->transCustom('.forgot_password.email_subject');
        $from = $this->getCmfConfig()->system_email_address();
        $to = $user->getValue($this->getUserEmailColumnName());
        $this->getMailer()->send(
            $this->getPasswordRecoveryEmailViewPath(),
            [
                'url' => cmfRoute('cmf_replace_password', [$user->getPasswordRecoveryAccessKey()], true, $this->getCmfConfig()),
                'user' => $user->toArrayWithoutFiles(),
                'cmfConfig' => $this->getCmfConfig(),
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
     * @return RecordInterface|ResetsPasswordsViaAccessKey
     */
    protected function getUserFromPasswordRecoveryAccessKey(string $accessKey): ?RecordInterface
    {
        /** @var ResetsPasswordsViaAccessKey $userClass */
        $userClass = $this->getUserRecordClass();
        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }
    
    /**
     * @throws \BadMethodCallException
     */
    protected function getUserEmailColumnName(): string
    {
        if (!$this->emailColumnName) {
            $usersTableStructure = $this->getUsersTable()->getTableStructure();
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
     */
    public function configureAuthorizationGatesAndPolicies(): void
    {
        $this->getLaravelApp()->singleton(CmfAccessPolicy::class, $this->getAccessPolicyClassName());
        $this->getAuthGate()->resource('resource', CmfAccessPolicy::class, [
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
        $this->getAuthGate()->define('cmf_page', CmfAccessPolicy::class . '@cmf_page');
    }
    
    public function listenForUserAuthenticationEvents(): void
    {
        $this->getEventsDispatcher()->listen(
            CmfUserAuthenticated::class,
            CmfUserAuthenticatedEventListener::class
        );
    }
    
    protected function getPasswordRecoveryEmailViewPath(): string
    {
        return $this->passwordRevoceryEmailViewPath;
    }
    
    public function saveIntendedUrl(string $url): void
    {
        $this->getSessionStore()
            ->put($this->getCmfConfig()->makeUtilityKey('intended_url'), $url);
    }
    
    public function getIntendedUrl(): string
    {
        $intendedUrl = $this->getSessionStore()->pull($this->getCmfConfig()->makeUtilityKey('intended_url'));
        if (empty($intendedUrl)) {
            return $this->getCmfConfig()->home_page_url();
        } elseif (preg_match('%/api/([^/]+?)/list/?$%i', $intendedUrl, $matches)) {
            return routeToCmfItemsTable($matches[1], [], false, $this->getCmfConfig());
        } elseif (preg_match('%/api/([^/]+?)/service/%i', $intendedUrl, $matches)) {
            return routeToCmfItemsTable($matches[1], [], false, $this->getCmfConfig());
        } elseif (preg_match('%/api/([^/]+?)/([^/?]+?)/?(?:\?details=(\d)|$)%i', $intendedUrl, $matches)) {
            if (!empty($matches[3]) && $matches[3] === '1') {
                return routeToCmfItemDetails($matches[1], $matches[2], false, $this->getCmfConfig());
            } else {
                return routeToCmfItemEditForm($matches[1], $matches[2], false, $this->getCmfConfig());
            }
        } elseif (preg_match('%/api/([^/]+?)%i', $intendedUrl, $matches)) {
            return routeToCmfItemsTable($matches[1], [], false, $this->getCmfConfig());
        } elseif (preg_match('%/page/([^/]+)\.html$%i', $intendedUrl, $matches)) {
            return routeToCmfPage($matches[1], [], false, $this->getCmfConfig());
        } else {
            return $intendedUrl;
        }
    }
    
    /**
     * @param Request $request
     * @param RecordInterface|Authenticatable $admin
     * @return array
     */
    protected function validateAndGetUserProfileUpdates(Request $request, RecordInterface $admin): array
    {
        $requirePassword = $this->getCmfConfig()->config('auth.profile_update_requires_current_password', true);
        $validationRules = [
            'old_password' => $requirePassword ? 'required|string' : 'nullable|required_with:new_password|string',
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
            $validationRules[$userLoginCol] = [
                'required',
                'regex:%^[a-zA-Z0-9_@.-]+$%is',
                'min:4',
                'unique:$usersTable,$userLoginCol,{$admin->getAuthIdentifier()},id',
            ];
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
        $this->validate(
            $request,
            $validationRules,
            Set::flatten((array)$this->getCmfConfig()->transCustom('.page.profile.errors'))
        );
        $errors = [];
        /** @noinspection MissingOrEmptyGroupStatementInspection */
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if (!$requirePassword && empty($request->input('new_password'))) {
            // do nothing
        } elseif (method_exists($admin, 'checkPassword')) {
            if (!$admin->checkPassword($request->input('old_password'))) {
                $errors['old_password'] = $this->getCmfConfig()->transCustom('.page.profile.errors.old_password.match');
            }
        } elseif (!$this->getLaravelApp()->make('hash')->check($request->input('old_password'), $admin->getAuthPassword())) {
            $errors['old_password'] = $this->getCmfConfig()->transCustom('.page.profile.errors.old_password.match');
        }
        if (count($errors) > 0) {
            $this->throwValidationErrorsResponse($errors);
        }
        
        return $request->only($columnsToUpdate);
    }
    
    public function isRecaptchaAvailable(): bool
    {
        return !empty($this->getCmfConfig()->recaptcha_public_key());
    }
    
    /**
     * Additional user profile fields and validators
     * Format: ['filed1' => 'validation rules', 'field2', ...]
     */
    protected function getCustomUserProfileFieldsAndValidators(): array
    {
        return [];
    }
    
    /**
     * Additional user profile fields and validators
     * Format: ['filed1' => 'validation rules', 'field2', ...]
     */
    protected function getCustomRegistrationFieldsAndValidators(): array
    {
        return [];
    }
    
    /**
     * Additional non-editable data to save into user record.
     * For example: role, language
     * @param RecordInterface|Authenticatable $user - user record with submitted data already set
     */
    protected function addCustomRegistrationData(RecordInterface $user): void
    {
    }
    
    /**
     * Additional actions after user's account created.
     * For example: send confirmation email
     * @param RecordInterface|Authenticatable $user
     */
    protected function afterRegistration(RecordInterface $user): void
    {
    }
    
    /**
     * Called when user have changed his emails address.
     * Here you can modify email confirmation status and send confirmation email
     * @param RecordInterface|Authenticatable $user
     * @param string|null $oldEmail
     */
    protected function onUserEmailAddressChange(RecordInterface $user, ?string $oldEmail): void
    {
    }
    
}
