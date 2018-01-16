<?php

namespace PeskyCMF\Config;

use PeskyCMF\Http\Middleware\ValidateCmfUser;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\ClassBuilder;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\StringUtils;
use Symfony\Component\Debug\Exception\ClassNotFoundException;

class CmfConfig extends ConfigsContainer {

    static private $instances = [];

    protected function __construct() {
        self::$instances[get_class($this)] = $this;
        if (!array_key_exists(__CLASS__, self::$instances)) {
            $this->useAsPrimary();
        }
    }

    private static function _protect() {
        if (static::class === CmfConfig::class) {
            throw new \BadMethodCallException('Attempt to call method of CmfConfig class instead of its child class');
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
        return self::getInstance();
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

    /**
     * File name for this site section in 'configs' folder of project's root directory (without '.php' extension)
     * Example: 'admin' for config/admin.php;
     */
    static protected function configsFileName() {
        return 'peskycmf';
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    static public function config($key, $default = null) {
        return config(static::configsFileName() . '.' . $key, $default);
    }

    static public function cmf_routes_config_files() {
        return [
            __DIR__ . '/peskycmf.routes.php',
        ];
    }

    /**
     * @return array
     */
    static public function language_detector_configs() {
        return [
            'autodetect' => true,
            'driver' => 'browser',
            'cookie' => true,
            'cookie_name' => static::makeUtilityKey('locale'),
            'cookie_encrypt' => true,
            'languages' => static::locales(),
        ];
    }

    /**
     * Set Auth guard to use
     * @return string
     */
    static public function auth_guard_name() {
        return static::config('auth_guard.name', function () {
            $config = static::config('auth_guard');
            return is_string($config) ? $config : 'admin';
        });
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Auth\SessionGuard
     */
    static public function getAuth() {
        return \Auth::guard(static::auth_guard_name());
    }

    /**
     * @return \PeskyCMF\Db\Admins\CmfAdmin|\Illuminate\Contracts\Auth\Authenticatable|\PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey|\App\Db\Admins\Admin|null
     */
    static public function getUser() {
        return static::getAuth()->user();
    }

    /**
     * Class name of user db object
     * @return string
     */
    static public function user_record_class() {
        return static::config('user_record_class', function () {
            throw new \UnexpectedValueException('You need to provide a DB Record class for users');
        });
    }

    /**
     * Table name where admins/users stored
     * @return string
     */
    static public function users_table_name() {
        /** @var RecordInterface $userObjectClass */
        $userObjectClass = static::user_record_class();
        return $userObjectClass::getTable()->getName();
    }

    /**
     * @return string
     */
    static public function user_login_column() {
        return static::config('user_login_column', 'email');
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
        return static::config('system_email_address', function () {
            return 'noreply@' . request()->getHost();
        });
    }

    /**
     * Usera access policy class to use in CMF
     * @return string
     */
    static public function cmf_user_acceess_policy_class() {
        return static::config('acceess_policy_class', CmfAccessPolicy::class);
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
    static public function configureAuthorizationGatesAndPolicies($policyName = 'CmfAccessPolicy') {
        app()->singleton($policyName, static::cmf_user_acceess_policy_class());
        \Gate::resource('resource', $policyName, [
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
            'custom_page_for_item' => 'custom_page_for_item',
        ]);
        \Gate::define('cmf_page', $policyName . '@cmf_page');
    }

    /**
     * Url prefix for routes
     * @return string
     */
    static public function url_prefix() {
        return static::config('url_prefix', 'admin');
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
        return static::config('views_subfolder', 'admin') . '.';
    }

    /**
     * .css files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_css_includes() {
        return (array)static::config('css_files', []);
    }

    /**
     * .js files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_js_includes() {
        return (array)static::config('js_files', []);
    }

    /**
     * @return array
     */
    static public function layout_js_code_blocks() {
        return (array)static::config('js_code_blocks', []);
    }

    /**
     * @return string
     */
    static public function default_page_title() {
        return setting()->default_browser_title(function () {
            return static::transCustom('.default_page_title');
        }, true);
    }

    /**
     * @return string
     */
    static public function page_title_addition() {
        return setting()->browser_title_addition(function () {
            return static::default_page_title();
        }, true);
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
     * Prefix for route names in peskycmf.routes.php
     * Use with caution and only when you really know what you're doing
     * @return string
     */
    static public function routes_names_prefix() {
        return '';
    }

    /**
     * @param string $routeAlias
     * @return string
     */
    static public function getRouteName($routeAlias) {
        return static::routes_names_prefix() . $routeAlias;
    }

    /**
     * Basic set of middlewares for scaffold api controller
     * @return array
     */
    static public function middleware_for_cmf_scaffold_api_controller() {
        return [];
    }

    /**
     * Middleware to be added to routes that require user authentication
     * @return array
     */
    static public function middleware_for_routes_that_require_authentication() {
        return static::config('routes_auth_middleware', [ValidateCmfUser::class]);
    }

    /**
     * View for CMF layout
     * @return string
     */
    static public function layout_view() {
        return 'cmf::layout';
    }

    static public function ui_skin() {
        return static::config('ui_skin', 'skin-blue');
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
        return static::config('is_password_restore_allowed', true);
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
     * Keys:
     *  - label - text of menu item (required)
     *  - icon - icon for menu item (optinal)
     *  - url - where to send user (required if no 'submenu' present; can contain empty value to hide menu item)
     *  - submenu - array of arrays (optional); arrays may contain all keys described here except 'submenu' (3rd level of menu items not supported)
     *  - additional - any html to add to menu item (optional)
     *  - counter - name of a "counter" variable (optional); adds <span class="pull-right-container"> to menu item.
     *          You can provide contents for this tag via CmfConfig::getCountersForMenu() method.
     *          Example: public function getCountersForMenu() {
     *              return ['prending_orders' => '<span class="label label-primary pull-right">2</span>'];
     *          }
     *          'prending_orders' here is the name of a "counter".
     *          The idea is to add a possibility to show some autoupdatable numbers (counters) on menu items like
     *          pending orders count, new messages count, etc...
     *          Counters updated on every POST/PUT/DELETE request and by timeout. Also you can be manually
     *          request counters update by JS function AdminUI.updateMenuCounters();
     *          Update interval can be changed via JS app config: CmfSettings.menuCountersUpdateIntervalMs = 30000;
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
                    ],
                ]*/
                /*[
                    'label' => 'required',
                    'icon' => 'optional',
                    'submenu' => [], //< 'optional'
                    'url' => 'required
                ]*/
            ],
            static::getMenuItems()
        );
    }

    /**
     * Get values for menu items counters (details in CmfConfig::menu())
     * @return array like ['pending_orders' => '<span class="label label-primary pull-right">2</span>']
     */
    static public function getValuesForMenuItemsCounters() {
        $counters = [];
        /** @var ScaffoldConfig $scaffoldConfigClass */
        foreach (static::getInstance()->resources as $scaffoldConfigClass) {
            $counterClosure = $scaffoldConfigClass::getMenuItemCounterValue();
            if (!empty($counterClosure)) {
                $counters[$scaffoldConfigClass::getMenuItemCounterName()] = value($counterClosure);
            }
        }
        return $counters;
    }

    private $menuItems = [];
    private $menuItemsAreDirty = true;

    /**
     * @param string $itemKey
     * @param array|\Closure $menuItem - format: see menu()
     */
    static public function addMenuItem($itemKey, $menuItem) {
        static::getInstance()->menuItems[$itemKey] = $menuItem;
        static::getInstance()->menuItemsAreDirty = true;
    }

    /**
     * @return array
     */
    static protected function getMenuItems() {
        if (static::getInstance()->menuItemsAreDirty) {
            // filter menu items and exec closures
            $items = [];
            foreach (static::getInstance()->menuItems as $name => $value) {
                if ($value instanceof \Closure) {
                    // convert closures in menu items to arrays
                    $tmp = $value();
                    if (is_array($tmp) && !empty($tmp)) {
                        $items[$name] = $tmp;
                    }
                } else if (!empty($value)) {
                    $items[$name] = $value;
                }
            }
            static::getInstance()->menuItems = $items;
            static::getInstance()->menuItemsAreDirty = false;
        }
        return static::getInstance()->menuItems;
    }

    /**
     * Get menu item config or null if there is no such menu item
     * @param string $resourceName
     * @return array|null
     */
    static protected function getMenuItem($resourceName) {
        return array_get(static::getMenuItems(), $resourceName);
    }

    /**
     * Menu item for api logs page.
     * Note: it is not added automatically to menu items - you need to add it manually to self::menu()
     * @return array
     */
    static public function getApiDocsMenuItem() {
        return [
            'label' => cmfTransCustom('api_docs.menu_title'),
            'icon' => 'glyphicon glyphicon-book',
            'url' => routeToCmfPage('api_docs'),
        ];
    }

    /**
     * Name for custom CMF dictionary that contains translation for CMF resource sections and pages
     * @return string
     */
    static public function custom_dictionary_name() {
        return static::config('dictionary', 'cmf::custom');
    }

    /**
     * Translate from custom dictionary. You can use it via CmfConfig::transCustom() insetad of
     * CmfConfig::getPrimary()->transCustom() if you need to get translation for primary config.
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::custom' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string
     */
    static public function transCustom($path, array $parameters = [], $locale = null) {
        $dict = self::getPrimary()->custom_dictionary_name();
        $path = '.' . ltrim($path, '.');
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
     * Translate from custom dictionary. You can use it via CmfConfig::transGeneral() insetad of
     * CmfConfig::getPrimary()->transGeneral() if you need to get translation for primary config
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::cmf' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string
     */
    static public function transGeneral($path, array $parameters = [], $locale = null) {
        $dict = self::getPrimary()->cmf_general_dictionary_name();
        $path = '.' . ltrim($path, '.');
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
        return static::config('locale', 'en');
    }

    /**
     * Supported locales for CMF
     * Note: you can redirect locales using key as locale to redirect from and value as locale to redirect to
     * For details see: https://github.com/vluzrmos/laravel-language-detector
     * @return array
     */
    static public function locales() {
        return static::config('locales', ['en']);
    }

    /**
     * Change locale inside CMF/CMS area
     * @param string $locale
     */
    static public function setLocale($locale) {
        static::_protect();
        \LanguageDetector::apply($locale);
        \LanguageDetector::addCookieToQueue(\App::getLocale());
        Column::setValidationErrorsMessages((array)static::transGeneral('form.message.column_validation_errors') ?: []);
    }

    /**
     * Reset locale to default
     */
    static public function resetLocale() {
        static::_protect();
        static::setLocale(\LanguageDetector::getDriver()->detect());
    }

    /**
     * @return string
     */
    static public function session_redirect_key() {
        return static::makeUtilityKey('redirect');
    }

    /**
     * @return string
     */
    static public function session_message_key() {
        return static::makeUtilityKey('message');
    }

    /**
     * List of roles for CMF
     * @return array
     */
    static public function roles_list() {
        return static::config('roles', ['admin']);
    }

    /**
     * Default admin role
     * Used in admins table config and admin creation
     * @return string
     */
    static public function default_role() {
        return static::config('default_role', 'admin');
    }

    /**
     * Start page URL of CMS section
     *
     * @param bool $absolute
     * @return string
     */
    static public function home_page_url($absolute = false) {
        return route(static::getRouteName('cmf_start_page'), [], $absolute);
    }

    /**
     * Login page URL of CMS section
     *
     * @param bool $absolute
     * @return string
     */
    static public function login_page_url($absolute = false) {
        return route(static::getRouteName('cmf_login'), [], $absolute);
    }

    /**
     * The logout route is the path where Administrator will send the user when they click the logout link
     *
     * @param bool $absolute
     * @return string
     */
    static public function logout_page_url($absolute = false) {
        return route(static::getRouteName('cmf_logout'), [], $absolute);
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
        return static::config('login_logo') ?: '<img src="/packages/cmf/img/peskycmf-logo-black.svg" width="340" alt=" " class="mb15">';
    }

    /**
     * Logo image to display in sidebar
     * @return string
     */
    static public function sidebar_logo() {
        return static::config('sidebar_logo') ?: '<img src="/packages/cmf/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';
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
                [ 'name' => 'colors', 'groups' => [ 'colors' ] ],
            ],
            'removeButtons' => 'Superscript,Find,Replace,SelectAll,Scayt,Flash,Smiley,PageBreak,Iframe,Form,Checkbox,'
                . 'Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Maximize,Save,NewPage,Preview,Print,'
                . 'Templates,Strike,Subscript,BidiLtr,BidiRtl,Language,Styles',
            'format_tags' => 'p;h1;h2;h3;pre',
            'enterMode' => 1, //< insert <p> on pressing ENTER
            'forceEnterMode' => true,
            'removeDialogTabs' => 'image:advanced',
            'extraPlugins' => 'uploadimage',
            'filebrowserImageUploadUrl' => route(static::getRouteName('cmf_ckeditor_upload_image'), ['_token' => csrf_token()]),
            'uploadUrl' => route(static::getRouteName('cmf_ckeditor_upload_image'), ['_token' => csrf_token()]),
            'contentsCss' => static::css_files_for_wysiwyg_editor(),
        ];
    }

    /**
     * Add some css files inside wysuwyg editor to allow custom styling while editing wysiwyg contents
     * @return array
     */
    static public function css_files_for_wysiwyg_editor() {
        return [];
    }

    /**
     * JS application settings (accessed via CmfSettings global variable)
     * @return array
     */
    static public function js_app_settings() {
        return [
            'isDebug' => config('app.debug'),
            'rootUrl' => '/' . trim(static::url_prefix(), '/'),
            'uiUrl' => cmfRoute('cmf_main_ui', [], false, static::getInstance()),
            'userDataUrl' => cmfRoute('cmf_profile_data', [], false, static::getInstance()),
            'menuCountersDataUrl' => cmfRoute('cmf_menu_counters_data', [], false, static::getInstance()),
            'defaultPageTitle' => static::default_page_title(),
            'pageTitleAddition' => static::page_title_addition(),
            'localizationStrings' => static::transGeneral('ui.js_component')
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

    protected $resources = [];

    /**
     * Map $tableNameInRoute to $table and $scaffoldConfigClass to be used in CmfConfig::getScaffoldConfig() and
     * CmfConfig::getTableByUnderscoredName(). It also adds menu item if it provided by ScaffoldConfig
     * @param string $scaffoldConfigClass - name of class that extends PeskyCMF\Scaffold\ScaffoldConfig class
     * @param null|string $resourceName - null: use table name from $table
     */
    static public function registerScaffoldConfigForResource($resourceName, $scaffoldConfigClass) {
        /** @var ScaffoldConfig $scaffoldConfigClass */
        static::getInstance()->resources[$resourceName] = $scaffoldConfigClass;
        static::addMenuItem($resourceName, function () use ($scaffoldConfigClass) {
            return $scaffoldConfigClass::getMainMenuItem();
        });
    }

    protected $scaffoldConfigs = [];
    /**
     * Get ScaffoldConfig instance
     * @param string $resourceName - table name passed via route parameter, may differ from $table->getTableName()
     *      and added here to be used in child configs when you need to use scaffolds with fake table names.
     *      It should be used together with static::getModelByTableName() to provide correct model for a fake table name
     * @return ScaffoldConfig
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \InvalidArgumentException
     */
    static public function getScaffoldConfig($resourceName) {
        if (!array_has(static::getInstance()->scaffoldConfigs, $resourceName)) {
            $className = array_get(static::getInstance()->resources, $resourceName, function () use ($resourceName) {
                return static::config('resources.' . $resourceName, function () use ($resourceName) {
                    throw new \InvalidArgumentException(
                        'There is no known ScaffoldConfig class for resource "' . $resourceName . '"'
                    );
                });
            });
            if (!class_exists($className)) {
                throw new ClassNotFoundException('Class ' . $className . ' not exists', new \ErrorException());
            }
            static::getInstance()->scaffoldConfigs[$resourceName] = new $className();
        }
        return static::getInstance()->scaffoldConfigs[$resourceName];
    }

    /**
     * @return ScaffoldConfig[]
     */
    static public function getRegisteredScaffolds() {
        return static::getInstance()->resources;
    }

    protected $tables = [];

    /**
     * Register DB Table for resource name (or table name if it is used as resource name) to optimize
     * CmfCongig::getTableByUnderscoredName() usage
     * @param TableInterface $table
     * @param null|string $resourceName - null: $table::getName() will be used as resource name
     */
    static public function registerTable(TableInterface $table, $resourceName = null) {
        if (empty($resourceName)) {
            $resourceName = $table::getName();
        }
        static::getInstance()->tables[$resourceName] = $table;
    }

    /**
     * Get TableInterface instance for $tableName
     * Note: can be ovewritted to allow usage of fake tables in resources routes
     * It is possible to use this with static::getScaffoldConfig() to alter default scaffold configs
     * @param string $tableName
     * @return TableInterface
     * @throws \ReflectionException
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getTableByUnderscoredName($tableName) {
        if (!array_key_exists($tableName, static::getInstance()->tables)) {
            if (array_key_exists($tableName, self::getInstance()->resources)) {
                /** @var ScaffoldConfigInterface $scaffoldConfigClass */
                $scaffoldConfigClass = self::getInstance()->resources[$tableName];
                $table = $scaffoldConfigClass::getTable();
            } else {
                /** @var ClassBuilder $builderClass */
                $builderClass = static::getDbClassesBuilderClass();
                /** @var Table $className */
                $className = static::getDbClassesNamespaceForTable($tableName) . '\\' . $builderClass::makeTableClassName($tableName);
                if (!class_exists($className)) {
                    throw new ClassNotFoundException('Class ' . $className . ' not exists', new \ErrorException());
                }
                $table = $className::getInstance();
            }
            static::getInstance()->tables[$tableName] = $table;
        }
        return static::getInstance()->tables[$tableName];
    }

    /**
     * @return ClassBuilder
     */
    static protected function getDbClassesBuilderClass() {
        return config('peskyorm.class_builder');
    }

    /**
     * @param string $tableName
     * @return string
     * @throws \ReflectionException
     */
    static protected function getDbClassesNamespaceForTable($tableName) {
        static $namespace = null;
        if ($namespace === null) {
            $namespace = rtrim(config('peskyorm.classes_namespace', 'App\\Db'), '\\') . '\\';
        }
        return $namespace . StringUtils::classify($tableName);
    }

    /**
     * @return string|null
     * @throws \UnexpectedValueException
     */
    static public function getResourceNameFromCurrentRoute() {
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
     * Data inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createDataInsertConfig() and WysiwygFormInput::createDataInsertConfigWithArguments()
     * to create valid config
     * @param ScaffoldConfig $scaffold
     * @return array
     */
    static public function getAdditionalWysywygDataInsertsForCmsPages(ScaffoldConfig $scaffold) {
        return [];
    }

    /**
     * Html inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createHtmlInsertConfig('<html>', 'menu title') to create valid config
     * @param ScaffoldConfig $scaffold
     * @return array
     */
    static public function getWysywygHtmlInsertsForCmsPages(ScaffoldConfig $scaffold) {
        return [];
    }

    /**
     * Provides sections with list of objects of classes that extend CmfApiDocsSection class to be displayed in api docs section
     * @return array - key - section name, value - array that contains names of classes that extend CmfApiDocsSection class
     */
    static public function getApiDocsSections() {
        return static::config('api_docs_class_names', []);
    }

    protected $httpRequestsLogger;

    /**
     * @return null|ScaffoldLoggerInterface;
     */
    static public function getHttpRequestsLogger() {
        if (!static::getInstance()->httpRequestsLogger && app()->bound(ScaffoldLoggerInterface::class)) {
            static::setHttpRequestsLogger(app(ScaffoldLoggerInterface::class));
        }
        return static::getInstance()->httpRequestsLogger;
    }

    /**
     * Logger will be used to logs requested records pk and changes
     * @param ScaffoldLoggerInterface $httpRequestsLogger
     */
    static public function setHttpRequestsLogger(ScaffoldLoggerInterface $httpRequestsLogger) {
        static::getInstance()->httpRequestsLogger = $httpRequestsLogger;
    }

    /**
     * @return array
     */
    static public function getCachedPagesTemplates() {
        return \Cache::remember(
            static::getCacheKeyForOptimizedUiTemplates('pages'),
            (int)static::config('optimize_ui_templates.timeout', 0),
            function () {
                $generalControllerClass = static::cmf_general_controller_class();
                /** @var \PeskyCMF\Http\Controllers\CmfGeneralController $controller */
                $controller = new $generalControllerClass();
                if (static::getUser()) {
                    return [
                        route(static::getRouteName('cmf_main_ui'), [], false) => $controller->getBasicUiView(),
                    ];
                } else {
                    return [
                        route(static::getRouteName('cmf_login'), [], false) . '.html' => $controller->getLoginTpl(),
                        route(static::getRouteName('cmf_forgot_password'), [], false) . '.html' => $controller->getForgotPasswordTpl(),
                    ];
                }
            }
        );
    }

    /**
     * @return array
     */
    static public function getCachedResourcesTemplates() {
        if (!static::getUser()) {
            return [];
        }
        return \Cache::remember(
            static::getCacheKeyForOptimizedUiTemplates('resources'),
            (int)static::config('optimize_ui_templates.timeout', 0),
            function () {
                return static::collectResourcesTemplatesToBeCached();
            }
        );
    }

    /**
     * @return array
     */
    static protected function collectResourcesTemplatesToBeCached() {
        $resourceTemplates = [];
        /** @var ScaffoldConfig $scaffoldConfigClass */
        foreach (static::getRegisteredScaffolds() as $resourceName => $scaffoldConfigClass) {
            /** @var ScaffoldConfig $scaffoldConfig */
            $scaffoldConfig = new $scaffoldConfigClass();
            $splitted = $scaffoldConfig->renderTemplatesAndSplit();
            if (!empty($splitted)) {
                $resourceTemplates[$resourceName] = $splitted;
            }
        }
        return $resourceTemplates;
    }

    /**
     * User-id-based cache key
     * @param string $group
     * @return string
     */
    static protected function getCacheKeyForOptimizedUiTemplates($group) {
        return static::getCacheKeyForOptimizedUiTemplatesBasedOnUserId($group);
    }

    /**
     * User-id-based cache key
     * @param string $group
     * @return string
     */
    static protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserId($group) {
        if (static::cmf_user_acceess_policy_class() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $user = static::getUser();
            $userId = $user ? $user->getAuthIdentifier() : 'not_authenticated';
        }
        return static::url_prefix() . '_templates_' . app()->getLocale() . '_' . $group . '_user_' . $userId;
    }

    /**
     * Role-based cache key
     * @param string $group
     * @return string
     */
    static protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserRole($group) {
        if (static::cmf_user_acceess_policy_class() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $userId = 'not_authenticated';
            $user = static::getUser();
            if ($user && $user->existsInDb()) {
                $userId = $user->is_superadmin ? '__superadmin__' : $user->role;
            }
        }
        return static::url_prefix() . '_templates_' . app()->getLocale() . '_' . $group . '_user_' . $userId;
    }

    /**
     * @param $keySuffix
     * @return string
     */
    static public function makeUtilityKey($keySuffix) {
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', static::url_prefix()) . '_' . $keySuffix;
    }

}