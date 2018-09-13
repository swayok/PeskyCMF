<?php

namespace PeskyCMF\Http\Controllers;

use App\Db\Admin\Admin;
use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Message;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\DbExpr;
use PeskyORM\DbModel;
use PeskyORM\DbObject;
use Redirect;
use Swayok\Utils\File;
use Swayok\Utils\Set;

class CmfGeneralController extends Controller {

    use DataValidationHelper;

    public function __construct() {

    }

    public function loadJsApp(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        }
        return view(CmfConfig::getInstance()->layout_view());
    }

    public function getPage(Request $request, $name) {
        if ($request->ajax()) {
            return view(CmfConfig::getInstance()->custom_views_prefix() . 'page/' . $name)->render();
        } else {
            return view(CmfConfig::getInstance()->layout_view())->render();
        }
    }

    public function getAboutCmfPage() {
        return view('cmf::page.about');
    }

    public function getUiView($viewName) {
        $configName = $viewName . '_view';
        $configs = CmfConfig::getInstance();
        if (!method_exists($configs, $configName)) {
            abort(HttpCode::NOT_FOUND, "Config [$configName] not defined");
        }
        return view(CmfConfig::getInstance()->$configName)->render();
    }

    public function getAdminProfile() {
        return view(CmfConfig::getInstance()->user_profile_view(), ['admin' => $this->getAdmin()]);
    }

    public function updateAdminProfile(Request $request) {
        $admin = $this->getAdmin();
        $updates = $this->validateAndGetAdminProfileUpdates($request, $admin);
        if (!is_array($updates)) {
            return $updates;
        } else {
            $admin
                ->begin()
                ->updateValues($updates);
            if (!empty(trim($request->data('new_password')))) {
                $admin->setPassword($request->data('new_password'));
            }
            if ($admin->commit()) {
                return cmfServiceJsonResponse()
                    ->setMessage(CmfConfig::transCustom('.page.profile.saved'))
                    ->reloadPage();
            } else {
                return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                    ->setMessage(CmfConfig::transBase('.form.failed_to_save_resource_data'))
                    ->reloadPage();
            }
        }
    }

    /**
     * @param Request $request
     * @param DbObject|Authenticatable $admin
     * @return array|\Illuminate\Http\JsonResponse
     */
    protected function validateAndGetAdminProfileUpdates(Request $request, DbObject $admin) {
        $validationRules = [
            'old_password' => 'required',
            'new_password' => 'min:6',
        ];
        $fieldsToUpdate = [];
        if ($admin->_hasField('language')) {
            $validationRules['language'] = 'required|in:' . implode(CmfConfig::getInstance()->locales());
            $fieldsToUpdate[] = 'language';
        }
        if ($admin->_hasField('name')) {
            $validationRules['name'] = 'max:200';
            $fieldsToUpdate[] = 'name';
        }
        if ($admin->_hasField('timezone')) {
            $validationRules['timezone'] = 'required|exists2:pg_timezone_names,name';
            $fieldsToUpdate[] = 'timezone';
        }
        $usersTable = CmfConfig::getInstance()->users_table_name();
        $userLoginCol = CmfConfig::getInstance()->user_login_column();
        if ($admin->_hasField('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:$usersTable,email,{$admin->getAuthIdentifier()},id";
            } else {
                $validationRules['email'] = 'email';
            }
            $fieldsToUpdate[] = 'email';
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|regex:%^[a-zA-Z0-9_@.-]+$%is|min:4|unique:$usersTable,$userLoginCol,{$admin->getAuthIdentifier()},id";
            $fieldsToUpdate[] = $userLoginCol;
        }
        foreach (CmfConfig::getInstance()->additional_user_profile_fields() as $fieldName => $rules) {
             if (is_int($fieldName)) {
                $fieldName = $rules;
             } else {
                $validationRules[$fieldName] = $rules;
             }
             $fieldsToUpdate[] = $fieldName;
        }
        $validator = \Validator::make(
            $request->data(),
            $validationRules,
            Set::flatten(CmfConfig::transCustom('.page.profile.errors'))
        );
        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
        } else if (!\Hash::check($request->data('old_password'), $admin->getAuthPassword())) {
            $errors['old_password'] = CmfConfig::transCustom('.page.profile.errors.old_password.match');
        }
        if (!empty($errors)) {
            return cmfJsonResponseForValidationErrors($errors);
        }
        return $request->only($fieldsToUpdate);
    }

    protected function getDataForBasicUiView() {
        return [
            'urlPrefix' => '/' . CmfConfig::getInstance()->url_prefix(),
        ];
    }

    public function getBasicUiView() {
        $viewData = $this->getDataForBasicUiView();
        return view(CmfConfig::getInstance()->ui_view(), $viewData)->render();
    }

    /**
     * @param null|string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLocale($locale = null) {
        if (is_string($locale) && $locale !== '' && in_array($locale, CmfConfig::getInstance()->locales(), true)) {
            \Session::set(CmfConfig::getInstance()->locale_session_key(), strtolower($locale));
        }
        return \Redirect::back();
    }

    static public function getLocale() {
        return \Session::get(
            CmfConfig::getInstance()->locale_session_key(),
            CmfConfig::getInstance()->default_locale()
        );
    }

    public function getLogin(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        } else if (!Auth::guard()->check()) {
            return view(CmfConfig::getInstance()->layout_view())->render();
        } else {
            return Redirect::to($this->getIntendedUrl());
        }
    }

    public function getReplacePassword(Request $request, $accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (empty($user)) {
            return cmfRedirectResponseWithMessage(
                route(CmfConfig::getInstance()->login_route()),
                CmfConfig::transCustom('.replace_password.invalid_access_key'),
                'error'
            );
        }
        return $this->loadJsApp($request);
    }

    /**
     * @param $accessKey
     * @return bool|CmfDbObject
     */
    protected function getUserFromPasswordRecoveryAccessKey($accessKey) {
        /** @var ResetsPasswordsViaAccessKey $userClass */
        $userClass = CmfConfig::getInstance()->user_object_class();
        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    public function getLoginTpl() {
        return view(CmfConfig::getInstance()->login_view())->render();
    }

    public function getForgotPasswordTpl() {
        return view(CmfConfig::getInstance()->forgot_password_view())->render();
    }

    public function getReplacePasswordTpl($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view(CmfConfig::getInstance()->replace_password_view(), [
                'accessKey' => $accessKey,
                'userId' => $user->_getPkValue()
            ])->render();
        } else {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transCustom('.replace_password.invalid_access_key'))
                ->setRedirect(route(CmfConfig::getInstance()->login_route()));
        }
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(CmfConfig::getInstance()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return CmfConfig::getInstance()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%i', $intendedUrl, $matches)) {
                return route('cmf_items_table', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/service/%i', $intendedUrl, $matches)) {
                return route('cmf_items_table', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/([^/]+?)/?(?:details=(\d)|$)%i', $intendedUrl, $matches)) {
                if (isset($matches[3]) && $matches[3] === '1') {
                    return route('cmf_item_details', [$matches[1], $matches[2]]);
                } else {
                    return route('cmf_item_edit_form', [$matches[1], $matches[2]]);
                }
            } else if (preg_match('%/api/([^/]+?)%i', $intendedUrl, $matches)) {
                return route('cmf_items_table', [$matches[1]]);
            } else if (preg_match('%/page/([^/]+)\.html$%i', $intendedUrl, $matches)) {
                return route('cmf_page', [$matches[1]]);
            } else {
               return $intendedUrl;
            }
        }
    }

    public function doLogin(Request $request) {
        $userLoginColumn = CmfConfig::getInstance()->user_login_column();
        $this->validate($request->data(), [
            $userLoginColumn => 'required' . ($userLoginColumn === 'email' ? '|email' : ''),
            'password' => 'required'
        ]);
        $credentials = [
            DbExpr::create("LOWER(`{$userLoginColumn}`) = LOWER(``" . trim($request->data($userLoginColumn)) . '``)'),
            'password' => $request->data('password')
        ];
        if (!Auth::guard()->attempt($credentials)) {
            return cmfServiceJsonResponse(HttpCode::INVALID)
                ->setMessage(CmfConfig::transCustom('.login_form.login_failed'));
        } else {
            return cmfServiceJsonResponse()->setRedirect($this->getIntendedUrl());
        }
    }

    public function sendPasswordReplacingInstructions(Request $request) {
        $this->validate($request->data(), [
            'email' => 'required|email',
        ]);
        $email = strtolower(trim($request->data('email')));
        if (Auth::guard()->attempt(['email' => $email], false, false)) {
            /** @var CmfDbObject|ResetsPasswordsViaAccessKey $user */
            $user = Auth::guard()->getLastAttempted();
            $data = [
                'url' => route('cmf_replace_password', [$user->getPasswordRecoveryAccessKey()]),
                'user' => $user->toPublicArrayWithoutFiles()
            ];
            $subject = CmfConfig::transCustom('.forgot_password.email_subject');
            $from = CmfConfig::getInstance()->system_email_address();
            $view = CmfConfig::getInstance()->password_recovery_email_view();
            \Mail::send($view, $data, function (Message $message) use ($from, $email, $subject) {
                $message
                    ->from($from)
                    ->to($email)
                    ->subject($subject);
            });
        }
        return cmfServiceJsonResponse()
            ->setMessage(CmfConfig::transCustom('.forgot_password.instructions_sent'))
            ->setRedirect(route(CmfConfig::getInstance()->login_route()));
    }

    public function replacePassword(Request $request, $accessKey) {
        $this->validate($request->data(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password'
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->_getPkValue() !== $request->data('id')) {
            /** @var CmfDbObject $user */
            $user->begin()->_setFieldValue('password', $request->data('password'));
            if ($user->commit()) {
                return cmfServiceJsonResponse()
                    ->setMessage(CmfConfig::transCustom('.replace_password.password_replaced'))
                    ->setRedirect(route(CmfConfig::getInstance()->login_route()));
            } else {
                return cmfServiceJsonResponse(HttpCode::SERVER_ERROR)
                    ->setMessage(CmfConfig::transCustom('.replace_password.failed_to_save'));
            }
        } else {
            return cmfServiceJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(CmfConfig::transCustom('.replace_password.invalid_access_key'))
                ->setRedirect(route(CmfConfig::getInstance()->login_route()));
        }
    }

    public function logout() {
        Auth::guard()->logout();
        \Session::flush();
        \Session::regenerate(true);
        return Redirect::route(CmfConfig::getInstance()->login_route());
    }

    public function getAdminInfo() {
        $admin = $this->getAdmin();
        $adminData = $admin->toPublicArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = ($admin->is_superadmin ? 'superadmin' : $admin->role);
            $adminData['role'] = CmfConfig::transCustom('.admins.role.' . $role);
        }
        return response()->json($adminData);
    }

    /**
     * @return Admin
     */
    protected function getAdmin() {
        return Auth::guard()->user();
    }

    public function cleanCache() {
        \Cache::flush();
    }

    public function handlerForRouteNotFound() {
        return view('cmf::ui.default_page_header', [
            'header' => 'Handler for route [' . request()->getPathInfo() . '] is not defined',
        ]);
    }

    public function serveCmfPublicFiles($filePath) {
        $filePath = __DIR__ . '/public/' . $filePath;
        if (File::exist($filePath)) {
            return response(File::contents(), 200, ['Content-Type' => File::load()->mime()]);
        } else {
            return response('File not found');
        }
    }

}
