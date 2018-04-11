<?php

namespace PeskyCMF\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Console\Commands\CmfAddAdminCommand;
use PeskyCMF\Console\Commands\CmfInstallCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsLoggingCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsProfilingCommand;
use PeskyCMF\Console\Commands\CmfMakeApiMethodDocCommand;
use PeskyCMF\Console\Commands\CmfMakeScaffoldCommand;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\Admins\CmfAdminsTable;
use PeskyCMF\Db\Admins\CmfAdminsTableStructure;
use PeskyCMF\Event\AdminAuthenticated;
use PeskyCMF\Listeners\AdminAuthenticatedEventListener;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;
use PeskyORM\ORM\TableStructureInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Vluzrmos\LanguageDetector\Facades\LanguageDetector;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;
use Illuminate\Foundation\Application;

/**
 * @property Application $app
 */
class PeskyCmfServiceProvider extends ServiceProvider {

    /**
     * @var PeskyCmfLanguageDetectorServiceProvider
     */
    protected $langDetectorProvider;

    /**
     * @var CmfConfig
     */
    private $config;

    /**
     * @var PeskyCmfAppSettings
     */
    private $appSettings;

    /**
     * @var null|bool
     */
    private $fitsRequestUri = null;

    public function register() {
        $this->mergeConfigFrom($this->getConfigFilePath(), 'peskycmf');

        $this->initDefaultCmfConfig();
        $this->initPrimaryCmfConfig();

        $this->app->register(PeskyCmfPeskyOrmServiceProvider::class);
        /** @var PeskyCmfLanguageDetectorServiceProvider $langDetectorProvider */
        $langDetectorProvider = $this->app->register(PeskyCmfLanguageDetectorServiceProvider::class);
        $this->app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        AliasLoader::getInstance()->alias('LanguageDetector', LanguageDetector::class);

        if ($this->app->environment() === 'local') {
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            }
            if (class_exists('\Barryvdh\Debugbar\ServiceProvider')) {
                $this->app->register('\Barryvdh\Debugbar\ServiceProvider');
            }
        }

        $this->registerCommands();

        if ($this->fitsRequestUri()) {
            $this->registerDbClasses();
            $langDetectorProvider->importConfigsFromPeskyCmf($this->getCmfConfig());
        }

        $this->app->singleton(PeskyCmfAppSettings::class, function () {
            return $this->getAppSettings();
        });
    }

    public function provides() {
        return [
            CmfConfig::class,
            PeskyCmfAppSettings::class,
        ];
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function boot() {
        require_once __DIR__ . '/../Config/helpers.php';

        $this->configurePublishes();

        $this->configureTranslations();
        $this->configureViews();

        if ($this->fitsRequestUri()) {
            $this->declareRoutes();
            $this->mergeAuthenticationConfigs();
            if ($this->getCmfConfig()->config('file_access_mask') !== null) {
                umask($this->getCmfConfig()->config('file_access_mask'));
            }
            $this->configureSession();
            $this->configureEventListeners();
            $this->configureAuthorizationGatesAndPolicies();
            $this->configureDefaultAuthGuard();
            $this->registerScaffoldConfigs();
        }


    }

    /**
     * @return int
     */
    protected function fitsRequestUri() {
        if ($this->fitsRequestUri === null) {
            if ($this->runningInConsole()) {
                $this->fitsRequestUri = true;
            } else {
                $prefix = trim($this->getCmfConfig()->url_prefix(), '/');
                $this->fitsRequestUri = preg_match("%^/{$prefix}(/|$)%", array_get($_SERVER, 'REQUEST_URI', '/')) > 0;
            }
            $this->app->offsetSet('is_peskycmf_section', $this->fitsRequestUri);
        }
        return $this->fitsRequestUri;
    }

    /**
     * Overwrite this method in your custom service provider if you do want or do not want to run
     * section-specific methods in register() and boot() methods
     * @return bool
     */
    protected function runningInConsole() {
        return $this->app->runningInConsole();
    }

    /**
     * @return CmfConfig
     */
    protected function getCmfConfig() {
        if ($this->config === null) {
            /** @var CmfConfig|string $className */
            $className = config('peskycmf.default_cmf_config', CmfConfig::class);
            $this->config = $className::getInstance();
        }
        return $this->config;
    }

    /**
     * @return CmfConfig|null - return null if your CmfConfig should not be default
     */
    protected function initDefaultCmfConfig() {
        return $this->getCmfConfig()->useAsDefault();
    }

    /**
     * @return CmfConfig|null - return null if your CmfConfig should not be default
     */
    protected function initPrimaryCmfConfig() {
        $this->app->singleton(CmfConfig::class, function () {
            return $this->getCmfConfig();
        });
        return $this->getCmfConfig()->useAsPrimary();
    }

    /**
     * @return PeskyCmfAppSettings
     */
    protected function getAppSettings() {
        if ($this->appSettings === null) {
            /** @var PeskyCmfAppSettings $appSettingsClass */
            $appSettingsClass = $this->getCmfConfig()->config('app_settings_class') ?: PeskyCmfAppSettings::class;
            $this->appSettings = $appSettingsClass::getInstance();
        }
        return $this->appSettings;
    }

    /**
     * @return ParameterBag
     */
    protected function getAppConfig() {
        return $this->app['config'];
    }

    /**
     * Register DB classes used in CMF/CMS in app's service container
     */
    protected function registerDbClasses() {
        if (!$this->app->bound(CmfAdmin::class)) {
            /** @var RecordInterface $userRecordClass */
            $userRecordClass = $this->getCmfConfig()->user_record_class();
            $this->registerClassNameSingleton(CmfAdmin::class, $userRecordClass);
            $this->registerClassInstanceSingleton(CmfAdminsTable::class, $userRecordClass::getTable());
            $this->registerClassInstanceSingleton(
                CmfAdminsTableStructure::class,
                $userRecordClass::getTable()->getTableStructure()
            );
        }
    }

    /**
     * Register resource name to ScaffoldConfig class mappings
     * @throws \UnexpectedValueException
     */
    protected function registerScaffoldConfigs() {
        $cmfConfig = $this->getCmfConfig();
        /** @var ScaffoldConfig $scaffoldConfigClass */
        foreach ($this->getScaffoldConfigs() as $resourceName => $scaffoldConfigClass) {
            $cmfConfig::registerScaffoldConfigForResource($resourceName, $scaffoldConfigClass);
        }
    }

    /**
     * @return array
     */
    protected function getScaffoldConfigs() {
        /** @var ScaffoldConfig[] $resources */
        $resources = (array)$this->getCmfConfig()->config('resources', []);
        $normalized = [];
        foreach ($resources as $scaffoldConfig) {
            $normalized[$scaffoldConfig::getResourceName()] = $scaffoldConfig;
        }
        return $normalized;
    }

    /**
     * Used to register DB Record class names (Records are not singletons but class name is a singleton)
     * @param string $singletonName - singleton name in app's service container (should be a class name)
     * @param null|string $className - maps singleton to this class. When null - $singletonName is used as $className.
     *      Singleton will return a class name, not an instance of class
     */
    protected function registerClassNameSingleton($singletonName, $className = null) {
        if (empty($className)) {
            $className = $singletonName;
        }
        $this->app->singleton($singletonName, function () use ($className) {
            // note: do not create record here or you will possibly encounter infinite loop because this class may be
            // used in TableStructure via app(NameTableStructure) (for example to get default value, etc)
            return $className;
        });
    }

    /**
     * Used to register DB Table or DB TableStructure singleton instances
     * @param string $singletonName - singleton name in app's service container (should be a class name)
     * @param null|string|TableInterface|TableStructureInterface $classNameOrInstance - map this class/TableInterface
     *      to a singleton. When null - $singletonName is used as $className. Singleton will return an instance of class
     */
    protected function registerClassInstanceSingleton($singletonName, $classNameOrInstance = null) {
        if (empty($classNameOrInstance)) {
            $classNameOrInstance = $singletonName;
        }
        $this->app->singleton($singletonName, function () use ($classNameOrInstance) {
            /** @var TableInterface|TableStructureInterface $classNameOrInstance */
            return is_string($classNameOrInstance)
                ? $classNameOrInstance::getInstance()
                : $classNameOrInstance;
        });
    }

    protected function configurePublishes() {
        if (!$this->runningInConsole()) {
            return ;
        }
        $cmfPublicDir = __DIR__ . '/..';
        $this->publishes([
            // cmf
            $cmfPublicDir . '/public/css' => public_path('packages/cmf/css'),
            $cmfPublicDir . '/public/js' => public_path('packages/cmf/js'),
            $cmfPublicDir . '/public/less' => public_path('packages/cmf/less'),
            $cmfPublicDir . '/public/img' => public_path('packages/cmf/img'),
            base_path('vendor/swayok/page.js/page.js') => public_path('packages/cmf-vendors/router/page.js'),
            // AdminLTE
            base_path('vendor/almasaeed2010/adminlte/dist/js/adminlte.js') => public_path('packages/adminlte/js/app.js'),
            base_path('vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js') => public_path('packages/adminlte/js/app.min.js'),
            base_path('vendor/almasaeed2010/adminlte/dist/img/boxed-bg.jpg') => public_path('packages/adminlte/img/boxed-bg.jpg'),
            base_path('vendor/almasaeed2010/adminlte/dist/img/boxed-bg.png') => public_path('packages/adminlte/img/boxed-bg.png'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/alt/AdminLTE-without-plugins.css') => public_path('packages/adminlte/css/AdminLTE.css'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/alt/AdminLTE-without-plugins.min.css') => public_path('packages/adminlte/css/AdminLTE.min.css'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/skins') => public_path('packages/adminlte/css/skins'),
            // bootstrap
            base_path('vendor/npm-asset/bootstrap/dist') => public_path('packages/cmf-vendors/bootstrap'),
            base_path('vendor/npm-asset/eonasdan-bootstrap-datetimepicker/build') => public_path('packages/cmf-vendors/bootstrap/datetimepicker'),
            base_path('vendor/npm-asset/bootstrap-switch/dist') => public_path('packages/cmf-vendors/bootstrap/switches'),
            base_path('vendor/npm-asset/bootstrap-select/dist') => public_path('packages/cmf-vendors/bootstrap/select'),
            base_path('vendor/npm-asset/ajax-bootstrap-select/dist') => public_path('packages/cmf-vendors/bootstrap/select'),
            base_path('vendor/npm-asset/bootstrap-fileinput/js') => public_path('packages/cmf-vendors/bootstrap/fileinput/js'),
            base_path('vendor/npm-asset/bootstrap-fileinput/css') => public_path('packages/cmf-vendors/bootstrap/fileinput/css'),
            base_path('vendor/npm-asset/bootstrap-fileinput/img') => public_path('packages/cmf-vendors/bootstrap/fileinput/img'),
            // font icons
//            base_path('vendor/npm-asset/font-awesome/css') => public_path('packages/cmf-vendors/fonticons/font-awesome/css'),
//            base_path('vendor/npm-asset/font-awesome/fonts') => public_path('packages/cmf-vendors/fonticons/font-awesome/fonts'),
            base_path('vendor/npm-asset/ionicons/dist/css') => public_path('packages/cmf-vendors/fonticons/ionicons/css'),
            base_path('vendor/npm-asset/ionicons/dist/fonts') => public_path('packages/cmf-vendors/fonticons/ionicons/fonts'),
            // jquery
            base_path('vendor/mistic100/jquery-querybuilder/dist') => public_path('packages/cmf-vendors/db-query-builder'),
            base_path('vendor/npm-asset/jquery/dist/jquery.js') => public_path('packages/cmf-vendors/jquery3/jquery.js'),
            base_path('vendor/npm-asset/jquery/dist/jquery.min.js') => public_path('packages/cmf-vendors/jquery3/jquery.min.js'),
            base_path('vendor/npm-asset/jquery/dist/jquery.min.map') => public_path('packages/cmf-vendors/jquery3/jquery.min.map'),
            base_path('vendor/npm-asset/jquery-form/dist') => public_path('packages/cmf-vendors/jquery-form'),
            // ckeditor - removed due to problems with updates and plugins packs. Currently should be updated manually in packages/cmf-vendors/ckeditor
//            base_path('vendor/ckeditor/ckeditor/ckeditor.js') => public_path('packages/cmf-vendors/ckeditor/ckeditor.js'),
//            base_path('vendor/ckeditor/ckeditor/styles.js') => public_path('packages/cmf-vendors/ckeditor/styles.js'),
//            base_path('vendor/ckeditor/ckeditor/contents.css') => public_path('packages/cmf-vendors/ckeditor/contents.css'),
//            base_path('vendor/ckeditor/ckeditor/adapters') => public_path('packages/cmf-vendors/ckeditor/adapters'),
//            base_path('vendor/ckeditor/ckeditor/lang') => public_path('packages/cmf-vendors/ckeditor/lang'),
//            base_path('vendor/ckeditor/ckeditor/plugins') => public_path('packages/cmf-vendors/ckeditor/plugins'),
//            base_path('vendor/ckeditor/ckeditor/skins') => public_path('packages/cmf-vendors/ckeditor/skins'),
            // libs
            base_path('vendor/npm-asset/simple-scrollbar/simple-scrollbar.css') => public_path('packages/cmf-vendors/scrollbar/simple-scrollbar.css'),
            base_path('vendor/npm-asset/simple-scrollbar/simple-scrollbar.js') => public_path('packages/cmf-vendors/scrollbar/simple-scrollbar.js'),
            base_path('vendor/npm-asset/datatables/media') => public_path('packages/cmf-vendors/datatables'),
            base_path('vendor/npm-asset/toastr/build') => public_path('packages/cmf-vendors/toastr'),
            base_path('vendor/npm-asset/moment/moment.js') => public_path('packages/cmf-vendors/moment/moment.js'),
            base_path('vendor/npm-asset/moment/min/moment.min.js') => public_path('packages/cmf-vendors/moment/moment.min.js'),
            base_path('vendor/npm-asset/moment/locale') => public_path('packages/cmf-vendors/moment/locale'),
            base_path('vendor/npm-asset/moment/locale/en-gb.js') => public_path('packages/cmf-vendors/moment/locale/en.js'),
            base_path('vendor/npm-asset/select2/dist') => public_path('packages/cmf-vendors/select2'),
            base_path('vendor/npm-asset/inputmask/dist/jquery.inputmask.bundle.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.js'),
            base_path('vendor/npm-asset/inputmask/dist/min/jquery.inputmask.bundle.min.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js'),
            base_path('vendor/npm-asset/inputmask/dist/min/jquery.inputmask.bundle.min.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js'),
//            base_path('vendor/npm-asset/cropperjs/dist') => public_path('packages/cmf-vendors/cropperjs'),
            base_path('vendor/npm-asset/sortablejs/Sortable.js') => public_path('packages/cmf-vendors/sortable/Sortable.js'),
            // additions to vendors
            $cmfPublicDir . '/public/cmf-vendors' => public_path('packages/cmf-vendors'),
        ], 'public');

        $this->publishes([
            $this->getConfigFilePath() => config_path('peskycmf.php'),
            $this->getOrmConfigFilePath() => config_path('peskyorm.php'),
            $cmfPublicDir . '/Config/ru_validation_translations.php' => resource_path('lang/ru/validation.php'),
        ], 'config');
    }

    protected function getConfigFilePath() {
        return __DIR__ . '/../Config/peskycmf.config.php';
    }

    protected function getOrmConfigFilePath() {
        return __DIR__ . '/../Config/peskyorm.config.php';
    }

    protected function registerCommands() {
        $this->registerInstallCommand();
        $this->registerInstallHttpRequestsLoggingCommand();
        $this->registerInstallHttpRequestsProfilingCommand();
        $this->registerAddAdminCommand();
        $this->registerMakeScaffoldCommand();
        $this->registerMakeApiMethodDocsCommand();
    }

    protected function registerInstallCommand() {
        $this->app->singleton('command.cmf.install', function() {
            return new CmfInstallCommand();
        });
        $this->commands('command.cmf.install');
    }

    protected function registerAddAdminCommand() {
        $this->app->singleton('command.cmf.add-admin', function() {
            return new CmfAddAdminCommand();
        });
        $this->commands('command.cmf.add-admin');
    }

    protected function registerMakeScaffoldCommand() {
        $this->app->singleton('command.cmf.make-scaffold', function() {
            return new CmfMakeScaffoldCommand();
        });
        $this->commands('command.cmf.make-scaffold');
    }

    protected function registerInstallHttpRequestsLoggingCommand() {
        $this->app->singleton('command.cmf.install-http-requests-logging', function() {
            return new CmfInstallHttpRequestsLoggingCommand();
        });
        $this->commands('command.cmf.install-http-requests-logging');
    }

    protected function registerInstallHttpRequestsProfilingCommand() {
        $this->app->singleton('command.cmf.install-http-requests-profiling', function() {
            return new CmfInstallHttpRequestsProfilingCommand();
        });
        $this->commands('command.cmf.install-http-requests-profiling');
    }

    protected function registerMakeApiMethodDocsCommand() {
        $this->app->singleton('command.cmf.make-api-method-docs', function() {
            return new CmfMakeApiMethodDocCommand();
        });
        $this->commands('command.cmf.make-api-method-docs');
    }

    protected function configureTranslations() {
//        if (!\Lang::has('cmf::test', 'en')) {
            $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cmf');
//        }
    }

    protected function configureViews() {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cmf');
    }

    protected function declareRoutes() {
        $cmfConfig = $this->getCmfConfig();
        $groupConfig = $this->getRoutesGroupConfig();
        if (!$this->app->routesAreCached()) {
            // custom routes
            $files = (array)$cmfConfig::config('routes_files', []);
            if (count($files) > 0) {
                foreach ($files as $filePath) {
                    \Route::group($groupConfig, function () use ($filePath, $cmfConfig) {
                        // warning! $cmfConfig may be used inside included file
                        include base_path($filePath);
                    });
                }
            }
            if (!\Route::has($cmfConfig::getRouteName('cmf_start_page'))) {
                \Route::group($groupConfig, function () use ($cmfConfig) {
                    \Route::get('/', [
                        'uses' => $cmfConfig::cmf_general_controller_class() . '@redirectToUserProfile',
                        'as' => $cmfConfig::getRouteName('cmf_start_page')
                    ]);
                });
            }

            unset($groupConfig['namespace']); //< cmf routes should be able to use controllers from vendors dir
            \Route::group($groupConfig, function () use ($cmfConfig) {
                // warning! $cmfConfig may be used inside included file
                include $this->getCmfRoutesFilePath();
            });

            // special route for ckeditor config.js file
            unset($groupConfig['middleware']);
            \Route::group($groupConfig, function () use ($cmfConfig) {
                \Route::get('ckeditor/config.js', [
                    'as' => $cmfConfig::routes_names_prefix() . 'cmf_ckeditor_config_js',
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getCkeditorConfigJs',
                ]);
            });
        }
    }

    protected function getCmfRoutesFilePath() {
        return __DIR__ . '/../Config/peskycmf.routes.php';
    }

    protected function getRoutesGroupConfig() {
        $config = [
            'prefix' => $this->getCmfConfig()->url_prefix(),
            'middleware' => (array)$this->getCmfConfig()->config('routes_middleware', ['web']),
        ];
        $namespace = $this->getCmfConfig()->config('controllers_namespace');
        if (!empty($namespace)) {
            $config['namespace'] = ltrim($namespace, '\\');
        }
        return $config;
    }

    protected function configureSession() {
        if ($this->app->configurationIsCached()) {
            return;
        }
        $config = $this->getAppConfig()->get('session', []);
        $config['path'] = '/' . trim($this->getCmfConfig()->url_prefix(), '/');
        $this->getAppConfig()->set('session', array_merge($config, (array)$this->getCmfConfig()->config('session', [])));
    }

    protected function configureEventListeners() {
        \Event::listen(AdminAuthenticated::class, AdminAuthenticatedEventListener::class);
    }

    protected function mergeAuthenticationConfigs() {
        // add guard and provider to configs provided by config/auth.php
        if ($this->app->configurationIsCached()) {
            return;
        }

        $cmfAuthConfig = $this->getCmfConfig()->config('auth_guard');
        if (!is_array($cmfAuthConfig)) {
            // custom auth guard name provided
            return;
        }

        $config = $this->getAppConfig()->get('auth', [
            'guards' => [],
            'providers' => [],
        ]);

        $guardName = $this->getCmfConfig()->auth_guard_name();
        if (array_key_exists($guardName, $config['guards'])) {
            throw new \UnexpectedValueException('There is already an auth guard with name "' . $guardName . '"');
        }
        $provider = array_get($cmfAuthConfig, 'provider');
        if (is_array($provider)) {
            $providerName = array_get($provider, 'name', $guardName);
            if (empty($provider['model'])) {
                $provider['model'] = app(CmfAdmin::class);
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

        $this->getAppConfig()->set('auth', $config);
    }

    protected function configureDefaultAuthGuard() {
        \Auth::shouldUse($this->getCmfConfig()->auth_guard_name());
    }

    protected function configureAuthorizationGatesAndPolicies($policyName = 'CmfAccessPolicy') {
        $this->getCmfConfig()->configureAuthorizationGatesAndPolicies($policyName);
    }

}