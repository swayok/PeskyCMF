<?php

declare(strict_types=1);

namespace PeskyCMF\UI;

use Illuminate\Support\Arr;
use Illuminate\View\Factory as ViewsFactory;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\File;

class CmfUIModule
{
    
    protected CmfConfig $cmfConfig;
    protected ViewsFactory $viewsFactory;
    
    protected string $scaffoldTemplatesForNormalTableViewPath = 'cmf::scaffold.templates';
    protected string $scaffoldTemplatesForKeyValueTableViewPath = 'cmf::scaffold.templates';
    
    protected string $defaultSidebarLogo = '<img src="/packages/cmf/raw/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';
    
    protected array $UIViews = [
        'layout' => 'cmf::layout',
        'ui' => 'cmf::ui.ui',
        'footer' => 'cmf::ui.footer',
        'sidebar_user_info' => 'cmf::ui.sidebar_user_info',
        'sidebar_menu' => 'cmf::ui.sidebar_menu',
        'top_navbar' => 'cmf::ui.top_navbar',
    ];
    protected bool $isUIViewsLoadedFromConfigs = false;
    
    protected ?array $resources = null;
    protected array $tables = [];
    protected array $scaffoldConfigs = [];
    
    protected ?array $menuItemsFromScaffoldConfigs = null;
    protected array $customMenuItems = [];
    protected ?array $allMenuItems = null;
    
    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
        $this->viewsFactory = $cmfConfig->getLaravelApp()->make('view');
    }
    
    public function renderLayoutView(): string
    {
        return $this->renderUIView('layout', $this->getDataForLayout());
    }
    
    protected function getDataForLayout(): array
    {
        $uiSkin = $this->getSkinName();
        return [
            'skin' => $uiSkin,
            'coreAssets' => $this->getCoreAssetsForLayout($uiSkin),
            'customAssets' => $this->getCustomAssetsForLayout(),
            'scriptsVersion' => '2.3.4',
            'jsAppSettings' => $this->getJsAppSettings(),
            'jsAppData' => $this->getJsAppData(),
        ];
    }
    
    public function getSkinName(): string
    {
        return $this->cmfConfig->config('ui.skin', 'skin-blue');
    }
    
    public function getCoreAssetsForLayout(string $skin): array
    {
        $assetsMode = config('peskycmf.assets');
        $subFolder = 'min';
        $minSuffix = '.min';
        $isSrcMode = stripos($assetsMode, 'src') === 0;
        if ($isSrcMode || $assetsMode === 'packed') {
            $minSuffix = '';
            $subFolder = 'packed';
        }
        $locale = $this->cmfConfig->getLocaleWithSuffix('_');
        $ret = [
            'js-head' => [
                "/packages/cmf/raw/js/jquery{$minSuffix}.js",
            ],
            'js' => [
                'cmf-libs' => "/packages/cmf/{$subFolder}/js/cmf-libs.js",
                'cmf-jquery-and-bootstrap-plugins' => "/packages/cmf/{$subFolder}/js/cmf-jquery-and-bootstrap-plugins.js",
                'cmf-core' => "/packages/cmf/{$subFolder}/js/cmf-core.js",
                'localization' => "/packages/cmf/{$subFolder}/js/locale/{$locale}.js",
                'app' => '/packages/cmf/raw/js/cmf-app.js',
            ],
            'css' => [
                '/packages/cmf/raw/css/fonts/Roboto/roboto.css',
                '/packages/cmf/raw/css/bootstrap/bootstrap.css',
                '/packages/cmf/raw/css/adminlte/AdminLTE.css',
                "/packages/cmf/raw/css/adminlte/skins/{$skin}.css",
                'cmf-libs' => "/packages/cmf/{$subFolder}/css/cmf-libs.css",
                'cmf-core' => "/packages/cmf/{$subFolder}/css/cmf.css",
                "/packages/cmf/raw/font-awesome/css/font-awesome{$minSuffix}.css",
            ],
        ];
        
        if ($isSrcMode) {
            $files = File::readJson(__DIR__ . '/../../../npm/config/cmf-assets.json');
            $isCore = $assetsMode === 'src-core';
            $packs = $isCore ? ['cmf-core'] : ['cmf-libs', 'cmf-core', 'cmf-jquery-and-bootstrap-plugins'];
            foreach ($packs as $packName) {
                if (isset($ret['js'][$packName])) {
                    $ret['js'][$packName] = [];
                    foreach ($files['scripts'][$packName]['files'] as $path) {
                        $ret['js'][$packName][] = '/packages/cmf/src/' . $path;
                    }
                }
                if (isset($ret['css'][$packName])) {
                    $ret['css'][$packName] = [];
                    foreach ($files['stylesheets'][$packName]['files'] as $path) {
                        $ret['css'][$packName][] = '/packages/cmf/src/' . $path;
                    }
                }
            }
            $ret['js']['app'] = '/packages/cmf/src/src/js/cmf.app.js';
            if (!$isCore && isset($files['localizations'][$locale])) {
                $ret['js']['localization'] = [];
                foreach ($files['localizations'][$locale]['files'] as $path) {
                    $ret['js']['localization'][] = '/packages/cmf/src/' . $path;
                }
            }
        }
        return $ret;
    }
    
    public function getCustomAssetsForLayout(): array
    {
        return [
            'js' => (array)$this->cmfConfig->config('ui.js_files', []),
            'css' => (array)$this->cmfConfig->config('ui.css_files', []),
            'js_code_blocks' => (array)$this->cmfConfig->config('ui.js_code_blocks', []),
        ];
    }
    
    /**
     * Prefix to load custom views from.
     * For example
     * - if custom views stored in /resources/views/admin - prefix should be "admin."
     * - if you placed views under namespace "admin" - prefix should be "admin:"
     */
    public function getCustomViewsPrefix(): string
    {
        return $this->cmfConfig->config('ui.views_subfolder', 'admin') . '.';
    }
    
    public function renderBasicUIView(): string
    {
        return $this->renderUIView('ui', $this->getDataForBasicUiView());
    }
    
    protected function getDataForBasicUiView(): array
    {
        return [
            'sidebarLogo' => $this->getSidebarLogo(),
        ];
    }
    
    public function getSidebarLogo(): string
    {
        return (string)($this->cmfConfig->config('ui.sidebar_logo') ?: $this->defaultSidebarLogo);
    }
    
    public function renderScaffoldTemplates(ScaffoldConfigInterface $scaffoldConfig): string
    {
        $view = $scaffoldConfig instanceof KeyValueTableScaffoldConfig
            ? $this->scaffoldTemplatesForNormalTableViewPath
            : $this->scaffoldTemplatesForKeyValueTableViewPath;
        return $this->viewsFactory->make(
            $view,
            array_merge(
                $scaffoldConfig->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => $scaffoldConfig::getResourceName()]
            )
        )->render();
    }
    
    protected function loadUIViewsFromConfig(): void
    {
        if (!$this->isUIViewsLoadedFromConfigs) {
            $this->UIViews = array_replace(
                $this->UIViews,
                (array)$this->cmfConfig->config('ui.views', [])
            );
        }
    }
    
    public function getUIView(string $viewName): string
    {
        $this->loadUIViewsFromConfig();
        if (!isset($this->UIViews[$viewName])) {
            abort(HttpCode::NOT_FOUND, "There is no UI view with name [$viewName]");
        }
        return $this->UIViews[$viewName];
    }
    
    public function renderUIView(string $viewName, array $data = []): string
    {
        return $this->viewsFactory->make($this->getUIView($viewName), $data)->render();
    }
    
    public function getResources(): array
    {
        if ($this->resources === null) {
            $this->resources = [];
            /** @var ScaffoldConfigInterface $scaffoldConfigClass */
            foreach ((array)$this->cmfConfig->config('ui.resources', []) as $scaffoldConfigClass) {
                $this->resources[$scaffoldConfigClass::getResourceName()] = $scaffoldConfigClass;
            }
        }
        return $this->resources;
    }
    
    /**
     * Get ScaffoldConfig instance
     * @param string $resourceName - table name passed via route parameter, may differ from $table->getTableName()
     *      and added here to be used in child configs when you need to use scaffolds with fake table names.
     *      It should be used together with static::getModelByTableName() to provide correct model for a fake table name
     * @return ScaffoldConfigInterface
     * @throws \InvalidArgumentException
     */
    public function getScaffoldConfig(string $resourceName): ScaffoldConfigInterface
    {
        if (!isset($this->scaffoldConfigs[$resourceName])) {
            /** @var ScaffoldConfig $className */
            $className = $this->getScaffoldConfigClass($resourceName);
            $this->scaffoldConfigs[$resourceName] = new $className($this->cmfConfig);
        }
        return $this->scaffoldConfigs[$resourceName];
    }
    
    /**
     * @param string $resourceName
     * @return string|ScaffoldConfig
     * @throws \InvalidArgumentException
     */
    public function getScaffoldConfigClass(string $resourceName): string
    {
        return Arr::get($this->getResources(), $resourceName, function () use ($resourceName) {
            throw new \InvalidArgumentException(
                'There is no known ScaffoldConfig class for resource "' . $resourceName . '"'
            );
        });
    }
    
    /**
     * Get values for menu items counters (details in CmfConfig::menu())
     * @return array like ['pending_orders' => '<span class="label label-primary pull-right">2</span>']
     */
    public function getValuesForMenuItemsCounters(): array
    {
        $counters = [];
        /** @var ScaffoldConfigInterface $scaffoldConfigClass */
        foreach ($this->getResources() as $scaffoldConfigClass) {
            $counterClosure = $scaffoldConfigClass::getMenuItemCounterValue();
            if (!empty($counterClosure)) {
                $counters[$scaffoldConfigClass::getMenuItemCounterName()] = value($counterClosure);
            }
        }
        return $counters;
    }
    
    /**
     * Get TableInterface instance for $tableName
     * Note: can be ovewritted to allow usage of fake tables in resources routes
     * It is possible to use this with static::getScaffoldConfig() to alter default scaffold configs
     * @param string $resourceName
     * @return TableInterface
     */
    public function getTableByResourceName(string $resourceName): TableInterface
    {
        if (!isset($this->tables[$resourceName])) {
            /** @var ScaffoldConfig $scaffoldConfigClass */
            $scaffoldConfigClass = $this->getScaffoldConfigClass($resourceName);
            $this->tables[$resourceName] = $scaffoldConfigClass::getTable();
        }
        return $this->tables[$resourceName];
    }
    
    /**
     * JS application settings (accessed via CmfSettings global variable)
     */
    public function getJsAppSettings(): array
    {
        return [
            'isDebug' => $this->cmfConfig->getLaravelConfigs()->get('app.debug'),
            'rootUrl' => '/' . trim($this->cmfConfig->url_prefix(), '/'),
            'enablePing' => (int)$this->cmfConfig->config('ping_interval') > 0,
            'pingInterval' => (int)$this->cmfConfig->config('ping_interval') * 1000,
            'uiUrl' => $this->cmfConfig->route('cmf_main_ui', [], false),
            'userDataUrl' => $this->cmfConfig->route('cmf_profile_data', [], false),
            'menuCountersDataUrl' => $this->cmfConfig->route('cmf_menu_counters_data', [], false),
            'defaultPageTitle' => $this->cmfConfig->default_page_title(),
            'pageTitleAddition' => $this->cmfConfig->page_title_addition(),
            'localizationStrings' => $this->cmfConfig->transGeneral('ui.js_component'),
        ];
    }
    
    /**
     * Variables that will be sent to js and stored into AppData
     * To access data from js code use AppData.key_name
     */
    public function getJsAppData(): array
    {
        return [];
    }
    
    /**
     * @param string $itemKey
     * @param array|\Closure $menuItem - format: see menu()
     * @return static
     */
    public function addCustomMenuItem(string $itemKey, $menuItem)
    {
        $this->customMenuItems[$itemKey] = $menuItem;
        if ($this->allMenuItems) {
            $menuItem = value($menuItem);
            if (is_array($menuItem) && !empty($menuItem)) {
                $this->allMenuItems[$itemKey] = $menuItem;
            }
        }
        return $this;
    }
    
    public function getMenuItems(): array
    {
        if ($this->allMenuItems === null) {
            $this->allMenuItems = $this->getMenuItemsFromScaffoldConfigs();
            foreach ($this->customMenuItems as $key => $menuItem) {
                $menuItem = value($menuItem);
                if (is_array($menuItem) && !empty($menuItem)) {
                    $this->allMenuItems[$key] = $menuItem;
                }
            }
        }
        return $this->allMenuItems;
    }
    
    protected function getMenuItemsFromScaffoldConfigs(): array
    {
        if ($this->menuItemsFromScaffoldConfigs === null) {
            $this->menuItemsFromScaffoldConfigs = [];
            /** @var ScaffoldConfig $scaffoldConfigClass */
            foreach ($this->getResources() as $resourceName => $scaffoldConfigClass) {
                $this->menuItemsFromScaffoldConfigs[$resourceName] = $this->getScaffoldConfig($resourceName)->getMainMenuItem();
            }
        }
        return $this->menuItemsFromScaffoldConfigs;
    }
    
}