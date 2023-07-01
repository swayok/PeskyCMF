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
use PeskyORM\ORM\Table\TableInterface;
use Swayok\Utils\File;

class CmfUIModule
{
    protected CmfConfig $cmfConfig;
    protected ViewsFactory $viewsFactory;

    protected string $scaffoldTemplatesForNormalTableViewPath = 'cmf::scaffold.templates';
    protected string $scaffoldTemplatesForKeyValueTableViewPath = 'cmf::scaffold.templates';

    protected string $defaultSidebarLogo =
        '<img src="/vendor/peskycmf/raw/img/peskycmf-logo-white.svg" height="30" alt=" " class="va-t mt10">';

    protected array $uiViews = [
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
                "/vendor/peskycmf/raw/js/jquery{$minSuffix}.js",
            ],
            'js' => [
                'cmf-libs' => "/vendor/peskycmf/{$subFolder}/js/cmf-libs.js",
                'cmf-jquery-and-bootstrap-plugins' => "/vendor/peskycmf/{$subFolder}/js/cmf-jquery-and-bootstrap-plugins.js", // phpcs:ignore
                'cmf-core' => "/vendor/peskycmf/{$subFolder}/js/cmf-core.js",
                'localization' => "/vendor/peskycmf/{$subFolder}/js/locale/{$locale}.js",
                'app' => '/vendor/peskycmf/raw/js/cmf-app.js',
            ],
            'css' => [
                '/vendor/peskycmf/raw/css/fonts/Roboto/roboto.css',
                '/vendor/peskycmf/raw/css/bootstrap/bootstrap.css',
                '/vendor/peskycmf/raw/css/adminlte/AdminLTE.css',
                "/vendor/peskycmf/raw/css/adminlte/skins/{$skin}.css",
                'cmf-libs' => "/vendor/peskycmf/{$subFolder}/css/cmf-libs.css",
                'cmf-core' => "/vendor/peskycmf/{$subFolder}/css/cmf.css",
                "/vendor/peskycmf/raw/font-awesome/css/font-awesome{$minSuffix}.css",
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
                        $ret['js'][$packName][] = '/vendor/peskycmf/src/' . $path;
                    }
                }
                if (isset($ret['css'][$packName])) {
                    $ret['css'][$packName] = [];
                    foreach ($files['stylesheets'][$packName]['files'] as $path) {
                        $ret['css'][$packName][] = '/vendor/peskycmf/src/' . $path;
                    }
                }
            }
            $ret['js']['app'] = '/vendor/peskycmf/src/src/js/cmf.app.js';
            if (!$isCore && isset($files['localizations'][$locale])) {
                $ret['js']['localization'] = [];
                foreach ($files['localizations'][$locale]['files'] as $path) {
                    $ret['js']['localization'][] = '/vendor/peskycmf/src/' . $path;
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
        return [];
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
            $this->uiViews = array_replace(
                $this->uiViews,
                (array)$this->cmfConfig->config('ui.views', [])
            );
        }
    }

    public function getUIView(string $viewName): string
    {
        $this->loadUIViewsFromConfig();
        if (!isset($this->uiViews[$viewName])) {
            abort(HttpCode::NOT_FOUND, "There is no UI view with name [$viewName]");
        }
        return $this->uiViews[$viewName];
    }

    public function renderUIView(string $viewName, array $data = []): string
    {
        $data['cmfConfig'] = $this->cmfConfig;
        $data['uiModule'] = $this;
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
     * @throws \InvalidArgumentException
     */
    public function getScaffoldConfigClass(string $resourceName): string
    {
        return Arr::get(
            $this->getResources(),
            $resourceName,
            static function () use ($resourceName) {
                throw new \InvalidArgumentException(
                    'Cannot find ScaffoldConfig class for resource "' . $resourceName . '"'
                );
            }
        );
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
     * Note: can be overwritten to allow usage of fake tables in resources routes
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

    public function getUiUrl(bool $absolute = false): string
    {
        return $this->cmfConfig->route('cmf_main_ui', [], $absolute);
    }

    /**
     * JS application settings (accessed via CmfSettings global variable)
     */
    public function getJsAppSettings(): array
    {
        return [
            'isDebug' => $this->cmfConfig->getLaravelConfigs()->get('app.debug'),
            'rootUrl' => '/' . trim($this->cmfConfig->urlPrefix(), '/'),
            'enablePing' => (int)$this->cmfConfig->config('ping_interval') > 0,
            'pingInterval' => (int)$this->cmfConfig->config('ping_interval') * 1000,
            'uiUrl' => $this->getUiUrl(false),
            'userDataUrl' => $this->cmfConfig->route('cmf_profile_data', [], false),
            'menuCountersDataUrl' => $this->cmfConfig->route('cmf_menu_counters_data', [], false),
            'defaultPageTitle' => $this->cmfConfig->defaultPageTitle(),
            'pageTitleAddition' => $this->cmfConfig->pageTitleAddition(),
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
     * $menuItem format described in CmfConfig->menu()
     */
    public function addCustomMenuItem(string $itemKey, array|\Closure $menuItem): static
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
                $this->menuItemsFromScaffoldConfigs[$resourceName]
                    = $this->getScaffoldConfig($resourceName)->getMainMenuItem();
            }
        }
        return $this->menuItemsFromScaffoldConfigs;
    }

    public static function modifyDotJsTemplateToAllowInnerScriptsAndTemplates(string $dotJsTemplate): string
    {
        return preg_replace_callback('%<script([^>]*)>(.*?)</script>%is', function ($matches) {
            if (preg_match('%type="text/html"%i', $matches[1])) {
                // inner dotjs template - needs to be encoded and decoded later
                $encoded = base64_encode($matches[2]);
                return "{{= '<' + 'script{$matches[1]}>' }}{{= Base64.decode('$encoded') }}{{= '</' + 'script>'}}";
            }
            // remove "//" comments from a script
            $script = preg_replace('%(^|\s)//.*$%m', '$1', $matches[2]);
            return "{{= '<' + 'script{$matches[1]}>' }}$script{{= '</' + 'script>'}}";
        }, $dotJsTemplate);
    }
}
