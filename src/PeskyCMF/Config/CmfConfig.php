<?php

namespace PeskyCMF\Config;

use Illuminate\Http\Request;
use PeskyCMF\ConfigsContainer;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\PeskyCmfAccessManager;

class CmfConfig extends ConfigsContainer {

    public function __construct() {
        parent::__construct();
        // make it possible to return child class instance by calling CmfConfig::getInstance()
        if (get_class($this) !== __CLASS__) {
            self::replaceConfigInstance(__CLASS__, $this);
        }
    }

    static public function cmf_routes_cofig_files() {
        return [
            __DIR__ . '/cmf.routes.php'
        ];
    }

    static public function routes_config_files() {
        return [
//            __DIR__ . '/admin.routes.php'
        ];
    }

    /**
     * Base DB model class. Used to get instances of real models.
     * Note: you must overwrite this to avoid problems
     * @return string
     */
    static public function base_db_model_class() {
        return CmfDbModel::class;
    }

    /**
     * Auth configs for cmf
     * For examples - look for /config/auth.php
     * This configs will be recursively merged over configs from /config/auth.php
     * @return array
     */
    static public function auth_configs() {
        return [
            'guards' => [
                self::auth_guard_name() => [
                    'driver' => 'session',
                    'provider' => 'cmf',
                ],
            ],

            'providers' => [
                'cmf' => [
                    'driver' => 'peskyorm',
                    'table' => 'admins',
                    'model' => call_user_func(
                        [self::getInstance()->base_db_model_class(), 'getFullDbObjectClass'],
                        'admins'
                    )
                ],
            ],
        ];
    }

    /**
     * Set Auth guard to use
     * @return string
     */
    static public function auth_guard_name() {
        return 'cmf';
    }

    /**
     * Url prefix for routes
     * @return string
     */
    static public function url_prefix() {
        return 'admin';
    }

    /**
     * Prefix to load custom views from.
     * For example
     * - if custom views stored in /resources/views/admin - prefix should be "admin."
     * - if you placed views under namespace "admin" - prefix should be "admin:"
     * @return string
     */
    static public function custom_views_prefix() {
        return 'admin.';
    }

    /**
     * .css files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_css_includes() {
        return [
//            '/packages/admin/css/admin.custom.css'
        ];
    }

    /**
     * .js files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_js_includes() {
        return [
//            '/packages/admin/css/admin.custom.js'
        ];
    }

    /**
     * @return string
     */
    static public function views_path() {
        return __DIR__ . '/../resources/views';
    }

    /**
     * Controller class name for CMF scaffolds API
     * @return string
     */
    static public function cmf_scaffold_api_controller_class() {
        return \PeskyCMF\Http\Controllers\CmfScaffoldApiController::class;
    }

    /**
     * General controller class name for CMF (basic ui views, custom pages views, login/logout, etc.)
     * @return string
     */
    static public function cmf_general_controller_class() {
        return \PeskyCMF\Http\Controllers\CmfGeneralController::class;
    }

    /**
     * View for CMF layout
     * @return string
     */
    static public function layout_view() {
        return 'cmf::layout';
    }

    /**
     * View for CMF UI
     * @return string
     */
    static public function login_view() {
        return 'cmf::ui.login';
    }

    /**
     * View for CMF UI
     * @return string
     */
    static public function ui_view() {
        return 'cmf::ui.ui';
    }

    static public function footer_view() {
        return 'cmf::ui.footer';
    }

    /**
     * View that contains all templates for scaffold section
     * @return string
     */
    static public function scaffold_templates_view() {
        return 'cmf::scaffold.templates';
    }

    /**
     * View name for CMF menu
     * @return string
     */
    static public function menu_view() {
        return 'cmf::ui.menu';
    }

    /**
     * The menu structure of the site.
     * @return array
     * Format:
     *    array(
     *        array(
     *              'label' => 'label',
     *              'url' => '/url',
     *              'icon' => 'icon',
     *         ),
     *         array(
     *              'label' => 'label',
     *              'icon' => 'icon',
     *              'submenu' => array(...)
     *         ),
     *    )
     */
    static public function menu() {
        return [
            [
                'label' => 'admin_area.dashboard.menu_title',
                'url' => '/page/dashboard',
                'icon' => 'glyphicon glyphicon-dashboard',
            ],
            /*[
                'label' => 'admin_area.users.menu_title',
                'url' => '/resource/users',
                'icon' => 'fa fa-group'
            ],*/
            /*[
                'label' => 'admin_area.menu.section_utils',
                'icon' => 'glyphicon glyphicon-align-justify',
                'submenu' => [
                    [
                        'label' => 'admin_area.admins.menu_title',
                        'url' => '/resource/admins',
                        'icon' => 'glyphicon glyphicon-user'
                    ],
                ]
            ]*/
        ];
    }

    /**
     * Path to CMF translations
     * @return string
     */
    static public function cmf_translations_path() {
        return __DIR__ . '/../resources/lang';
    }

    /**
     * Default CMF language
     * @return string
     */
    static public function default_locale() {
        return 'en';
    }

    /**
     * Supported locales for CMF
     * @return array
     */
    static public function locales() {
        return [
            'en'
        ];
    }

    static public function locale_session_key() {
        return 'cmf_locale';
    }

    static public function session_redirect_key() {
        return 'cmf_redirect';
    }

    /**
     * The permission option is the highest-level authentication check that lets you define a closure that should return true if the current user
     * is allowed to view the admin section. Any "falsey" response will send the user back to the 'login_path' defined below.
     *
     * @param Request $request
     * @return callable
     */
    static public function isAuthorised(Request $request) {
        return PeskyCmfAccessManager::isAuthorised($request);
    }

    /**
     * List of roles for CMF
     * @return array
     */
    static public function roles_list() {
        return [
            'admin'
        ];
    }

    /**
     * Default admin role
     * Used in admins table config and admin creation
     * @return string
     */
    static public function default_role() {
        return 'admin';
    }

    /**
     * The menu item that should be used as the default landing page of the administrative section
     *
     * @return string
     */
    static public function home_page_url() {
        return '/' . self::url_prefix() . '/page/dashboard';
    }

    /**
     * The route to which the user will be taken when they click the "back to site" button
     *
     * @return string
     */
    static public function back_to_site_url() {
        return '/';
    }

    /**
     * The login route is the path where Administrator will send the user if they fail a permission check
     *
     * @return string
     */
    static public function login_route() {
        return 'cmf_login';
    }

    /**
     * The logout route is the path where Administrator will send the user when they click the logout link
     *
     * @return string
     */
    static public function logout_route() {
        return 'cmf_logout';
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    static public function rows_per_page() {
        return 25;
    }

    /**
     * Logo image for login and restore password pages
     * @return string
     */
    static public function login_logo() {
        return '<img src="/packages/cmf/img/peskycmf-logo-black.svg" width="340" alt=" " class="mb15">';
    }

    /**
     * Logo image to display in sidebar
     * @return string
     */
    static public function sidebar_logo() {
        return '<img src="/packages/cmf/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';
    }

}