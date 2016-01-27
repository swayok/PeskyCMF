<?php

namespace PeskyCMF\Http\Controllers;

use App\Db\Admin\Admin;
use Auth;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
use PeskyORM\DbExpr;
use Redirect;
use Swayok\Utils\Set;

class CmfGeneralController extends Controller {

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
            abort(404, "Config [$configName] not defined");
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
        // todo: validate access key
        return $this->loadJsApp($request, ['accessKey' => $accessKey, 'userId' => '0']);
    }

    public function getLoginTpl() {
        return view(CmfConfig::getInstance()->login_view())->render();
    }

    public function getForgotPasswordTpl() {
        return view(CmfConfig::getInstance()->forgot_password_view())->render();
    }

    public function getReplacePasswordTpl() {
        return view(CmfConfig::getInstance()->replace_password_view())->render();
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
        return response()->json([
            '_message' => CmfConfig::transCustom('.forgot_password_form.forgot_password_form'),
            'redirect' => route(CmfConfig::getInstance()->login_route())
        ]);
    }

    public function replacePassword(Request $request, $accessKey) {
        // todo: validate access key
        return response()->json([
            '_message' => CmfConfig::transCustom('.replace_password_form.password_replaced'),
            'redirect' => route(CmfConfig::getInstance()->login_route())
        ]);
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
