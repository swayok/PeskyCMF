<?php

namespace PeskyCMF\UI;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\KeyValueTableScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldConfigInterface;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\File;

class CmfUIModule {

    protected $cmfConfig;

    protected $scaffoldTemplatesForNormalTableViewPath = 'cmf::scaffold.templates';
    protected $scaffoldTemplatesForKeyValueTableViewPath = 'cmf::scaffold.templates';

    protected $defaultSidebarLogo = '<img src="/packages/cmf/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';

    protected $UIViews = [
        'layout' => 'cmf::layout',
        'ui' => 'cmf::ui.ui',
        'footer' => 'cmf::ui.footer',
        'sidebar_user_info' => 'cmf::ui.sidebar_user_info',
        'sidebar_menu' => 'cmf::ui.sidebar_menu',
        'top_navbar' => 'cmf::ui.top_navbar',
    ];
    protected $isUIViewsLoadedFromConfigs = false;

    protected $resources = null;
    protected $tables = null;
    protected $scaffoldConfigs = [];

    protected $menuItemsFromScaffoldConfigs;
    protected $customMenuItems = [];
    protected $allMenuItems;

    public function __construct(CmfConfig $cmfConfig) {
        $this->cmfConfig = $cmfConfig;
    }

    /**
     * @return CmfConfig
     */
    public function getCmfConfig() {
        return $this->cmfConfig;
    }

    public function renderLayoutView(): string {
        return $this->renderUIView('layout', $this->getDataForLayout());
    }

    protected function getDataForLayout(): array {
        $uiSkin = $this->getSkinName();
        return [
            'skin' => $uiSkin,
            'coreAssets' => $this->getCoreAssetsForLayout($uiSkin),
            'customAssets' => $this->getCustomAssetsForLayout(),
            'scriptsVersion' => '2.3.3',
            'jsAppSettings' => $this->getJsAppSettings(),
            'jsAppData' => $this->getJsAppData(),
        ];
    }

    public function getSkinName(): string {
        return $this->getCmfConfig()->config('ui.skin', 'skin-blue');
    }

    public function getCoreAssetsForLayout(string $skin): array {
        $assetsMode = config('peskycmf.assets');
        $subFolder = 'min';
        $minSuffix = '.min';
        $isSrcMode = stripos($assetsMode, 'src') === 0;
        if ($isSrcMode || $assetsMode === 'packed') {
            $minSuffix = '';
            $subFolder = 'packed';
        }
        $locale = $this->getCmfConfig()->getLocaleWithSuffix('_');
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

    public function getCustomAssetsForLayout(): array {
        return [
            'js' => (array)$this->getCmfConfig()->config('ui.js_files', []),
            'css' => (array)$this->getCmfConfig()->config('ui.css_files', []),
            'js_code_blocks' => (array)$this->getCmfConfig()->config('ui.js_code_blocks', []),
        ];
    }

    /**
     * Prefix to load custom views from.
     * For example
     * - if custom views stored in /resources/views/admin - prefix should be "admin."
     * - if you placed views under namespace "admin" - prefix should be "admin:"
     * @return string
     */
    public function getCustomViewsPrefix(): string {
        return $this->getCmfConfig()->config('ui.views_subfolder', 'admin') . '.';
    }

    public function renderBasicUIView() {
        $cmfConfig = $this->getCmfConfig();
        return $this->renderUIView('ui', [
            'sidebarLogo' => $cmfConfig::config('ui.sidebar_logo') ?: $this->defaultSidebarLogo,
        ]);
    }

    public function renderScaffoldTemplates(ScaffoldConfigInterface $scaffoldConfig) {
        $view = $scaffoldConfig instanceof KeyValueTableScaffoldConfig
            ? $this->scaffoldTemplatesForNormalTableViewPath
            : $this->scaffoldTemplatesForKeyValueTableViewPath;
        return view(
            $view,
            array_merge(
                $scaffoldConfig->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => $scaffoldConfig::getResourceName()]
            )
        )->render();
    }

    protected function loadUIViewsFromConfig() {
        if (!$this->isUIViewsLoadedFromConfigs) {
            $this->UIViews = array_replace(
                $this->UIViews,
                (array)$this->getCmfConfig()->config('ui.views', [])
            );
        }
    }

    public function getUIView(string $viewName) {
        $this->loadUIViewsFromConfig();
        if (!isset($this->UIViews[$viewName])) {
            abort(HttpCode::NOT_FOUND, "There is no UI view with name [$viewName]");
        }
        return $this->UIViews[$viewName];
    }

    public function renderUIView(string $viewName, array $data = []): string {
        return view($this->getUIView($viewName), $data)->render();
    }

    public function getResources(): array {
        if ($this->resources === null) {
            $this->resources = [];
            /** @var ScaffoldConfigInterface $scaffoldConfigClass */
            foreach ((array)$this->getCmfConfig()->config('ui.resources', []) as $scaffoldConfigClass) {
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
    public function getScaffoldConfig(string $resourceName): ScaffoldConfigInterface {
        if (!isset($this->scaffoldConfigs[$resourceName])) {
            $className = $this->getScaffoldConfigClass($resourceName);
            $this->scaffoldConfigs[$resourceName] = new $className();
        }
        return $this->scaffoldConfigs[$resourceName];
    }

    /**
     * @param string $resourceName
     * @return string|ScaffoldConfig
     * @throws \InvalidArgumentException
     */
    public function getScaffoldConfigClass(string $resourceName): string {
        return array_get($this->getResources(), $resourceName, function () use ($resourceName) {
            throw new \InvalidArgumentException(
                'There is no known ScaffoldConfig class for resource "' . $resourceName . '"'
            );
        });
    }

    /**
     * Get values for menu items counters (details in CmfConfig::menu())
     * @return array like ['pending_orders' => '<span class="label label-primary pull-right">2</span>']
     */
    public function getValuesForMenuItemsCounters(): array {
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
    public function getTableByResourceName(string $resourceName): TableInterface {
        if (!isset($this->tables[$resourceName])) {
            $scaffoldConfigClass = $this->getScaffoldConfigClass($resourceName);
            $this->tables[$resourceName] = $scaffoldConfigClass::getTable();
        }
        return $this->tables[$resourceName];
    }

    /**
     * JS application settings (accessed via CmfSettings global variable)
     * @return array
     */
    public function getJsAppSettings(): array {
        $cmfConfig = $this->getCmfConfig();
        return [
            'isDebug' => config('app.debug'),
            'rootUrl' => '/' . trim($cmfConfig::url_prefix(), '/'),
            'enablePing' => (int)$cmfConfig::config('ping_interval') > 0,
            'pingInterval' => (int)$cmfConfig::config('ping_interval') * 1000,
            'uiUrl' => $cmfConfig::route('cmf_main_ui', [], false),
            'userDataUrl' => $cmfConfig::route('cmf_profile_data', [], false),
            'menuCountersDataUrl' => $cmfConfig::route('cmf_menu_counters_data', [], false),
            'defaultPageTitle' => $cmfConfig::default_page_title(),
            'pageTitleAddition' => $cmfConfig::page_title_addition(),
            'localizationStrings' => $cmfConfig::transGeneral('ui.js_component'),
        ];
    }

    /**
     * Variables that will be sent to js and stored into AppData
     * To access data from js code use AppData.key_name
     * @return array
     */
    public function getJsAppData(): array {
        return [];
    }

    /**
     * @param string $itemKey
     * @param array|\Closure $menuItem - format: see menu()
     */
    public function addCustomMenuItem(string $itemKey, $menuItem) {
        $this->customMenuItems[$itemKey] = $menuItem;
        if ($this->allMenuItems) {
            $menuItem = value($menuItem);
            if (is_array($menuItem) && !empty($menuItem)) {
                $this->allMenuItems[$itemKey] = $menuItem;
            }
        }
    }

    public function getMenuItems(): array {
        if (!$this->allMenuItems) {
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

    protected function getMenuItemsFromScaffoldConfigs(): array {
        if ($this->menuItemsFromScaffoldConfigs === null) {
            $this->menuItemsFromScaffoldConfigs = [];
            /** @var ScaffoldConfig $scaffoldConfigClass */
            foreach ($this->getResources() as $resourceName => $scaffoldConfigClass) {
                $this->menuItemsFromScaffoldConfigs[$resourceName] = $scaffoldConfigClass::getMainMenuItem();
            }
        }
        return $this->menuItemsFromScaffoldConfigs;
    }

}