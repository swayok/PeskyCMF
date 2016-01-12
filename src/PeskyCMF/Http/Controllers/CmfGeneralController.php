<?php

namespace PeskyCMF\Http\Controllers;

use App\Db\Admin\Admin;
use Auth;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Request;
use PeskyCMF\HttpCode;
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
        $validationRules = [
            'old_password' => 'required',
            'new_password' => 'min:6',
            'email' => 'required|email|unique:admins,email,' . $admin->id . ',id',
            'language' => 'required|in:' . implode(CmfConfig::getInstance()->locales()),
            'name' => 'max:200'
        ];
        $validator = \Validator::make(
            $request->data(),
            $validationRules,
            Set::flatten(trans('cmf::cmf.page.profile.errors'))
        );
        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
        } else if (!\Hash::check($request->data('old_password'), $admin->password)) {
            $errors['old_password'] = trans('cmf::cmf.page.profile.errors.old_password.match');
        }
        if (!empty($errors)) {
            return response()->json(
                [
                    'errors' => $errors,
                    'message' => trans('cmf::cmf.form.validation_errors')
                ],
                HttpCode::INVALID
            );
        } else {
            $admin
                ->begin()
                ->updateValues($request->only(['email', 'name', 'language']));
            if (!empty(trim($request->data('new_password')))) {
                $admin->setPassword($request->data('new_password'));
            }
            if ($admin->commit()) {
                return response()->json([
                    '_message' => trans('cmf::cmf.page.profile.saved'),
                    'redirect' => 'reload'
                ]);
            } else {
                return response()->json(
                    [
                        '_message' => trans('cmf::cmf.form.failed_to_save_resource_data'),
                        'redirect' => 'reload'
                    ],
                    HttpCode::SERVER_ERROR
                );
            }
        }
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

    public function getLoginTpl() {
        return view(CmfConfig::getInstance()->login_view())->render();
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(CmfConfig::getInstance()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return CmfConfig::getInstance()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%is', $intendedUrl, $matches)) {
                return route('admin_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/service/%is', $intendedUrl, $matches)) {
                return route('admin_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/api/([^/]+?)/([^/]+?)/?(?:details=(\d)|$)%is', $intendedUrl, $matches)) {
                if ($matches[3] === '1') {
                    return route('cmf_item_details', [$matches[1], $matches[2]]);
                } else {
                    return route('cmf_item_edit_form', [$matches[1], $matches[2]]);
                }
            } else if (preg_match('%/api/([^/]+?)%is', $intendedUrl, $matches)) {
                return route('admin_resource_show_list', [$matches[1]]);
            } else if (preg_match('%/page/([^/]+)\.html$%is', $intendedUrl, $matches)) {
                return route('cmf_page', [$matches[1]]);
            } else {
               return $intendedUrl;
            }
        }
    }

    public function doLogin(Request $request) {
        if (!Auth::guard()->attempt(['email' => mb_strtolower(trim($request->data('email'))), 'password' => $request->data('password')])) {
            return response()->json(['_message' => trans('cmf::cmf.login_form.login_failed')], HttpCode::INVALID);
        } else {
            return response()->json(['redirect' => $this->getIntendedUrl()]);
        }
    }

    public function logout() {
        Auth::guard()->logout();
        \Session::clear();
        return Redirect::route(CmfConfig::getInstance()->login_route());
    }

    public function getAdminInfo() {
        return response()->json($this->getAdmin()->toPublicArray());
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
