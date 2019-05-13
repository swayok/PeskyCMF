<?php

namespace PeskyCMF\Config;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Application;
use Illuminate\View\View;
use PeskyCMF\ApiDocs\CmfApiDocumentationModule;
use PeskyCMF\Auth\CmfAccessPolicy;
use PeskyCMF\Auth\CmfAuthModule;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\Http\Middleware\UseCmfSection;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Providers\PeskyCmfLanguageDetectorServiceProvider;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyCMF\Scaffold\ScaffoldLoggerInterface;
use PeskyCMF\UI\CmfUIModule;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;
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
    static public function getAppSettings() {
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
    static public function getUser(): ?RecordInterface {
        return static::getAuthModule()->getUser();
    }

    /**
     * @return CmfAuthModule
     */
    static public function getAuthModule(): CmfAuthModule {
        return app(CmfAuthModule::class);
    }

    /**
     * @return CmfUIModule
     */
    static public function getUiModule(): CmfUIModule {
        return app(CmfUIModule::class);
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

    static public function recaptcha_script(): string {
        return 'https://www.google.com/recaptcha/api.js?hl=' . static::getShortLocale();
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
        return static::url_prefix() . '/';
    }

    /**
     * @param string $routeAlias
     * @return string
     */
    static public function getRouteName($routeAlias): string {
        return static::routes_names_prefix() . $routeAlias;
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    static public function route(string $routeName, array $parameters = [], bool $absolute = false): string {
        return route(static::getRouteName($routeName), $parameters, $absolute);
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
     * The menu structure of the site.
     * @return array
     * You may use static::getMenuItem($resourceName) or static::getMenuItems() to get
     * menu items from registered scaffold configs.
     * Todo: add CmfSidebarMenuItem class to help designing menu items
     * Menu item format:
     *    [
     *         [
     *              'label' => 'label',
     *              'url' => '/url',
     *              'icon' => 'icon',
     *         ],
     *         [
     *              'label' => 'label',
     *              'icon' => 'icon',
     *              'open' => false,
     *              'submenu' => [...]
     *         ],
     *    ]
     * Available options:
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
        return static::getUiModule()->getValuesForMenuItemsCounters();
    }

    /**
     * @param string $itemKey
     * @param array|\Closure $menuItem - format: see menu()
     */
    static public function addMenuItem(string $itemKey, $menuItem) {
        static::getUiModule()->addCustomMenuItem($itemKey, $menuItem);
    }

    /**
     * @param array $itemsKeys - list of keys to return values for, if empty - will return all menu items
     * @return array
     */
    static protected function getMenuItems(...$itemsKeys): array {
        $menuItems = static::getUiModule()->getMenuItems();
        if (empty($itemsKeys)) {
            return $menuItems;
        } else {
            return array_intersect_key($menuItems, array_flip($itemsKeys));
        }
    }

    /**
     * @param array $excludeItemsWithKeys - list of keys to exclude from resulting menu items array
     * @return array
     */
    static protected function getMenuItemsExcept(...$excludeItemsWithKeys): array {
        if (empty($excludeItemsWithKeys)) {
            return static::getInstance()->getMenuItems();
        } else {
            return array_diff_key(static::getInstance()->getMenuItems(), array_flip($excludeItemsWithKeys));
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
        return static::route('cmf_start_page', [], $absolute);
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    static public function rows_per_page(): int {
        return 25;
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
            'filebrowserImageUploadUrl' => static::route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]),
            'uploadUrl' => static::route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]),
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
     * Get ScaffoldConfig instance
     * @param string $resourceName
     * @return ScaffoldConfig|ScaffoldConfigInterface
     * @throws \InvalidArgumentException
     */
    static public function getScaffoldConfig(string $resourceName): ScaffoldConfigInterface {
        return static::getUiModule()->getScaffoldConfig($resourceName);
    }

    /**
     * Get ScaffoldConfig class name
     * @param string $resourceName
     * @return string
     * @throws \InvalidArgumentException
     */
    static public function getScaffoldConfigClass(string $resourceName): string {
        return static::getUiModule()->getScaffoldConfigClass($resourceName);
    }

    /**
     * Get TableInterface instance for $tableName
     * Note: can be ovewritted to allow usage of fake tables in resources routes
     * It is possible to use this with static::getScaffoldConfig() to alter default scaffold configs
     * @param string $resourceName
     * @return TableInterface
     */
    static public function getTableByResourceName(string $resourceName): TableInterface {
        return static::getUiModule()->getTableByResourceName($resourceName);
    }

    static public function getApiDocumentationModule(): CmfApiDocumentationModule {
        return app(CmfApiDocumentationModule::class);
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
            (int)static::config('ui.optimize_ui_templates.timeout', 0),
            function () {
                $generalControllerClass = static::cmf_general_controller_class();
                /** @var \PeskyCMF\Http\Controllers\CmfGeneralController $controller */
                $controller = new $generalControllerClass();
                if (static::getUser()) {
                    return [
                        static::route('cmf_main_ui', [], false) => $controller->getBasicUiView(),
                    ];
                } else {
                    return [
                        static::route('cmf_login', [], false) . '.html' => $controller->getLoginTpl(),
                        static::route('cmf_forgot_password', [], false) . '.html' => $controller->getForgotPasswordTpl(),
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
            (int)static::config('ui.optimize_ui_templates.timeout', 0),
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
        foreach (static::getUiModule()->getResources() as $resourceName => $scaffoldConfigClass) {
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
        \Route::group($groupConfig, function () {
            \Route::get('ckeditor/config.js', [
                'uses' => static::cmf_general_controller_class() . '@getCkeditorConfigJs',
                'as' => static::getRouteName('cmf_ckeditor_config_js'),
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
        $cmfAuthModuleClass = static::config('auth.module') ?: CmfAuthModule::class;
        /** @var CmfAuthModule $authModule */
        $authModule = new $cmfAuthModuleClass($this);
        $app->singleton(CmfAuthModule::class, function () use ($authModule) {
            return $authModule;
        });
        $authModule->init();

        // init UI module
        /** @var CmfAuthModule $cmfAuthModuleClass */
        $cmfUIModuleClass = static::config('ui.module') ?: CmfUIModule::class;
        /** @var CmfAuthModule $authModule */
        $uiModule = new $cmfUIModuleClass($this);
        $app->singleton(CmfUIModule::class, function () use ($uiModule) {
            return $uiModule;
        });

        // init API Documentation module
        $app->singleton(CmfApiDocumentationModule::class, function () {
            $cmfApiDocsModuleClass = static::config('api_documentation.module') ?: CmfApiDocumentationModule::class;
            return new $cmfApiDocsModuleClass($this);
        });

        // send $cmfConfig and $uiModule var to all views
        \View::composer('*', function (View $view) use ($uiModule) {
            $view
                ->with('cmfConfig', $this)
                ->with('uiModule', $uiModule);
        });

        /** @var PeskyCmfLanguageDetectorServiceProvider $langDetectorProvider */
        $langDetectorProvider = $app->register(PeskyCmfLanguageDetectorServiceProvider::class);
        $app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        $langDetectorProvider->importConfigsFromPeskyCmf($this);

        // configure session
        $this->configureSession($app);

        if (static::config('file_access_mask') !== null) {
            umask(static::config('file_access_mask'));
        }

        // Register resource name to ScaffoldConfig class mappings
        //$resources = static::getUiModule()->getScaffolds();
        /** @var ScaffoldConfig $scaffoldConfigClass */
        /*foreach ($resources as $scaffoldConfigClass) {
            static::registerScaffoldConfigForResource($scaffoldConfigClass::getResourceName(), $scaffoldConfigClass);
        }*/
    }

    /**
     * @param Application $app
     */
    protected function configureSession($app) {
        /** @var \Illuminate\Config\Repository $appConfigs */
        $appConfigs = $app['config'];
        $config = array_merge(
            $appConfigs->get('session'),
            ['path' => '/' . trim(static::url_prefix(), '/')],
            (array)static::config('session', [])
        );
        $appConfigs->set('session', $config);
    }

}