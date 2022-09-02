<?php

declare(strict_types=1);

namespace PeskyCMF\Config;

use Illuminate\Auth\SessionGuard;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Factory as ViewsFactory;
use Illuminate\View\View;
use PeskyCMF\ApiDocs\CmfApiDocumentationModule;
use PeskyCMF\Auth\CmfAccessPolicy;
use PeskyCMF\Auth\CmfAuthModule;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\CmfUrl;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\Contracts\ResetsPasswordsViaAccessKey;
use PeskyCMF\Http\Controllers\CmfGeneralController;
use PeskyCMF\Http\Controllers\CmfScaffoldApiController;
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
use Vluzrmos\LanguageDetector\Contracts\LanguageDetectorInterface;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

abstract class CmfConfig
{
    
    protected Application $app;
    protected ConfigRepository $configs;
    protected LanguageDetectorInterface $languageDetector;
    protected ViewsFactory $viewsFactory;
    protected ?ScaffoldLoggerInterface $httpRequestsLogger = null;
    
    protected static array $localeSuffixMap = [
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
    
    public function __construct()
    {
    }
    
    public function getLaravelApp(): Application
    {
        return $this->app;
    }
    
    public function getLanguageDetector(): LanguageDetectorInterface
    {
        return $this->languageDetector;
    }
    
    public function getLaravelConfigs(): ConfigRepository
    {
        return $this->configs;
    }
    
    public function getViewsFactory(): ViewsFactory
    {
        return $this->viewsFactory;
    }
    
    /**
     * File name for this site section in 'configs' folder of project's root directory (without '.php' extension)
     * Example: 'admin' for config/admin.php;
     */
    protected function configsFileName(): string
    {
        return 'peskycmf';
    }
    
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->getLaravelConfigs()->get($this->configsFileName() . '.' . $key, $default);
    }
    
    public function routes_files(): array
    {
        return (array)$this->config('routes_files', []);
    }
    
    final public function cmf_views_dir(): string
    {
        return __DIR__ . '/../resources/views';
    }
    
    public function language_detector_configs(): array
    {
        return [
            'autodetect' => true,
            'driver' => 'browser',
            'cookie' => true,
            'cookie_name' => $this->makeUtilityKey('locale'),
            'cookie_encrypt' => true,
            'languages' => $this->locales_for_language_detector(),
        ];
    }
    
    public function getAppSettings(): PeskyCmfAppSettings
    {
        return $this->getLaravelApp()->make(PeskyCmfAppSettings::class);
    }
    
    /**
     * @return Guard|StatefulGuard|SessionGuard
     * @noinspection PhpDocSignatureInspection
     */
    public function getAuthGuard(): Guard
    {
        return $this->getAuthModule()->getAuthGuard();
    }
    
    /**
     * @return CmfAdmin|Authenticatable|ResetsPasswordsViaAccessKey|RecordInterface|null
     * @noinspection PhpDocSignatureInspection
     */
    public function getUser(): ?RecordInterface
    {
        return $this->getAuthModule()->getUser();
    }
    
    public function getAuthModule(): CmfAuthModule
    {
        return $this->getLaravelApp()->make(CmfAuthModule::class);
    }
    
    public function getUiModule(): CmfUIModule
    {
        return $this->getLaravelApp()->make(CmfUIModule::class);
    }
    
    /**
     * Email address used in "From" header for emails sent to users
     */
    public function system_email_address(): string
    {
        return $this->getLaravelConfigs()->get('system_email_address', function () {
            /** @var Request $request */
            $request = $this->getLaravelApp()->make('request');
            return 'noreply@' . $request->getHost();
        });
    }
    
    /**
     * Url prefix for routes
     */
    public function url_prefix(): string
    {
        return $this->config('url_prefix', 'admin');
    }
    
    /**
     * Cmf classes subfolder in application
     */
    public function app_subfolder(): string
    {
        return $this->config('app_subfolder', 'Admin');
    }
    
    public function recaptcha_private_key(): ?string
    {
        return $this->getLaravelConfigs()->get('services.recaptcha.private_key');
    }
    
    public function recaptcha_public_key(): ?string
    {
        return $this->getLaravelConfigs()->get('services.recaptcha.public_key');
    }
    
    public function recaptcha_script(): string
    {
        return 'https://www.google.com/recaptcha/api.js?hl=' . $this->getShortLocale();
    }
    
    public function default_page_title(): string
    {
        return $this->getAppSettings()->default_browser_title(function () {
            return $this->transCustom('.default_page_title');
        }, true);
    }
    
    public function page_title_addition(): string
    {
        return $this->getAppSettings()->browser_title_addition(function () {
            return $this->default_page_title();
        }, true);
    }
    
    /**
     * Controller class name for CMF scaffolds API
     */
    public function cmf_scaffold_api_controller_class(): string
    {
        return CmfScaffoldApiController::class;
    }
    
    /**
     * General controller class name for CMF (basic ui views, custom pages views, login/logout, etc.)
     */
    public function cmf_general_controller_class(): string
    {
        return CmfGeneralController::class;
    }
    
    /**
     * Prefix for route names in peskycmf.routes.php
     * Use with caution and only when you really know what you're doing
     */
    public function routes_names_prefix(): string
    {
        return $this->url_prefix() . '/';
    }
    
    public function getRouteName(string $routeAlias): string
    {
        return $this->routes_names_prefix() . $routeAlias;
    }
    
    public function route(string $routeName, array $parameters = [], bool $absolute = false): string
    {
        return route($this->getRouteName($routeName), $parameters, $absolute);
    }
    
    /**
     * Note: placed here to avoid problems with auth module constructor when registering routes for all cmf sections
     */
    public function auth_middleware(): array
    {
        return (array)$this->config('auth.middleware', [CmfAuth::class]);
    }
    
    /**
     * Basic set of middlewares for scaffold api controller
     */
    public function middleware_for_cmf_scaffold_api_controller(): array
    {
        return [];
    }
    
    /**
     * The menu structure of the site.
     * @return array
     * You may use $this->getMenuItem($resourceName) or $this->getMenuItems() to get
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
    public function menu(): array
    {
        return array_merge(
            [
                [
                    'label' => $this->transCustom('.page.dashboard.menu_title'),
                    'url' => CmfUrl::toPage('dashboard', [], false, $this),
                    'icon' => 'glyphicon glyphicon-dashboard',
                ],
                /*[
                    'label' => $this->transCustom('.users.menu_title'),
                    'url' => '/resource/users',
                    'icon' => 'fa fa-group'
                ],*/
                /*[
                    'label' => $this->transCustom('.menu.section_utils'),
                    'icon' => 'glyphicon glyphicon-align-justify',
                    'submenu' => [
                        [
                            'label' => $this->transCustom('.admins.menu_title'),
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
            $this->getMenuItems()
        );
    }
    
    /**
     * Get values for menu items counters (details in CmfConfig::menu())
     * @return array like ['pending_orders' => '<span class="label label-primary pull-right">2</span>']
     */
    public function getValuesForMenuItemsCounters(): array
    {
        return $this->getUiModule()->getValuesForMenuItemsCounters();
    }
    
    /**
     * For $menuItem format see docs for menu()
     */
    public function addMenuItem(string $itemKey, array|\Closure $menuItem): void
    {
        $this->getUiModule()->addCustomMenuItem($itemKey, $menuItem);
    }
    
    /**
     * @param array $itemsKeys - list of keys to return values for, if empty - will return all menu items
     * @return array
     */
    protected function getMenuItems(...$itemsKeys): array
    {
        $menuItems = $this->getUiModule()->getMenuItems();
        if (empty($itemsKeys)) {
            return $menuItems;
        } else {
            return array_intersect_key($menuItems, array_flip($itemsKeys));
        }
    }
    
    /**
     * @param array $excludeItemsWithKeys - list of keys to exclude from resulting menu items array
     * @return array
     * @noinspection PhpUnused
     */
    protected function getMenuItemsExcept(...$excludeItemsWithKeys): array
    {
        if (empty($excludeItemsWithKeys)) {
            return $this->getMenuItems();
        } else {
            return array_diff_key($this->getMenuItems(), array_flip($excludeItemsWithKeys));
        }
    }
    
    /**
     * Get menu item config or null if there is no such menu item
     * @noinspection PhpUnused
     */
    protected function getMenuItem(string $resourceName): ?array
    {
        return Arr::get($this->getMenuItems(), $resourceName);
    }
    
    /**
     * Name for custom CMF dictionary that contains translation for CMF resource sections and pages
     */
    public function custom_dictionary_name(): string
    {
        return $this->config('dictionary', 'cmf::custom');
    }
    
    /**
     * Translate from custom dictionary. You can use it via CmfConfig::transCustom() insetad of
     * CmfConfig::getPrimary()->transCustom() if you need to get translation for primary config.
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::custom' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param string|null $locale
     * @return string|array
     */
    public function transCustom(string $path, array $parameters = [], ?string $locale = null): array|string
    {
        $dict = $this->custom_dictionary_name();
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
     */
    public function cmf_general_dictionary_name(): string
    {
        return 'cmf::cmf';
    }
    
    /**
     * Translate from custom dictionary. You can use it via CmfConfig::transGeneral() insetad of
     * CmfConfig::getPrimary()->transGeneral() if you need to get translation for primary config
     * Note: if there is no translation in your dictionary - it will be imported from 'cmf::cmf' dictionary
     * @param string $path - without dictionary name. Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array $parameters
     * @param string|null $locale
     * @return string|array
     */
    public function transGeneral(string $path, array $parameters = [], ?string $locale = null): array|string
    {
        $dict = $this->cmf_general_dictionary_name();
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
     */
    public function transApiDoc(string $translationPath, array $parameters = [], ?string $locale = null): array|string
    {
        $translationPath = 'api_docs.' . ltrim($translationPath, '.');
        return $this->transCustom($translationPath, $parameters, $locale);
    }
    
    /**
     * Default CMF language
     */
    public function default_locale(): string
    {
        return $this->config('locale', 'en');
    }
    
    /**
     * Supported locales for CMF
     * @return array - each value is locale code (usually 2 chars: en, ru; rarely - 5 chars: ru_RU, en_US)
     */
    public function locales(): array
    {
        return array_unique(array_values($this->config('locales', ['en'])));
    }
    
    /**
     * Supported locales for CMF
     * Note: you can redirect locales using key as locale to redirect from and value as locale to redirect to
     * For details see: https://github.com/vluzrmos/laravel-language-detector
     */
    public function locales_for_language_detector(): array
    {
        return (array)$this->config('locales', ['en']);
    }
    
    /**
     * Change locale inside CMF/CMS area
     */
    public function setLocale(string $locale): void
    {
        $this->getLaravelApp()->setLocale($locale);
    }
    
    /**
     * 2 chars in lowercase
     */
    public function getShortLocale(): string
    {
        return strtolower(substr($this->getLaravelApp()->getLocale(), 0, 2));
    }
    
    /** @noinspection PhpUnusedParameterInspection */
    public function onLocaleChanged(string $newLocale): void
    {
        Column::setValidationErrorsMessages((array)$this->transGeneral('form.message.column_validation_errors') ?: []);
    }
    
    /**
     * Get locale in format "en_US" or "ru-RU" or "it-it"
     * @param string $separator
     * @param bool $lowercased - true: will return "it-it" instead of "it-IT"
     * @return string
     */
    public function getLocaleWithSuffix(string $separator = '_', bool $lowercased = false): string
    {
        $locale = preg_split('%[-_]%', strtolower($this->getLaravelApp()->getLocale()));
        if (count($locale) === 2) {
            return $locale[0] . $separator . ($lowercased ? $locale[1] : strtoupper($locale[1]));
        } else {
            $localeSuffix = static::$localeSuffixMap[$locale[0]] ?? $locale[0];
            return $locale[0] . $separator . ($lowercased ? $localeSuffix : strtoupper($localeSuffix));
        }
    }
    
    /**
     * Detect locale from browser or subdomain
     */
    public function detectLocale(): void
    {
        $this->getLaravelApp()->setLocale($this->getLanguageDetector()->getDriver()->detect());
    }
    
    public function session_message_key(): string
    {
        return $this->makeUtilityKey('message');
    }
    
    /**
     * Start page URL of CMS section
     */
    public function home_page_url(bool $absolute = false): string
    {
        return $this->route('cmf_start_page', [], $absolute);
    }
    
    /**
     * How much rows to display in data tables
     */
    public function rows_per_page(): int
    {
        return 25;
    }
    
    /**
     * Additional configs for jQuery Data Tables lib
     */
    public function data_tables_config(): array
    {
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
    public function ckeditor_config(): array
    {
        return [
            'language' => $this->getShortLocale(),
            'toolbarGroups' => [
                ['name' => 'clipboard', 'groups' => ['clipboard', 'undo']],
                ['name' => 'editing', 'groups' => ['find', 'selection', 'spellchecker', 'editing']],
                ['name' => 'links', 'groups' => ['links']],
                ['name' => 'insert', 'groups' => ['insert']],
                ['name' => 'forms', 'groups' => ['forms']],
                ['name' => 'tools', 'groups' => ['tools']],
                ['name' => 'document', 'groups' => ['mode', 'document', 'doctools']],
                ['name' => 'others', 'groups' => ['others']],
                ['name' => 'about', 'groups' => ['about']],
                '/',
                ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup']],
                ['name' => 'paragraph', 'groups' => ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']],
                ['name' => 'styles', 'groups' => ['styles']],
                ['name' => 'colors', 'groups' => ['colors']],
            ],
            'removeButtons' => 'Superscript,Find,Replace,SelectAll,Scayt,Flash,Smiley,PageBreak,Iframe,Form,Checkbox,'
                . 'Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Maximize,Save,NewPage,Preview,Print,'
                . 'Templates,Strike,Subscript,BidiLtr,BidiRtl,Language,Styles',
            'format_tags' => 'p;h1;h2;h3;pre',
            'enterMode' => 1, //< insert <p> on pressing ENTER
            'forceEnterMode' => true,
            'removeDialogTabs' => 'image:advanced',
            'extraPlugins' => 'uploadimage',
            'contentsCss' => $this->css_files_for_wysiwyg_editor(),
            // _token won't be working this way =(
            // moved to WysiwygFormInput->getWysiwygConfig()
            //'uploadUrl' => $this->route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]),
            //'filebrowserImageUploadUrl' => $this->route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]),
        ];
    }
    
    /**
     * Add some css files inside wysuwyg editor to allow custom styling while editing wysiwyg contents
     * @return array
     */
    public function css_files_for_wysiwyg_editor(): array
    {
        return [];
    }
    
    /**
     * Data inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createDataInsertConfig() and WysiwygFormInput::createDataInsertConfigWithArguments()
     * to create valid config
     * @noinspection PhpUnusedParameterInspection
     */
    public function getAdditionalWysywygDataInsertsForCmsPages(ScaffoldConfig $scaffold): array
    {
        return [];
    }
    
    /**
     * Html inserts for CmsPage-related scaffold configs to be added to ckeditor's plugin
     * Use WysiwygFormInput::createHtmlInsertConfig('<html>', 'menu title') to create valid config
     * @noinspection PhpUnusedParameterInspection
     */
    public function getWysywygHtmlInsertsForCmsPages(ScaffoldConfig $scaffold): array
    {
        return [];
    }
    
    /**
     * Get ScaffoldConfig instance by resource name
     */
    public function getScaffoldConfig(string $resourceName): ScaffoldConfigInterface
    {
        return $this->getUiModule()->getScaffoldConfig($resourceName);
    }
    
    /**
     * Get ScaffoldConfig class name by resource name
     */
    public function getScaffoldConfigClass(string $resourceName): string
    {
        return $this->getUiModule()->getScaffoldConfigClass($resourceName);
    }
    
    /**
     * Get TableInterface instance by resource name
     * Note: can be ovewritted to allow usage of fake tables in resources routes
     * It is possible to use this with static::getScaffoldConfig() to alter default scaffold configs
     */
    public function getTableByResourceName(string $resourceName): TableInterface
    {
        return $this->getUiModule()->getTableByResourceName($resourceName);
    }
    
    public function getApiDocumentationModule(): CmfApiDocumentationModule
    {
        return $this->getLaravelApp()->make(CmfApiDocumentationModule::class);
    }
    
    public function getHttpRequestsLogger(): ?ScaffoldLoggerInterface
    {
        if (!$this->httpRequestsLogger && $this->getLaravelApp()->bound(ScaffoldLoggerInterface::class)) {
            $this->setHttpRequestsLogger($this->getLaravelApp()->make(ScaffoldLoggerInterface::class));
        }
        return $this->httpRequestsLogger;
    }
    
    /**
     * Logger will be used to logs requested records pk and changes
     * @param ScaffoldLoggerInterface $httpRequestsLogger
     */
    public function setHttpRequestsLogger(ScaffoldLoggerInterface $httpRequestsLogger): void
    {
        $this->httpRequestsLogger = $httpRequestsLogger;
    }
    
    /**
     * @return array
     */
    public function getCachedPagesTemplates(): array
    {
        return Cache::remember(
            $this->getCacheKeyForOptimizedUiTemplates('pages'),
            (int)$this->config('ui.optimize_ui_templates.timeout', 0),
            function () {
                $generalControllerClass = $this->cmf_general_controller_class();
                /** @var CmfGeneralController $controller */
                $controller = new $generalControllerClass();
                if ($this->getUser()) {
                    return [
                        $this->getUiModule()->getUiUrl(false) => $controller->getBasicUiView(),
                    ];
                } else {
                    return [
                        $this->getAuthModule()->getLoginPageUrl(false) . '.html' => $controller->getLoginTpl(),
                        $this->getAuthModule()->getPasswordRecoveryStartPageUrl(false) . '.html' => $controller->getForgotPasswordTpl(),
                    ];
                }
            }
        );
    }
    
    public function getCachedResourcesTemplates(): array
    {
        if (!$this->getUser()) {
            return [];
        }
        return $this->getLaravelApp()->make('cache.store')
            ->remember(
                $this->getCacheKeyForOptimizedUiTemplates('resources'),
                (int)$this->config('ui.optimize_ui_templates.timeout', 0),
                function () {
                    return $this->collectResourcesTemplatesToBeCached();
                }
            );
    }
    
    protected function collectResourcesTemplatesToBeCached(): array
    {
        $resourceTemplates = [];
        /** @var ScaffoldConfig $scaffoldConfigClass */
        foreach ($this->getUiModule()->getResources() as $resourceName => $scaffoldConfigClass) {
            $scaffoldConfig = new $scaffoldConfigClass($this);
            $splitted = $scaffoldConfig->renderTemplatesAndSplit();
            if (!empty($splitted)) {
                $resourceTemplates[$resourceName] = $splitted;
            }
        }
        return $resourceTemplates;
    }
    
    /**
     * User-id-based cache key
     */
    protected function getCacheKeyForOptimizedUiTemplates(string $group): string
    {
        return $this->getCacheKeyForOptimizedUiTemplatesBasedOnUserId($group);
    }
    
    /**
     * User-id-based cache key
     */
    protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserId(string $group): string
    {
        if ($this->getAuthModule()->getAccessPolicyClassName() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $user = $this->getUser();
            $userId = $user ? $user->getAuthIdentifier() : 'not_authenticated';
        }
        return $this->url_prefix() . '_templates_' . $this->getShortLocale() . '_' . $group . '_user_' . $userId;
    }
    
    /**
     * Role-based cache key
     * @noinspection PhpUnused
     */
    protected function getCacheKeyForOptimizedUiTemplatesBasedOnUserRole(string $group): string
    {
        if ($this->getAuthModule()->getAccessPolicyClassName() === CmfAccessPolicy::class) {
            $userId = 'any';
        } else {
            $userId = 'not_authenticated';
            $user = $this->getUser();
            if ($user && $user->existsInDb()) {
                if ($user::hasColumn('is_superadmin')) {
                    $userId = '__superadmin__';
                } elseif ($user::hasColumn('role')) {
                    $userId = $user->role;
                } else {
                    $userId = 'user';
                }
            }
        }
        return $this->url_prefix() . '_templates_' . $this->getShortLocale() . '_' . $group . '_user_' . $userId;
    }
    
    public function makeUtilityKey(string $keySuffix): string
    {
        return preg_replace('%[^a-zA-Z0-9]+%i', '_', $this->url_prefix()) . '_' . $keySuffix;
    }
    
    /** @noinspection OnlyWritesOnParameterInspection */
    public function declareRoutes(Application $app, string $sectionName): void
    {
        $cmfConfig = $this;
        $groupConfig = [
            'prefix' => $this->url_prefix(),
            'middleware' => (array)$this->config('routes_middleware', ['web']),
        ];
        $cmfSectionSelectorMiddleware = $this->getUseCmfSectionMiddleware($sectionName);
        array_unshift($groupConfig['middleware'], $cmfSectionSelectorMiddleware);
        $namespace = $this->config('controllers_namespace');
        if (!empty($namespace)) {
            $groupConfig['namespace'] = ltrim($namespace, '\\');
        }
        // custom routes
        $files = $this->routes_files();
        $router = $app->make('router');
        if (count($files) > 0) {
            foreach ($files as $filePath) {
                $router->group($groupConfig, function () use ($filePath, $cmfConfig) {
                    // warning! $cmfConfig may be used inside included file
                    include base_path($filePath);
                });
            }
        }
        
        unset($groupConfig['namespace']); //< cmf routes should be able to use controllers from vendors dir
        if (!$router->has($this->getRouteName('cmf_start_page'))) {
            $router->group($groupConfig, function () use ($router) {
                $router->get('/', [
                    'uses' => $this->cmf_general_controller_class() . '@redirectToUserProfile',
                    'as' => $this->getRouteName('cmf_start_page'),
                ]);
            });
        }
        
        $router->group($groupConfig, function () use ($cmfConfig) {
            // warning! $cmfConfig may be used inside included file
            include __DIR__ . '/peskycmf.routes.php';
        });
        
        // special route for ckeditor config.js file
        $groupConfig['middleware'] = [$cmfSectionSelectorMiddleware]; //< only 1 needed
        $router->group($groupConfig, function () use ($router) {
            $router->get('ckeditor/config.js', [
                'uses' => $this->cmf_general_controller_class() . '@getCkeditorConfigJs',
                'as' => $this->getRouteName('cmf_ckeditor_config_js'),
            ]);
        });
    }
    
    /**
     * Get middleware that will tell system whick CMF section to use
     */
    protected function getUseCmfSectionMiddleware(string $sectionName): string
    {
        return UseCmfSection::class . ':' . $sectionName;
    }
    
    public function extendLaravelAppConfigs(Application $app): void
    {
        /** @var ConfigRepository $appConfigs */
        $appConfigs = $app->make('config');
        // add auth guard but do not select it as primary
        $this->addAuthGuardConfigToAppConfigs($appConfigs);
    }
    
    protected function addAuthGuardConfigToAppConfigs(ConfigRepository $appConfigs): void
    {
        // merge cmf guard and provider with configs in config/auth.php
        $cmfAuthConfig = $this->config('auth.guard');
        if (!is_array($cmfAuthConfig)) {
            // custom auth guard name provided
            return;
        }
        
        $config = $appConfigs->get('auth', [
            'guards' => [],
            'providers' => [],
        ]);
        
        $guardName = Arr::get($cmfAuthConfig, 'name') ?: $this->url_prefix();
        if (array_key_exists($guardName, $config['guards'])) {
            return; //< it is provided manually or cached
        }
        $provider = Arr::get($cmfAuthConfig, 'provider');
        if (is_array($provider)) {
            $providerName = Arr::get($provider, 'name', $guardName);
            if (empty($provider['model'])) {
                $provider['model'] = $this->config('auth.user_record_class', function () {
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
            'driver' => Arr::get($cmfAuthConfig, 'driver', 'session'),
            'provider' => $providerName,
        ];
        if (!empty($provider)) {
            $config['providers'][$providerName] = $provider;
        }
        
        $appConfigs->set('auth', $config);
    }
    
    public function initSection(Application $app): void
    {
        $this->setLaravelApp($app);
        
        // configurators
        $this->configureSession();
        $this->configureLanguageDetector();
        
        // locale handlers
        $this->listenForAppLocaleChanges();
        
        // init auth module
        /** @var CmfAuthModule $cmfAuthModuleClass */
        $cmfAuthModuleClass = $this->config('auth.module') ?: CmfAuthModule::class;
        /** @var CmfAuthModule $authModule */
        $authModule = new $cmfAuthModuleClass($this);
        $app->singleton(CmfAuthModule::class, function () use ($authModule) {
            return $authModule;
        });
        $authModule->init();
        
        // init UI module
        /** @var CmfAuthModule $cmfAuthModuleClass */
        $cmfUIModuleClass = $this->config('ui.module') ?: CmfUIModule::class;
        /** @var CmfAuthModule $authModule */
        $uiModule = new $cmfUIModuleClass($this);
        $app->singleton(CmfUIModule::class, function () use ($uiModule) {
            return $uiModule;
        });
        
        // init API Documentation module
        $app->singleton(CmfApiDocumentationModule::class, function () {
            $cmfApiDocsModuleClass = $this->config('api_documentation.module') ?: CmfApiDocumentationModule::class;
            return new $cmfApiDocsModuleClass($this);
        });
        
        // send $cmfConfig and $uiModule var to all views
        $this->getViewsFactory()->composer('*', function (View $view) use ($uiModule) {
            $view
                ->with('cmfConfig', $this)
                ->with('uiModule', $uiModule);
        });
        
        /** @var PeskyCmfLanguageDetectorServiceProvider $langDetectorProvider */
        $langDetectorProvider = $app->register(PeskyCmfLanguageDetectorServiceProvider::class);
        $app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        $langDetectorProvider->importConfigsFromPeskyCmf($this);
        
        if ($this->config('file_access_mask') !== null) {
            umask($this->config('file_access_mask'));
        }
        
        // Register resource name to ScaffoldConfig class mappings
        //$resources = $this->getUiModule()->getScaffolds();
        /** @var ScaffoldConfig $scaffoldConfigClass */
        /*foreach ($resources as $scaffoldConfigClass) {
            static::registerScaffoldConfigForResource($scaffoldConfigClass::getResourceName(), $scaffoldConfigClass);
        }*/
    }
    
    protected function setLaravelApp(Application $app): void
    {
        $this->app = $app;
        $this->languageDetector = $app->make(LanguageDetectorInterface::class);
        $this->configs = $app->make(ConfigRepository::class);
        $this->viewsFactory = $app->make('view');
    }
    
    protected function configureSession(): void
    {
        $appConfigs = $this->getLaravelConfigs();
        $config = array_merge(
            $appConfigs->get('session'),
            ['path' => '/' . trim($this->url_prefix(), '/')],
            (array)$this->config('session', [])
        );
        $appConfigs->set('session', $config);
    }
    
    protected function configureLanguageDetector(): void
    {
        //< todo: extract language detection and updates to separate module and get rid of PeskyCmfLanguageDetectorServiceProvider
        /** @var PeskyCmfLanguageDetectorServiceProvider $langDetectorProvider */
        $langDetectorProvider = $this->getLaravelApp()->register(PeskyCmfLanguageDetectorServiceProvider::class);
        $this->getLaravelApp()->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        $langDetectorProvider->importConfigsFromPeskyCmf($this);
    }
    
    protected function getEventsDispatcher(): Dispatcher
    {
        return $this->getLaravelApp()->make('events');
    }
    
    protected function listenForAppLocaleChanges(): void
    {
        $this->getEventsDispatcher()->listen(
            LocaleUpdated::class,
            function (LocaleUpdated $event) {
                $this->onLocaleChanged($event->locale);
            }
        );
    }
    
}
