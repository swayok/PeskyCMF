<?php

namespace PeskyCMF\Config;

use App\AppSettings;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Application;
use PeskyCMF\ApiDocs\CmfApiDocumentation;
use PeskyCMF\ApiDocs\CmfApiMethodDocumentation;
use PeskyCMF\Auth\CmfAccessPolicy;
use PeskyCMF\Auth\CmfAuthModule;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\Http\Middleware\UseCmfSection;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Providers\PeskyCmfLanguageDetectorServiceProvider;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyORM\ORM\ClassBuilder;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Table;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\Folder;
use Swayok\Utils\StringUtils;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

abstract class CmfConfig extends ConfigsContainer {

    static private $instances = [];

    protected final function __construct() {
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
    public final function useAsDefault() {
        self::$instances['default'] = $this;
    }

    /**
     * Get CmfConfig marked as default one (or primary config if default one not provided)
     * @return CmfConfig
     */
    static final public function getDefault(): CmfConfig {
        return isset(self::$instances['default']) ? self::$instances['default'] : self::getPrimary();
    }

    /**
     * Use this object as primary config
     * Note: this object will be returned when you call CmfConfig::getInstance() instead of CustomConfig::getInstance()
     */
    public final function useAsPrimary() {
        self::$instances[__CLASS__] = $this;
    }

    /**
     * @return CmfConfig
     */
    static final public function getPrimary(): CmfConfig {
        if (!isset(self::$instances[__CLASS__])) {
            throw new \BadMethodCallException('Primary CMF Config is not specified');
        }
        return self::$instances[__CLASS__];
    }

    /**
     * Returns instance of config class it was called from
     * Note: method excluded from toArray() results but key "config_instance" added instead of it
     * @return $this
     */
    static final public function getInstance() {
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
    static protected function configsFileName(): string {
        return 'peskycmf';
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    static public function config($key, $default = null) {
        return config(static::getInstance()->configsFileName() . '.' . $key, $default);
    }

    static public function cmf_routes_config_files(): array {
        return [
            __DIR__ . '/peskycmf.routes.php',
        ];
    }

    /**
     * @return array
     */
    static public function language_detector_configs(): array {
        return [
            'autodetect' => true,
            'driver' => 'browser',
            'cookie' => true,
            'cookie_name' => static::makeUtilityKey('locale'),
            'cookie_encrypt' => true,
            'languages' => static::locales_for_language_detector(),
        ];
    }

    /**
     * @return PeskyCmfAppSettings|\App\AppSettings
     */
    static public function getAppSettings(): AppSettings {
        return app(PeskyCmfAppSettings::class);
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Auth\SessionGuard
     */
    static public function getAuthGuard(): Guard {
        return static::getAuthModule()->getAuthGuard();
    }

    /**
     * @return \PeskyCMF\Db\Admins\CmfAdmin|\Illuminate\Contracts\Auth\Authenticatable|\PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey|\App\Db\Admins\Admin|null
     */
    static public function getUser(): RecordInterface {
        return static::getAuthModule()->getUser();
    }

    /**
     * @return CmfAuthModule
     */
    static public function getAuthModule(): CmfAuthModule {
        return app(CmfAuthModule::class);
    }

    /**
     * Email address used in "From" header for emails sent to users
     * @return string
     * @throws \UnexpectedValueException
     */
    static public function system_email_address(): string {
        return static::config('system_email_address', function () {
            return 'noreply@' . request()->getHost();
        });
    }

    /**
     * Url prefix for routes
     * @return string
     */
    static public function url_prefix(): string {
        return static::config('url_prefix', 'admin');
    }

    /**
     * Url prefix for routes
     * @return string
     */
    static public function app_subfolder(): string {
        return static::config('app_subfolder', 'Admin');
    }

    /**
     * @return string
     */
    static public function getPathToCmfClasses(): string {
        return app_path(static::app_subfolder());
    }

    /**
     * @return string|null
     */
    static public function recaptcha_private_key(): ?string {
        return config('services.recaptcha.private_key');
    }

    /**
     * @return string|null
     */
    static public function recaptcha_public_key(): ?string {
        return config('services.recaptcha.public_key');
    }

    static public function recaptcha_js_file(): string {
        return 'https://www.google.com/recaptcha/api.js?hl=' . static::getShortLocale();
    }

    /**
     * Prefix to load custom views from.
     * For example
     * - if custom views stored in /resources/views/admin - prefix should be "admin."
     * - if you placed views under namespace "admin" - prefix should be "admin:"
     * @return string
     */
    static public function custom_views_prefix(): string {
        return static::config('views_subfolder', 'admin') . '.';
    }

    /**
     * .css files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_css_includes(): array {
        return (array)static::config('css_files', []);
    }

    /**
     * .js files to insert into cmf::layout.blade.php
     * @return array
     */
    static public function layout_js_includes(): array {
        return (array)static::config('js_files', []);
    }

    /**
     * @return array
     */
    static public function layout_js_code_blocks(): array {
        return (array)static::config('js_code_blocks', []);
    }

    /**
     * @return string
     */
    static public function default_page_title(): string {
        return setting()->default_browser_title(function () {
            return static::transCustom('.default_page_title');
        }, true);
    }

    /**
     * @return string
     */
    static public function page_title_addition(): string {
        return setting()->browser_title_addition(function () {
            return static::default_page_title();
        }, true);
    }

    /**
     * Controller class name for CMF scaffolds API
     * @return string
     */
    static public function cmf_scaffold_api_controller_class(): string {
        return \PeskyCMF\Http\Controllers\CmfScaffoldApiController::class;
    }

    /**
     * General controller class name for CMF (basic ui views, custom pages views, login/logout, etc.)
     * @return string
     */
    static public function cmf_general_controller_class(): string {
        return \PeskyCMF\Http\Controllers\CmfGeneralController::class;
    }

    /**
     * Prefix for route names in peskycmf.routes.php
     * Use with caution and only when you really know what you're doing
     * @return string
     */
    static public function routes_names_prefix(): string {
        return static::url_prefix();
    }

    /**
     * @param string $routeAlias
     * @return string
     */
    static public function getRouteName($routeAlias): string {
        return static::routes_names_prefix() . $routeAlias;
    }

    /**
     * Note: placed here to avoid problems with auth module constructor when registering routes for all cmf sections
     * @return array
     */
    static public function auth_middleware(): array {
        return (array)static::config('auth.middleware', [CmfAuth::class]);
    }

    /**
     * Basic set of middlewares for scaffold api controller
     * @return array
     */
    static public function middleware_for_cmf_scaffold_api_controller(): array {
        return [];
    }

    /**
     * View for CMF layout
     * @return string
     */
    static public function layout_view(): string {
        return 'cmf::layout';
    }

    static public function ui_skin(): string {
        return static::config('ui_skin', 'skin-blue');
    }

    /**
     * View for CMF UI
     * @return string
     */
    static public function ui_view(): string {
        return 'cmf::ui.ui';
    }

    /**
     * @return string
     */
    static public function footer_view(): string {
        return 'cmf::ui.footer';
    }

    /**
     * View that contains all templates for scaffold section
     * @return string
     */
    static public function scaffold_templates_view_for_normal_table(): string {
        return 'cmf::scaffold.templates';
    }

    /**
     * View that contains all templates for scaffold section
     * @return string
     */
    static public function scaffold_templates_view_for_key_value_table(): string {
        return 'cmf::scaffold.templates';
    }

    static public function top_navbar_view(): string {
        return 'cmf::ui.top_navbar';
    }

    /**
     * View that shows show admin info in sidebar
     * @return string
     */
    static public function sidebar_admin_info_view(): string {
        return 'cmf::ui.sidebar_admin_info';
    }

    /**
     * View name for CMF menu
     * @return string
     */
    static public function menu_view(): string {
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
     *              'open' => false,
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
    static public function menu(): array {
        return array_merge(
            [
                [
                    'label' => static::transCustom('.page.dashboard.menu_title'),
                    'url' => routeToCmfPage('dashboard'),
                    'icon' => 'glyphicon glyphicon-dashboard',
                ],
                /*[
                    'label' => static::transCustom('.users.menu_title'),
                    'url' => '/resource/users',
                    'icon' => 'fa fa-group'
                ],*/
                /*[
                    'label' => static::transCustom('.menu.section_utils'),
                    'icon' => 'glyphicon glyphicon-align-justify',
                    'submenu' => [
                        [
                            'label' => static::transCustom('.admins.menu_title'),
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
    static public function getValuesForMenuItemsCounters(): array {
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
     * @param array $items - list of itemd to return, if empty - will return all items
     * @return array
     */
    static protected function getMenuItems(...$items): array {
        if (static::getInstance()->menuItemsAreDirty) {
            // filter menu items and exec closures
            $menuItems = [];
            foreach (static::getInstance()->menuItems as $name => $value) {
                if ($value instanceof \Closure) {
                    // convert closures in menu items to arrays
                    $tmp = $value();
                    if (is_array($tmp) && !empty($tmp)) {
                        $menuItems[$name] = $tmp;
                    }
                } else if (!empty($value)) {
                    $menuItems[$name] = $value;
                }
            }
            static::getInstance()->menuItems = $menuItems;
            static::getInstance()->menuItemsAreDirty = false;
        }
        if (empty($items)) {
            return static::getInstance()->menuItems;
        } else {
            return array_intersect_key(static::getInstance()->menuItems, array_flip($items));
        }
    }

    /**
     * @param array $excludeItems - list of menu items to exclude
     * @return array
     */
    static protected function getMenuItemsExcept(...$excludeItems): array {
        if (empty($excludeItems)) {
            return static::getInstance()->getMenuItems();
        } else {
            return array_diff_key(static::getInstance()->getMenuItems(), array_flip($excludeItems));
        }
    }

    /**
     * Get menu item config or null if there is no such menu item
     * @param string $resourceName
     * @return array|null
     */
    static protected function getMenuItem($resourceName): ?array {
        return array_get(static::getMenuItems(), $resourceName);
    }

    /**
     * Menu item for api logs page.
     * Note: it is not added automatically to menu items - you need to add it manually to static::menu()
     * @return array
     */
    static public function getApiDocsMenuItem(): array {
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
    static public function custom_dictionary_name(): string {
        return static::config('dictionary', 'cmf::custom');
    }

    /**
     * Translate from custom dictionary. You can use it via CmfConfig::transCustom() insetad of
     * CmfConfig::getPrimary()->transCustom() if you need to get translation for primary config.
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::custom' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string|array
     */
    static public function transCustom($path, array $parameters = [], $locale = null) {
        $dict = static::getInstance()->custom_dictionary_name();
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
    static public function cmf_general_dictionary_name(): string {
        return 'cmf::cmf';
    }

    /**
     * Translate from custom dictionary. You can use it via CmfConfig::transGeneral() insetad of
     * CmfConfig::getPrimary()->transGeneral() if you need to get translation for primary config
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::cmf' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param null|string $locale
     * @return string|array
     */
    static public function transGeneral($path, array $parameters = [], $locale = null) {
        $dict = static::getInstance()->cmf_general_dictionary_name();
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
     * Translations for Api Docs
     * @param string $translationPath
     * @param array $parameters
     * @param null $locale
     * @return string|array
     */
    static public function transApiDoc(string $translationPath, array $parameters = [], $locale = null) {
        if (static::class === self::class) {
            // redirect CmfConfig::transApiDoc() calls to primary config class
            return self::getPrimary()->transApiDoc($translationPath, $parameters, $locale);
        } else {
            $translationPath = 'api_docs.' . ltrim($translationPath, '.');
            return static::transCustom($translationPath, $parameters, $locale);
        }
    }

    /**
     * Default CMF language
     * @return string
     */
    static public function default_locale(): string {
        return static::config('locale', 'en');
    }

    /**
     * Supported locales for CMF
     * @return array - each value is locale code (usually 2 chars: en, ru; rarely - 5 chars: ru_RU, en_US)
     */
    static public function locales(): array {
        return array_unique(array_values(static::config('locales', ['en'])));
    }

    /**
     * Supported locales for CMF
     * Note: you can redirect locales using key as locale to redirect from and value as locale to redirect to
     * For details see: https://github.com/vluzrmos/laravel-language-detector
     * @return array
     */
    static public function locales_for_language_detector(): array {
        return (array)static::config('locales', ['en']);
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
     * @return string - 2 chars lowercases
     */
    static public function getShortLocale(): string {
        return strtolower(substr(app()->getLocale(), 0, 2));
    }

    static protected $localeSuffixMap = [
        'en' => 'US',
        'ko' => 'KR',
        'ja' => 'JP',
        'cs' => 'CZ',
        'da' => 'DK',
        'et' => 'EE',
        'fa' => 'IR',
        'kh' => 'KM',
        'nb' => 'NO',
        'pt' => 'BR',
        'sv' => 'SE',
        'vi' => 'VN',
        'zh' => 'CN',
    ];
    /**
     * Get locale in format "en_US" or "ru-RU" or "it-it"
     * @param string $separator
     * @param bool $lowercased - true: will return "it-it" instead of "it-IT"
     * @return string
     */
    static public function getLocaleWithSuffix($separator = '_', $lowercased = false): ?string {
        $locale = preg_split('%[-_]%', strtolower(app()->getLocale()));
        if (count($locale) === 2) {
            return $locale[0] . $separator . ($lowercased ? $locale[1] : strtoupper($locale[1]));
        } else {
            $localeSuffix = isset(static::$localeSuffixMap[$locale[0]]) ? static::$localeSuffixMap[$locale[0]] : $locale[0];
            return $locale[0] . $separator . ($lowercased ? $localeSuffix : strtoupper($localeSuffix));
        }
    }

    /**
     * Reset locale to default
     */
    static public function resetLocale() {
        static::setLocale(\LanguageDetector::getDriver()->detect());
    }

    /**
     * @return string
     */
    static public function session_message_key(): string {
        return static::makeUtilityKey('message');
    }

    /**
     * Start page URL of CMS section
     *
     * @param bool $absolute
     * @return string
     */
    static public function home_page_url($absolute = false): string {
        return route(static::getRouteName('cmf_start_page'), [], $absolute);
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    static public function rows_per_page(): int {
        return 25;
    }

    /**
     * Logo image to display in sidebar
     * @return string
     */
    static public function sidebar_logo(): string {
        return static::config('sidebar_logo') ?: '<img src="/packages/cmf/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';
    }

    /**
     * Additional configs for jQuery Data Tables lib
     * @return array
     */
    static public function data_tables_config(): array {
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
    static public function ckeditor_config(): array {
        return [
            'language' => static::getShortLocale(),
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
    static public function css_files_for_wysiwyg_editor(): array {
        return [];
    }

    /**
     * JS application settings (accessed via CmfSettings global variable)
     * @return array
     */
    static public function js_app_settings(): array {
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
    static public function js_app_data(): array {
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
    static public function getScaffoldConfig($resourceName): ScaffoldConfig {
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
    static public function getRegisteredScaffolds(): array {
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
     * @throws \Symfony\Component\Debug\Exception\ClassNotFoundException
     */
    static public function getTableByUnderscoredName($tableName): TableInterface {
        if (!array_key_exists($tableName, static::getInstance()->tables)) {
            if (array_key_exists($tableName, static::getInstance()->resources)) {
                /** @var ScaffoldConfigInterface $scaffoldConfigClass */
                $scaffoldConfigClass = static::getInstance()->resources[$tableName];
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
     * @return ClassBuilder|string
     */
    static protected function getDbClassesBuilderClass(): string {
        return config('peskyorm.class_builder');
    }

    private $dbClassesNamespace;
    /**
     * @param string $tableName
     * @return string
     * @throws \ReflectionException
     */
    static protected function getDbClassesNamespaceForTable($tableName): string {
        if (static::getInstance()->dbClassesNamespace === null) {
            static::getInstance()->dbClassesNamespace = rtrim(config('peskyorm.classes_namespace', 'App\\Db'), '\\') . '\\';
        }
        return static::getInstance()->dbClassesNamespace . StringUtils::classify($tableName);
    }

    private $currentResourceName;
    /**
     * @return string|null
     * @throws \UnexpectedValueException
     */
    static public function getResourceNameFromCurrentRoute(): ?string {
        if (static::getInstance()->currentResourceName === null) {
            static::getInstance()->currentResourceName = request()->route()->parameter('table_name');
        }
        return static::getInstance()->currentResourceName;
    }

    /**
     * Data inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createDataInsertConfig() and WysiwygFormInput::createDataInsertConfigWithArguments()
     * to create valid config
     * @param ScaffoldConfig $scaffold
     * @return array
     */
    static public function getAdditionalWysywygDataInsertsForCmsPages(ScaffoldConfig $scaffold): array {
        return [];
    }

    /**
     * Html inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createHtmlInsertConfig('<html>', 'menu title') to create valid config
     * @param ScaffoldConfig $scaffold
     * @return array
     */
    static public function getWysywygHtmlInsertsForCmsPages(ScaffoldConfig $scaffold): array {
        return [];
    }

    /**
     * Provides sections with list of objects of classes that extend CmfApiMethodDocumentation class to be displayed in api docs section
     * @return array - key - section name, value - array that contains names of classes that extend CmfApiDocumentation class
     */
    static public function getApiDocumentationClasses(): array {
        $classNames = static::config('api_documentation.classes', []);
        if (empty($classNames)) {
            $classNames = static::loadApiDocumentationClassesFromFileSystem();
        }
        return $classNames;
    }

    /**
     * Load api dosc sections from files in static::api_methods_documentation_classes_folder() and its subfolders.
     * Should be used only when static::config('api_docs_class_names') not provided.
     * Subfolders names used as API sections.
     * Collects only classes that extend next classes:
     *  - ApiDocumentation
     *  - ApiMethodDocumentation
     *  - static::api_method_documentation_base_class()
     * @return array
     */
    static protected function loadApiDocumentationClassesFromFileSystem(): array {
        $folder = static::api_documentation_classes_folder();
        if (!Folder::exist()) {
            return [];
        }
        $ret = [];
        $classFinder = function ($folderPath, array $files) {
            $classes = [];
            foreach ($files as $fileName) {
                if (preg_match('%\.php$%i', $fileName)) {
                    $file = fopen($folderPath . DIRECTORY_SEPARATOR . $fileName, 'rb');
                    $buffer = fread($file, 512);
                    $parentClassName = class_basename(static::api_method_documentation_base_class()) . '|[a-zA-Z0-9_-]+ApiMethodDocumentation|CmfApiDocumentation';
                    if (preg_match('%^\s*class\s+(\w+)\s+extends\s+(' . $parentClassName . ')%im', $buffer, $classMatches)) {
                        $class = $classMatches[1];
                        if (preg_match("%[^w]namespace\s+([\w\\\]+).*?class\s+{$class}\s+%is", $buffer, $nsMatches)) {
                            $namespace = $nsMatches[1];
                            $classes[] = '\\' . $namespace . '\\' . $class;
                        }
                    }
                }
            }
            // sort classes
            usort($classes, function ($class1, $class2) {
                /** @var CmfApiDocumentation $class1 */
                /** @var CmfApiDocumentation $class2 */
                $pos1 = $class1::getPosition();
                $pos2 = $class2::getPosition();
                if ($pos1 === null) {
                    return $pos2 === null ? 0 : 1;
                } else if ($pos2 === null) {
                    return $pos1 === null ? 0 : -1;
                } else if ($pos1 === $pos2) {
                    return 0;
                } else {
                    return $pos1 > $pos2;
                }
            });
            return $classes;
        };
        $folder = Folder::load($folder);
        list($subFolders, $files) = $folder->read();
        $withoutSection = $classFinder($folder->pwd(), $files);
        if (!empty($withoutSection)) {
            $ret[(string)static::transCustom('api_docs.section.no_section')] = $withoutSection;
        }
        foreach ($subFolders as $subFolderName) {
            if ($subFolderName[0] === '.') {
                // ignore folders starting with '.' - nothing useful there
                continue;
            }
            $subFolder = Folder::load($folder->pwd() . DIRECTORY_SEPARATOR . $subFolderName);
            $files = $subFolder->find('.*\.php');
            $classes = $classFinder($subFolder->pwd(), $files);
            if (!empty($classes)) {
                $ret[(string)static::transApiDoc('section.' . snake_case($subFolderName))] = $classes;
            }
        }
        return $ret;
    }

    /**
     * @return string
     */
    static public function api_documentation_classes_folder(): string {
        return static::config('api_documentation.folder') ?: app_path('Api/Docs');
    }

    /**
     * @return string
     */
    static public function api_method_documentation_base_class(): string {
        return static::config('api_documentation.base_class_for_method') ?: CmfApiMethodDocumentation::class;
    }

    /**
     * @return string
     */
    static public function api_documentation_class_name_suffix(): string {
        return static::config('api_documentation.class_suffix', 'Documentation');
    }

    protected $httpRequestsLogger;

    /**
     * @return null|ScaffoldLoggerInterface;
     */
    static public function getHttpRequestsLogger(): ?ScaffoldLoggerInterface {
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
    static public function getCachedPagesTemplates(): array {
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
    static public function getCachedResourcesTemplates(): array {
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
    static protected function collectResourcesTemplatesToBeCached(): array {
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
    static protected function getCacheKeyForOptimizedUiTemplates($group): string {
        return static::getCacheKeyForOptimizedUiTemplatesBasedOnUserId($group);
    }

    /**
     * User-id-based cache key
     * @param string $group
     * @return string
     */
    static protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserId($group): string {
        if (static::getAuthModule()->getAccessPolicyClassName() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $user = static::getUser();
            $userId = $user ? $user->getAuthIdentifier() : 'not_authenticated';
        }
        return static::url_prefix() . '_templates_' . static::getShortLocale() . '_' . $group . '_user_' . $userId;
    }

    /**
     * Role-based cache key
     * @param string $group
     * @return string
     */
    static protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserRole($group): string {
        if (static::getAuthModule()->getAccessPolicyClassName() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $userId = 'not_authenticated';
            $user = static::getUser();
            if ($user && $user->existsInDb()) {
                if ($user::hasColumn('is_superadmin')) {
                    $userId = '__superadmin__';
                } else if ($user::hasColumn('role')) {
                    $userId = $user->role;
                } else {
                    $userId = 'user';
                }
            }
        }
        return static::url_prefix() . '_templates_' . static::getShortLocale() . '_' . $group . '_user_' . $userId;
    }

    /**
     * @param $keySuffix
     * @return string
     */
    static public function makeUtilityKey($keySuffix): string {
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', static::url_prefix()) . '_' . $keySuffix;
    }

    /**
     * @param string $sectionName - cmf section name
     */
    public function declareRoutes($sectionName) {
        $cmfConfig = $this;
        $groupConfig = [
            'prefix' => static::url_prefix(),
            'middleware' => (array)static::config('routes_middleware', ['web']),
        ];
        $cmfSectionSelectorMiddleware = $this->getUseCmfSectionMiddleware($sectionName);
        array_unshift($groupConfig['middleware'], $cmfSectionSelectorMiddleware);
        $namespace = static::config('controllers_namespace');
        if (!empty($namespace)) {
            $groupConfig['namespace'] = ltrim($namespace, '\\');
        }
        // custom routes
        $files = (array)static::config('routes_files', []);
        if (count($files) > 0) {
            foreach ($files as $filePath) {
                \Route::group($groupConfig, function () use ($filePath, $cmfConfig) {
                    // warning! $cmfConfig may be used inside included file
                    include base_path($filePath);
                });
            }
        }

        unset($groupConfig['namespace']); //< cmf routes should be able to use controllers from vendors dir
        if (!\Route::has(static::getRouteName('cmf_start_page'))) {
            \Route::group($groupConfig, function () {
                \Route::get('/', [
                    'uses' => static::cmf_general_controller_class() . '@redirectToUserProfile',
                    'as' => static::getRouteName('cmf_start_page'),
                ]);
            });
        }

        \Route::group($groupConfig, function () use ($cmfConfig) {
            // warning! $cmfConfig may be used inside included file
            include __DIR__ . '/peskycmf.routes.php';
        });

        // special route for ckeditor config.js file
        $groupConfig['middleware'] = [$cmfSectionSelectorMiddleware]; //< only 1 needed
        \Route::group($groupConfig, function () use ($cmfConfig) {
            \Route::get('ckeditor/config.js', [
                'as' => $cmfConfig::routes_names_prefix() . 'cmf_ckeditor_config_js',
                'uses' => $cmfConfig::cmf_general_controller_class() . '@getCkeditorConfigJs',
            ]);
        });
    }

    /**
     * Get middleware that will tell system whick CMF section to use
     * @param string $sectionName
     * @return string
     */
    protected function getUseCmfSectionMiddleware($sectionName): string {
        return UseCmfSection::class . ':' . $sectionName;
    }

    /**
     * @param Application $app
     */
    public function updateAppConfigs($app) {
        /** @var \Illuminate\Config\Repository $appConfigs */
        $appConfigs = $app['config'];
        // add auth guard but do not select it as primary
        $this->addAuthGuardConfigToAppConfigs($appConfigs);
    }

    /**
     * @param \Illuminate\Config\Repository $appConfigs
     */
    protected function addAuthGuardConfigToAppConfigs($appConfigs) {
        // merge cmf guard and provider with configs in config/auth.php
        $cmfAuthConfig = static::config('auth.guard');
        if (!is_array($cmfAuthConfig)) {
            // custom auth guard name provided
            return;
        }

        $config = $appConfigs->get('auth', [
            'guards' => [],
            'providers' => [],
        ]);

        $guardName = array_get($cmfAuthConfig, 'name') ?: static::url_prefix();
        if (array_key_exists($guardName, $config['guards'])) {
            throw new \UnexpectedValueException('There is already an auth guard with name "' . $guardName . '"');
        }
        $provider = array_get($cmfAuthConfig, 'provider');
        if (is_array($provider)) {
            $providerName = array_get($provider, 'name', $guardName);
            if (empty($provider['model'])) {
                $provider['model'] = static::config('auth.user_record_class', function () {
                    throw new \UnexpectedValueException('You need to provide a DB Record class for users');
                });
            }
        } else {
            $providerName = $provider;
            $provider = null;
        }
        if (array_key_exists($providerName, $config['providers'])) {
            throw new \UnexpectedValueException('There is already an auth provider with name "' . $guardName . '"');
        }
        $config['guards'][$guardName] = [
            'driver' => array_get($cmfAuthConfig, 'driver', 'session'),
            'provider' => $providerName,
        ];
        if (!empty($provider)) {
            $config['providers'][$providerName] = $provider;
        }

        $appConfigs->set('auth', $config);
    }

    /**
     * @param Application $app
     */
    public function initSection($app) {
        $this->useAsPrimary();

        // init auth module
        /** @var CmfAuthModule $cmfAuthModuleClass */
        $cmfAuthModuleClass = static::config('auth.module');
        /** @var CmfAuthModule $authModule */
        $authModule = new $cmfAuthModuleClass($this);
        $app->singleton(CmfAuthModule::class, function () use ($authModule) {
            return $authModule;
        });
        $authModule->init();

        /** @var PeskyCmfLanguageDetectorServiceProvider $langDetectorProvider */
        $langDetectorProvider = $app->register(PeskyCmfLanguageDetectorServiceProvider::class);
        $app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        $langDetectorProvider->importConfigsFromPeskyCmf($this);

        // configure session
        $this->configureSession($app);

        if (static::config('file_access_mask') !== null) {
            umask(static::config('file_access_mask'));
        }
        $this->registerScaffoldConfigsFromConfigFile();
    }

    /**
     * @param Application $app
     */
    protected function configureSession($app) {
        /** @var \Illuminate\Config\Repository $appConfigs */
        $appConfigs = $app['config'];
        $config = $appConfigs->get('session', []);
        $config['path'] = '/' . trim(static::url_prefix(), '/');
        $appConfigs->set('session', array_merge($config, (array)static::config('session', [])));
    }

    /**
     * Register resource name to ScaffoldConfig class mappings
     * @throws \UnexpectedValueException
     */
    protected function registerScaffoldConfigsFromConfigFile() {
        /** @var ScaffoldConfig[] $resources */
        $resources = (array)static::config('resources', []);
        foreach ($resources as $scaffoldConfig) {
            static::registerScaffoldConfigForResource($scaffoldConfig::getResourceName(), $scaffoldConfig);
        }
    }

}