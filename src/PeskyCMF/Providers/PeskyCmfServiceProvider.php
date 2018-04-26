<?php

namespace PeskyCMF\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Console\Commands\CmfAddAdminCommand;
use PeskyCMF\Console\Commands\CmfAddSectionCommand;
use PeskyCMF\Console\Commands\CmfInstallCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsLoggingCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsProfilingCommand;
use PeskyCMF\Console\Commands\CmfMakeApiDocCommand;
use PeskyCMF\Console\Commands\CmfMakeApiMethodDocCommand;
use PeskyCMF\Console\Commands\CmfMakeScaffoldCommand;
use PeskyCMF\Facades\PeskyCmf;
use PeskyCMF\Http\Middleware\UseCmfSection;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\PeskyCmfManager;
use PeskyORM\ORM\TableInterface;
use PeskyORM\ORM\TableStructureInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Vluzrmos\LanguageDetector\Facades\LanguageDetector;
use Illuminate\Foundation\Application;

/**
 * @property Application $app
 */
class PeskyCmfServiceProvider extends ServiceProvider {

    public function register() {
        $this->mergeConfigFrom($this->getConfigFilePath(), 'peskycmf');

        $this->app->singleton(PeskyCmfManager::class, function ($app) {
            return new PeskyCmfManager($app);
        });

        $this->app->register(PeskyCmfPeskyOrmServiceProvider::class);
        AliasLoader::getInstance()->alias('LanguageDetector', LanguageDetector::class);
        AliasLoader::getInstance()->alias('PeskyCmf', PeskyCmf::class);

        if ($this->app->environment() === 'local') {
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            }
            if (class_exists('\Barryvdh\Debugbar\ServiceProvider')) {
                $this->app->register('\Barryvdh\Debugbar\ServiceProvider');
            }
        }

        $this->registerCommands();

        $this->app->singleton(PeskyCmfAppSettings::class, function ($app) {
            /** @var PeskyCmfManager $cmfManager */
            $cmfManager = $app[PeskyCmfManager::class];
            return $cmfManager->getCmfConfigForSection()->config('app_settings_class') ?: PeskyCmfAppSettings::class;
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

        $this->declareRoutesForAllCmfSections();
    }

    /**
     * Overwrite this method in your custom service provider if you do want or do not want to run
     * console-specific methods in register() and boot() methods
     * @return bool
     */
    protected function runningInConsole() {
        return $this->app->runningInConsole();
    }

    /**
     * @return ParameterBag
     */
    protected function getAppConfigs() {
        return $this->app['config'];
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
        $this->registerAddSectionCommand();
        $this->registerInstallHttpRequestsLoggingCommand();
        $this->registerInstallHttpRequestsProfilingCommand();
        $this->registerAddAdminCommand();
        $this->registerMakeScaffoldCommand();
        $this->registerMakeApiDocsCommands();
    }

    protected function registerInstallCommand() {
        $this->app->singleton('command.cmf.install', function() {
            return new CmfInstallCommand();
        });
        $this->commands('command.cmf.install');
    }

    protected function registerAddSectionCommand() {
        $this->app->singleton('command.cmf.add-section', function() {
            return new CmfAddSectionCommand();
        });
        $this->commands('command.cmf.add-section');
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

    protected function registerMakeApiDocsCommands() {
        $this->app->singleton('command.cmf.make-api-docs', function() {
            return new CmfMakeApiDocCommand();
        });
        $this->commands('command.cmf.make-api-docs');

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

    /**
     * @return CmfConfig[]
     */
    protected function getAvailableCmfConfigs() {
        $cmfConfigsClasses = $this->getAppConfigs()->get('peskycmf.cmf_configs');
        $configs = [];
        /** @var CmfConfig $className */
        foreach ($cmfConfigsClasses as $sectionName => $className) {
            if (class_exists($className) && is_subclass_of($className, CmfConfig::class)) {
                $configs[$sectionName] = $className::getInstance();
            }
        }
        return $configs;
    }

    protected function declareRoutesForAllCmfSections() {
        foreach ($this->getAvailableCmfConfigs() as $sectionName => $cmfConfig) {
            if (!$this->app->routesAreCached()) {
                $groupConfig = $this->getRoutesGroupConfig($cmfConfig, $sectionName);
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

                unset($groupConfig['namespace']); //< cmf routes should be able to use controllers from vendors dir
                if (!\Route::has($cmfConfig::getRouteName('cmf_start_page'))) {
                    \Route::group($groupConfig, function () use ($cmfConfig) {
                        \Route::get('/', [
                            'uses' => $cmfConfig::cmf_general_controller_class() . '@redirectToUserProfile',
                            'as' => $cmfConfig::getRouteName('cmf_start_page'),
                        ]);
                    });
                }

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
    }

    protected function getCmfRoutesFilePath() {
        return __DIR__ . '/../Config/peskycmf.routes.php';
    }

    protected function getRoutesGroupConfig(CmfConfig $cmfConfig, $sectionName) {
        $config = [
            'prefix' => $cmfConfig::url_prefix(),
            'middleware' => (array)$cmfConfig::config('routes_middleware', ['web']),
        ];
        array_unshift($config['middleware'], UseCmfSection::class . ':' . $sectionName);
        $namespace = $cmfConfig::config('controllers_namespace');
        if (!empty($namespace)) {
            $config['namespace'] = ltrim($namespace, '\\');
        }
        return $config;
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

}