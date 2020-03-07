<?php

namespace PeskyCMF\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Console\Commands\CmfAddAdminCommand;
use PeskyCMF\Console\Commands\CmfAddSectionCommand;
use PeskyCMF\Console\Commands\CmfCleanUploadedTempFilesCommand;
use PeskyCMF\Console\Commands\CmfInstallCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsLoggingCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsProfilingCommand;
use PeskyCMF\Console\Commands\CmfMakeApiDocCommand;
use PeskyCMF\Console\Commands\CmfMakeApiMethodDocCommand;
use PeskyCMF\Console\Commands\CmfMakeScaffoldCommand;
use PeskyCMF\Facades\PeskyCmf;
use PeskyCMF\PeskyCmfAppSettings;
use PeskyCMF\PeskyCmfManager;
use PeskyORM\ORM\TableInterface;
use PeskyORM\ORM\TableStructureInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Vluzrmos\LanguageDetector\Facades\LanguageDetector;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

/**
 * @property Application $app
 */
class PeskyCmfServiceProvider extends ServiceProvider {

    public function register() {
        $this->mergeConfigFrom($this->getConfigFilePath(), 'peskycmf');

        $this->setDefaultCmfConfig();

        $this->app->singleton(PeskyCmfManager::class, function ($app) {
            return new PeskyCmfManager($app);
        });

        $this->app->register(PeskyCmfPeskyOrmServiceProvider::class);
        $this->app->register(RecaptchaServiceProvider::class);
        AliasLoader::getInstance()->alias('LanguageDetector', LanguageDetector::class);
        AliasLoader::getInstance()->alias('PeskyCmf', PeskyCmf::class);

        if ($this->runningInConsole()) {
            $this->app->register(PeskyCmfLanguageDetectorServiceProvider::class);
            $this->app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        }

        if ($this->app->environment() === 'local') {
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            }
            if (class_exists('\Barryvdh\Debugbar\ServiceProvider')) {
                $this->app->register('\Barryvdh\Debugbar\ServiceProvider');
            }
        }

        $this->registerCommands();

        $this->app->singleton(PeskyCmfAppSettings::class, function () {
            /** @var PeskyCmfAppSettings $appSettingsClass */
            $appSettingsClass = $this->getAppConfigs()->get('peskycmf.app_settings_class') ?: PeskyCmfAppSettings::class;
            return $appSettingsClass::getInstance();
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

        $this->declareRoutesAndConfigsForAllCmfSections();

        $this->scheduleCommands();
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
        $cmfLibDir = __DIR__ . '/..';
        // publish cmf assets (minified)
        $cmfNpmDistDir = $cmfLibDir . '/../../npm/dist';
        $cmfAssets = [
            $cmfNpmDistDir . '/min' => public_path('packages/cmf/min'),
            $cmfNpmDistDir . '/packed' => public_path('packages/cmf/packed'),
            $cmfNpmDistDir . '/raw' => public_path('packages/cmf/raw'),
        ];
        if (stripos(config('peskycmf.assets'), 'src') === 0) {
            $cmfAssets[$cmfNpmDistDir . '/src'] = public_path('packages/cmf/src');
        }
        $this->publishes($cmfAssets, 'public');

        $this->publishes([
            $this->getConfigFilePath() => config_path('peskycmf.php'),
            $this->getOrmConfigFilePath() => config_path('peskyorm.php'),
            $cmfLibDir . '/Config/ru_validation_translations.php' => resource_path('lang/ru/validation.php'),
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
        $this->registerCleanUploadedTempFilesCommand();
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

    protected function registerCleanUploadedTempFilesCommand() {
        $this->app->singleton('command.cmf.clean-uploaded-temp-files', function () {
            return new CmfCleanUploadedTempFilesCommand();
        });
        $this->commands('command.cmf.clean-uploaded-temp-files');
    }

    protected function scheduleCommands() {
        if ($this->runningInConsole()) {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('cmf:clean-uploaded-temp-files')
                ->dailyAt('06:00');
        }
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

    protected function declareRoutesAndConfigsForAllCmfSections() {
        foreach ($this->getAvailableCmfConfigs() as $sectionName => $cmfConfig) {
            if (!$this->app->routesAreCached()) {
                $cmfConfig::getInstance()->declareRoutes($sectionName);
            }
            if (!$this->app->configurationIsCached()) {
                $cmfConfig::getInstance()->updateAppConfigs($this->app);
            }
        }
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

    protected function setDefaultCmfConfig() {
        $defaultConfig = $this->getAppConfigs()->get('peskycmf.default_cmf_config');
        if ($defaultConfig) {
            /** @var CmfConfig $className */
            $className = $this->getAppConfigs()->get('peskycmf.cmf_configs.' . $defaultConfig);
            if (!empty($className) && class_exists($className)) {
                $className::getInstance()->useAsDefault();
                $this->app->singleton(CmfConfig::class, function () {
                    return CmfConfig::getDefault();
                });
            }
        }
    }

}
