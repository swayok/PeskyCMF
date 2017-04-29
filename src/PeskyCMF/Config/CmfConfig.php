<?php

namespace PeskyCMF\Config;

use Illuminate\Http\Request;
use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\Admins\CmsAdminsTableStructure;
use PeskyCMF\Db\CmfDbTable;
use PeskyCMF\Http\Middleware\ValidateAdmin;
use PeskyCMF\PeskyCmfAccessManager;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\ClassBuilder;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\StringUtils;

abstract class CmfConfig extends ConfigsContainer {

    static private $instances = [];

    protected function __construct() {
        self::$instances[get_class($this)] = $this;
        if (!array_key_exists(__CLASS__, self::$instances)) {
            $this->useAsPrimary();
        }
    }

    /**
     * Use this object as default config
     * Note: this is used in *Record, *Table, and *TableStructure DB classes of CMS
     */
    public function useAsDefault() {
        self::$instances['default'] = $this;
    }

    /**
     * Get CmfConfig marked as default one (or primary config if default one not provided)
     * @return $this
     */
    static public function getDefault() {
        return array_key_exists('default', self::$instances) ? self::$instances['default'] : self::getInstance();
    }

    /**
     * Use this object as primary config
     * Note: this object will be returned when you call CmfConfig::getInstance() instead of CustomConfig::getInstance()
     */
    public function useAsPrimary() {
        self::$instances[__CLASS__] = $this;
    }

    /**
     * @return $this
     */
    static public function getPrimary() {
        return CmfConfig::getInstance();
    }

    /**
     * Returns instance of config class it was called from
     * Note: method excluded from toArray() results but key "config_instance" added instead of it
     * @return $this
     */
    static public function getInstance() {
        $class = get_called_class();
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class;
        }
        return self::$instances[$class];
    }

    static public function cmf_routes_config_files() {
        return [
            __DIR__ . '/cmf.routes.php'
        ];
    }

    static public function routes_config_files() {
        return [
//            base_path('routes/admin.php')
        ];
    }

    /**
     * Base DB model class. Used to get instances of real models.
     * Note: you must overwrite this to avoid problems
     * @return string
     */
    static public function base_db_table_class() {
        return CmfDbTable::class;
    }

    /**
     * Suffix for scaffold config class name.
     * If DbObject class is named "Admin" and sufix is "ScaffoldConfig"
     * then scaffold config class name will be "AdminScaffoldConfig"
     * @return string
     */
    static public function scaffold_config_class_suffix() {
        return 'ScaffoldConfig';
    }

    /**
     * Session configs
     * @return array
     * @throws \BadMethodCallException
     */
    static public function session_configs() {
        $config = [
            'table' => static::sessions_table_name(),
            'cookie' => static::sessions_table_name(),
            'lifetime' => 1440,
        ];
        $sessionDriver = strtolower(config('session.driver', 'file'));
        if (in_array($sessionDriver, ['database', 'redis'], true)) {
            $config['connection'] = static::session_connection();
        }
        return $config;
    }

    /**
     * Table name with sessions for db driver. Also cookie key
     * @return string
     */
    static public function sessions_table_name() {
        return str_plural(static::users_table_name()) . '_sessions';
    }

    /**
     * Connection name for session. Required when you use redis or any db driver to store sessions
     * @return string
     * @throws \BadMethodCallException
     */
    static public function session_connection() {
        throw new \BadMethodCallException('You must overwrite session_connection() in ' . get_called_class());
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
                static::auth_guard_name() => [
                    'driver' => 'session',
                    'provider' => static::auth_guard_name(),
                ],
            ],

            'providers' => [
                static::auth_guard_name() => [
                    'driver' => static::auth_user_provider_name(),
                    'model' => static::user_object_class()
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
     * User provider name
     * @return string
     */
    static public function auth_user_provider_name() {
        return 'peskyorm';
    }

    /**
     * Class name of user db object
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function user_object_class() {
        return app(CmsAdmin::class);
    }

    /**
     * Table name where admins/users stored
     * @return string
     */
    static public function users_table_name() {
        /** @var CmsAdminsTableStructure $adminsTableStructure */
        $adminsTableStructure = app(CmsAdminsTableStructure::class);
        return $adminsTableStructure::getTableName();
    }

    /**
     * @return string
     */
    static public function user_login_column() {
        return 'email';
    }

    /**
     * @return string
     */
    static public function user_profile_view() {
        return 'cmf::page.profile';
    }

    /**
     * Additional user profile fields and validators
     * Format: ['filed1' => 'validation rules', 'field2', ...]
     * @return array
     */
    static public function additional_user_profile_fields() {
        return [];
    }

    /**
     * @return string
     */
    static public function password_recovery_email_view() {
        return 'cmf::emails.password_restore_instructions';
    }

    /**
     * Email address used in "From" header for emails sent to users
     * @return string
     * @throws \UnexpectedValueException
     */
    static public function system_email_address() {
        return 'noreply@' . static::domain();
    }

    /**
     * Domain name
     * @return string
     * @throws \UnexpectedValueException
     */
    static public function domain() {
        return request()->getHost();
    }

    /**
     * Url prefix for routes
     * @return string
     */
    static public function url_prefix() {
        return 'admin';
    }

    /**
     * @return string
     */
    static public function recaptcha_private_key() {
        return config('app.recaptcha_private_key');
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

    static public function default_page_title() {
        return static::transCustom('.default_page_title');
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
     * Basic set of middlewares for scaffold api controller
     * @return array
     */
    static public function middleware_for_cmf_scaffold_api_controller() {
        return [];
    }

    /**
     * Middleware to be added to routes that require user authorisation
     * @return array
     */
    static public function middleware_for_routes_that_require_authorisation() {
        return [
            ValidateAdmin::class,
        ];
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

    static public function ui_skin() {
        return 'skin-blue';
    }

    /**
     * View for CMF login form
     * @return string
     */
    static public function login_view() {
        return 'cmf::ui.login';
    }

    /**
     * Enable/disable password restore link in login form
     * @return bool
     */
    static public function is_password_restore_allowed() {
        return true;
    }

    /**
     * View for CMF forgot password form
     * @return string
     */
    static public function forgot_password_view() {
        return 'cmf::ui.forgot_password';
    }

    /**
     * View for CMF replace password form
     * @return string
     */
    static public function replace_password_view() {
        return 'cmf::ui.replace_password';
    }

    /**
     * View for CMF UI
     * @return string
     */
    static public function ui_view() {
        return 'cmf::ui.ui';
    }

    /**
     * @return string
     */
    static public function footer_view() {
        return 'cmf::ui.footer';
    }

    /**
     * View that contains all templates for scaffold section
     * @return string
     */
    static public function scaffold_templates_view_for_normal_table() {
        return 'cmf::scaffold.templates';
    }

    /**
     * View that contains all templates for scaffold section
     * @return string
     */
    static public function scaffold_templates_view_for_key_value_table() {
        return 'cmf::scaffold.templates';
    }

    /**
     * View that shows show admin info in sidebar
     * @return string
     */
    static public function sidebar_admin_info_view() {
        return 'cmf::ui.sidebar_admin_info';
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
        return array_merge(
            [
                [
                    'label' => self::transCustom('.page.dashboard.menu_title'),
                    'url' => routeToCmfPage('dashboard'),
                    'icon' => 'glyphicon glyphicon-dashboard',
                ],
                /*[
                    'label' => self::transCustom('.users.menu_title'),
                    'url' => '/resource/users',
                    'icon' => 'fa fa-group'
                ],*/
                /*[
                    'label' => self::transCustom('.menu.section_utils'),
                    'icon' => 'glyphicon glyphicon-align-justify',
                    'submenu' => [
                        [
                            'label' => self::transCustom('.admins.menu_title'),
                            'url' => '/resource/admins',
                            'icon' => 'glyphicon glyphicon-user'
                        ],
                    ]
                ]*/
            ],
            static::$menuItems
        );
    }

    static protected $menuItems = [];

    /**
     * @param string $itemKey
     * @param array|\Closure $menuItem - format: see menu()
     */
    static public function addMenuItem($itemKey, $menuItem) {
        static::$menuItems[$itemKey] = $menuItem;
    }

    /**
     * Path to CMF translations
     * @return string
     */
    static public function cmf_dictionaries_path() {
        return __DIR__ . '/../resources/lang';
    }

    /**
     * Name for custom CMF dictionary that contains translation for CMF resource sections and pages
     * @return string
     */
    static public function custom_dictionary_name() {
        return 'cmf::custom';
    }

    /**
     * Translate from custom dictionary. Uses CmfConfig::getPrimary()
     * @param $path - must strat with '.'
     * @param array $parameters
     * @param null|string $locale
     * @return string
     */
    static public function transCustom($path, array $parameters = [], $locale = null) {
        $dict = CmfConfig::getPrimary()->custom_dictionary_name();
        $primaryPath = $dict . $path;
        $trans = trans($primaryPath, $parameters, $locale);
        if ($trans === $primaryPath && $dict !== 'cmf::custom') {
            $fallbackPath = 'cmf::custom' . $path;
            $trans = trans($fallbackPath, $parameters, $locale);
            if ($trans === $fallbackPath) {
                return $primaryPath;
            }
        }
        return $trans;
    }

    /**
     * Dictionary that contains general ui translations for CMF
     * @return string
     */
    static public function cmf_general_dictionary_name() {
        return 'cmf::cmf';
    }

    /**
     * @param $path - must strat with '.'
     * @param array $parameters
     * @param null|string $locale
     * @return string
     */
    static public function transGeneral($path, array $parameters = [], $locale = null) {
        $dict = CmfConfig::getPrimary()->cmf_general_dictionary_name();
        $primaryPath = $dict . $path;
        $trans = trans($primaryPath, $parameters, $locale);
        if ($trans === $primaryPath && $dict !== 'cmf::cmf') {
            $fallbackPath = 'cmf::cmf' . $path;
            $trans = trans($fallbackPath, $parameters, $locale);
            if ($trans === $fallbackPath) {
                return $primaryPath;
            }
        }
        return $trans;
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
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', self::getInstance()->url_prefix()) . '_locale';
    }

    static public function session_redirect_key() {
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', self::getInstance()->url_prefix()) . '_redirect';
    }

    static public function session_message_key() {
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', self::getInstance()->url_prefix()) . '_message';
    }

    /**
     * The permission option is the highest-level authentication check that lets you define a closure that should return true if the current user
     * is allowed to view the admin section. Any "falsey" response will send the user back to the 'login_path' defined below.
     *
     * @param Request $request
     * @return bool
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
        return route('cmf_start_page');
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

    /**
     * Additional configs for jQuery Data Tables lib
     * @return array
     */
    static public function data_tables_config() {
        return [
            'scrollX' => true,
            'scrollY' => 'calc(100vh - 248px)',
            'scrollCollapse' => true,
            'width' => '100%',
            'filter' => true,
            'stateSave' => true,
            'dom' => "
                <'row toolbar-container'
                    <'col-xs-12 col-md-5'<'filter-toolbar btn-toolbar text-left'>>
                    <'col-xs-12 col-md-7'<'toolbar btn-toolbar text-right'>>
                >
                <'#query-builder-container'<'#query-builder'>>
                <'row data-grid-container'<'col-sm-12'tr>>
                <'row pagination-container'
                    <'col-md-3 hidden-xs hidden-sm'i>
                    <'col-xs-12 col-md-6'p>
                    <'col-md-3 hidden-xs hidden-sm'l>
                >",
        ];
    }

    /**
     * Additional configs for CKEditor lib
     * @return array
     */
    static public function ckeditor_config() {
        return [
            'language' => app()->getLocale(),
            'toolbarGroups' => [
                [ 'name' => 'clipboard', 'groups' => [ 'clipboard', 'undo' ] ],
                [ 'name' => 'editing', 'groups' => [ 'find', 'selection', 'spellchecker', 'editing' ] ],
                [ 'name' => 'links', 'groups' => [ 'links' ] ],
                [ 'name' => 'insert', 'groups' => [ 'insert' ] ],
                [ 'name' => 'forms', 'groups' => [ 'forms' ] ],
                [ 'name' => 'tools', 'groups' => [ 'tools' ] ],
                [ 'name' => 'document', 'groups' => [ 'mode', 'document', 'doctools' ] ],
                [ 'name' => 'others', 'groups' => [ 'others' ] ],
                [ 'name' => 'about', 'groups' => [ 'about' ] ],
                '/',
                [ 'name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ] ],
                [ 'name' => 'paragraph', 'groups' => [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] ],
                [ 'name' => 'styles', 'groups' => [ 'styles' ] ],
                [ 'name' => 'colors', 'groups' => [ 'colors' ] ]
            ],
            'removeButtons' => 'Superscript,Find,Replace,SelectAll,Scayt,Flash,Smiley,PageBreak,Iframe,Form,Checkbox,'
                . 'Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Maximize,Save,NewPage,Preview,Print,'
                . 'Templates,Strike,Subscript,BidiLtr,BidiRtl,Language,Styles',
            'format_tags' => 'p;h1;h2;h3;pre',
            'removeDialogTabs' => 'image:advanced',
            'extraPlugins' => 'uploadimage',
            'filebrowserImageUploadUrl' => route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]),
            'uploadUrl' => route('cmf_ckeditor_upload_image', ['_token' => csrf_token()])
        ];
    }

    /**
     * Variables that will be sent to js and stored into AppData
     * To access data from js code use AppData.key_name
     * @return array
     */
    static public function js_app_data() {
        return [];
    }

    static private $tableNameInRouteMap = [];

    /**
     * Map $tableNameInRoute to $table and $scaffoldConfigClass to be used in CmfConfig::getScaffoldConfig() and
     * CmfConfig::getTableByUnderscoredName()
     * @param TableInterface $table
     * @param string $scaffoldConfigClass - name of class that extends PeskyCMF\Scaffold\ScaffoldConfig class
     * @param null|string $tableNameInRoute - null: use table name from $table
     */
    static public function registerDbTableAndScaffoldConfig(TableInterface $table, $scaffoldConfigClass, $tableNameInRoute = null) {
        self::$tableNameInRouteMap[$tableNameInRoute ?: $table->getTableStructure()->getTableName()] = [
            'table' => $table,
            'scaffold_class' => $scaffoldConfigClass
        ];
    }

    /**
     * Get ScaffoldConfig instance
     * @param TableInterface $table - a model to be used in ScaffoldConfig
     * @param string $tableNameInRoute - table name passed via route parameter, may differ from $table->getTableName()
     *      and added here to be used in child configs when you need to use scaffolds with fake table names.
     *      It should be used together with static::getModelByTableName() to provide correct model for a fake table name
     * @return ScaffoldConfig
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getScaffoldConfig(TableInterface $table, $tableNameInRoute) {
        if (array_key_exists($tableNameInRoute, self::$tableNameInRouteMap)) {
            $className = self::$tableNameInRouteMap[$tableNameInRoute]['scaffold_class'];
        } else if (app()->offsetExists("cms.section.{$tableNameInRoute}.scaffold")) {
            return app("cms.section.{$tableNameInRoute}.scaffold");
        } else {
            /** @var ClassBuilder $builderClass */
            $builderClass = static::getDbClassesBuilderClass();
            $className = preg_replace(
                '%\\\([A-Za-z0-9]+?)' . $builderClass::makeTableClassName('') . '$%',
                '\\\$1' . static::scaffold_config_class_suffix(),
                get_class($table)
            );
        }
        return new $className($table, $tableNameInRoute);
    }

    /**
     * Get TableInterface instance for $tableName
     * Note: can be ovewritted to allow usage of fake tables in resources routes
     * It is possible to use this with static::getScaffoldConfig() to alter default scaffold configs
     * @param string $tableName
     * @return TableInterface
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getTableByUnderscoredName($tableName) {
        if (array_key_exists($tableName, self::$tableNameInRouteMap)) {
            return self::$tableNameInRouteMap[$tableName]['table'];
        } else if (app()->offsetExists("cms.section.{$tableName}.table")) {
            return app("cms.section.{$tableName}.table");
        } else {
            /** @var ClassBuilder $builderClass */
            $builderClass = static::getDbClassesBuilderClass();
            /** @var Table $class */
            $class = static::getDbClassesNamespaceForTable($tableName) . '\\' . $builderClass::makeTableClassName($tableName);
            return $class::getInstance();
        }
    }

    /**
     * @param string $tableName
     * @return string
     */
    static public function getDbClassesNamespaceForTable($tableName) {
        static $namespace = null;
        if ($namespace === null) {
            $namespace = '\\' . (new \ReflectionClass(static::base_db_table_class()) )->getNamespaceName() . '\\';
        }
        return $namespace . StringUtils::classify($tableName);
    }

    /**
     * Shortcut to static::getScaffoldConfig()
     * @param string $tableName
     * @return ScaffoldConfig
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getScaffoldConfigByTableName($tableName) {
        if (app()->offsetExists("cms.section.{$tableName}.scaffold")) {
            return app("cms.section.{$tableName}.scaffold");
        } else {
            return static::getScaffoldConfig(static::getTableByUnderscoredName($tableName), $tableName);
        }
    }

    /**
     * Class that knows how to build db classes (table, structure and record)
     * @return string
     */
    static public function getDbClassesBuilderClass() {
        return ClassBuilder::class;
    }

    /**
     * @return string|null
     * @throws \UnexpectedValueException
     */
    static public function getTableNameFromCurrentRoute() {
        static $tableNameForRoutes;
        if ($tableNameForRoutes === null) {
            if (!request()->route()->hasParameter('table_name')) {
                return null;
            }
            $tableNameForRoutes = request()->route()->parameter('table_name');
        }
        return $tableNameForRoutes;
    }

    /**
     * @return array
     */
    static public function getAdditionalWysywygDataInsertsForCmsPages() {
        return [];
    }

    /**
     * @return array - key - section name, value - array that contains objects of class CmsApiDocs
     */
    public static function getApiDocsSections() {
        return [];
    }

}