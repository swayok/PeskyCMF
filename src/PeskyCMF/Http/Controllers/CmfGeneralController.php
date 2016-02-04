<?php

namespace PeskyCMF\Http\Controllers;

use App\Db\Admin\Admin;
use Auth;
use Illuminate\Mail\Message;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\DbExpr;
use Redirect;
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

    public function getUiView($viewName) {
        $configName = $viewName . '_view';
        $configs = CmfConfig::getInstance();
        if (!method_exists($configs, $configName)) {
            abort(HttpCode::NOT_FOUND, "Config [$configName] not defined");
        }
        return view(CmfConfig::getInstance()->$configName)->render();
    }

    public function getAdminProfile() {
        return view('cmf::page.profile', ['admin' => $this->getAdmin()]);
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
                return response()->json([
                    '_message' => CmfConfig::transCustom('.page.profile.saved'),
                    'redirect' => 'reload'
                ]);
            } else {
                return response()->json(
                    [
                        '_message' => CmfConfig::transBase('.form.failed_to_save_resource_data'),
                        'redirect' => 'reload'
                    ],
                    HttpCode::SERVER_ERROR
                );
            }
        }
    }

    /**
     * @param Request $request
     * @param Admin $admin
     * @return array|\Illuminate\Http\JsonResponse
     */
    protected function validateAndGetAdminProfileUpdates(Request $request, Admin $admin) {
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
        $usersTable = CmfConfig::getInstance()->users_table_name();
        $userLoginCol = CmfConfig::getInstance()->user_login_column();
        if ($admin->_hasField('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:$usersTable,email,{$admin->id},id";
            } else {
                $validationRules['email'] = "email";
            }
            $fieldsToUpdate[] = 'email';
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|alpha_dash|min:4|unique:$usersTable,$userLoginCol,{$admin->id},id";
            $fieldsToUpdate[] = $userLoginCol;
        }
        $validator = \Validator::make(
            $request->data(),
            $validationRules,
            Set::flatten(CmfConfig::transCustom('.page.profile.errors'))
        );
        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
        } else if (!\Hash::check($request->data('old_password'), $admin->password)) {
            $errors['old_password'] = CmfConfig::transCustom('.page.profile.errors.old_password.match');
        }
        if (!empty($errors)) {
            return response()->json(
                [
                    'errors' => $errors,
                    'message' => CmfConfig::transBase('.form.validation_errors')
                ],
                HttpCode::INVALID
            );
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

    public function switchLocale($locale = null) {
        if (is_string($locale) && strlen($locale) && in_array($locale, CmfConfig::getInstance()->locales())) {
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
            return Redirect::to(route(CmfConfig::getInstance()->login_route()))
                ->with(CmfConfig::getInstance()->session_message_key(), [
                    'message' => CmfConfig::transCustom('.replace_password.invalid_access_key'),
                    'type' => 'error'
                ]);
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
            return response()->json([
                '_message' => CmfConfig::transCustom('.replace_password.invalid_access_key'),
                'redirect' => route(CmfConfig::getInstance()->login_route())
            ], HttpCode::FORBIDDEN);
        }
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(CmfConfig::getInstance()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return CmfConfig::getInstance()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%is', $intendedUrl, $matches)) {
                return route('cmf_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/service/%is', $intendedUrl, $matches)) {
                return route('cmf_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/([^/]+?)/?(?:details=(\d)|$)%is', $intendedUrl, $matches)) {
                if ($matches[3] === '1') {
                    return route('cmf_item_details', [$matches[1], $matches[2]]);
                } else {
                    return route('cmf_item_edit_form', [$matches[1], $matches[2]]);
                }
            } else if (preg_match('%/api/([^/]+?)%is', $intendedUrl, $matches)) {
                return route('cmf_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/page/([^/]+)\.html$%is', $intendedUrl, $matches)) {
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
            return response()->json(['_message' => CmfConfig::transCustom('.login_form.login_failed')], HttpCode::INVALID);
        } else {
            return response()->json(['redirect' => $this->getIntendedUrl()]);
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
        return response()->json([
            '_message' => CmfConfig::transCustom('.forgot_password.instructions_sent'),
            'redirect' => route(CmfConfig::getInstance()->login_route())
        ]);
    }

    public function replacePassword(Request $request, $accessKey) {
        $this->validate($request->data(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password'
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->_getPkValue() !== $request->data('id')) {
            $user->begin()->_setFieldValue('password', $request->data('password'));
            if ($user->commit()) {
                return response()->json([
                    '_message' => CmfConfig::transCustom('.replace_password.password_replaced'),
                    'redirect' => route(CmfConfig::getInstance()->login_route())
                ]);
            } else {
                return response()->json([
                    '_message' => CmfConfig::transCustom('.replace_password.failed_to_save'),
                ], HttpCode::SERVER_ERROR);
            }
        } else {
            return response()->json([
                '_message' => CmfConfig::transCustom('.replace_password.invalid_access_key'),
                'redirect' => route(CmfConfig::getInstance()->login_route())
            ], HttpCode::FORBIDDEN);
        }
    }

    public function logout() {
        Auth::guard()->logout();
        \Session::clear();
        return Redirect::route(CmfConfig::getInstance()->login_route());
    }

    public function getAdminInfo() {
        $admin = $this->getAdmin()->toPublicArray();
        if (!empty($admin['role'])) {
            $admin['_role'] = $admin['role'];
            $admin['role'] = CmfConfig::transCustom('.admins.role.' . $admin['role']);
        }
        return response()->json($admin);
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

}
