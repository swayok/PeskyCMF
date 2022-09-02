<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\CmfManager;
use PeskyCMF\Console\Commands\CmfAddAdminCommand;
use PeskyCMF\Console\Commands\CmfAddSectionCommand;
use PeskyCMF\Console\Commands\CmfCleanUploadedTempFilesCommand;
use PeskyCMF\Console\Commands\CmfInstallCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsLoggingCommand;
use PeskyCMF\Console\Commands\CmfInstallHttpRequestsProfilingCommand;
use PeskyCMF\Console\Commands\CmfMakeApiDocCommand;
use PeskyCMF\Console\Commands\CmfMakeApiMethodDocCommand;
use PeskyCMF\Console\Commands\CmfMakeScaffoldCommand;
use PeskyCMF\Db\Settings\CmfSettingsTable;
use PeskyCMF\Db\Settings\CmfSettingsTableStructure;
use PeskyCMF\PeskyCmfAppSettings;
use Symfony\Component\HttpFoundation\ParameterBag;
use Vluzrmos\LanguageDetector\Facades\LanguageDetector;
use Vluzrmos\LanguageDetector\Providers\LanguageDetectorServiceProvider;

class PeskyCmfServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigFilePath(), 'peskycmf');
        
        $this->registerCmfInjections();
        
        $this->registerRelatedServiceProviders();
        $this->registerServiceProvidersForConsole();
        $this->registerServiceProvidersForLocalDevelopment();
        
        $this->registerFacades();
        
        $this->registerCommands();
    }
    
    public function provides(): array
    {
        return [
            CmfManager::class,
            'cmf.manager',
            PeskyCmfAppSettings::class,
        ];
    }
    
    public function boot(): void
    {
        require_once __DIR__ . '/../Config/helpers.php';
        
        $this->configurePublishes();
        
        $this->configureTranslations();
        $this->configureViews();
        
        $this->declareRoutesAndConfigsForAllCmfSections();
        
        $this->scheduleCommands();
    }
    
    protected function registerCmfInjections(): void
    {
        $this->app->singleton(CmfManager::class, function ($app) {
            return new CmfManager($app);
        });
        $this->app->alias(CmfManager::class, 'cmf.manager');
    
        $this->app->singleton(PeskyCmfAppSettings::class, function () {
            /** @var PeskyCmfAppSettings $appSettingsClass */
            $appSettingsClass = $this->getAppConfigs()->get('peskycmf.app_settings_class') ?: PeskyCmfAppSettings::class;
            return new $appSettingsClass($this->app);
        });
    
        $this->app->singleton(CmfSettingsTable::class, function () {
            return CmfSettingsTable::getInstance();
        });
    
        $this->app->singleton(CmfSettingsTableStructure::class, function () {
            return CmfSettingsTableStructure::getInstance();
        });
    }
    
    protected function registerRelatedServiceProviders(): void
    {
        $this->app->register(PeskyCmfPeskyOrmServiceProvider::class);
        $this->app->register(RecaptchaServiceProvider::class);
    }
    
    protected function registerServiceProvidersForConsole(): void
    {
        if ($this->runningInConsole()) {
            $this->app->register(PeskyCmfLanguageDetectorServiceProvider::class);
            $this->app->alias(LanguageDetectorServiceProvider::class, PeskyCmfLanguageDetectorServiceProvider::class);
        }
    }
    
    protected function registerServiceProvidersForLocalDevelopment(): void
    {
        if ($this->app->environment('local')) {
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            }
            if (class_exists('\Barryvdh\Debugbar\ServiceProvider')) {
                $this->app->register('\Barryvdh\Debugbar\ServiceProvider');
            }
        }
    }
    
    protected function registerFacades(): void
    {
        AliasLoader::getInstance()->alias('LanguageDetector', LanguageDetector::class);
        AliasLoader::getInstance()->alias('CmfManager', \PeskyCMF\Facades\CmfManager::class);
    }
    
    protected function runningInConsole(): bool
    {
        return $this->app->runningInConsole();
    }
    
    /**
     * @return ParameterBag
     */
    protected function getAppConfigs(): ParameterBag
    {
        return $this->app['config'];
    }
    
    protected function configurePublishes(): void
    {
        if (!$this->runningInConsole()) {
            return;
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
    
    protected function getConfigFilePath(): string
    {
        return __DIR__ . '/../Config/peskycmf.config.php';
    }
    
    protected function getOrmConfigFilePath(): string
    {
        return __DIR__ . '/../Config/peskyorm.config.php';
    }
    
    protected function registerCommands(): void
    {
        $this->registerInstallCommand();
        $this->registerAddSectionCommand();
        $this->registerInstallHttpRequestsLoggingCommand();
        $this->registerInstallHttpRequestsProfilingCommand();
        $this->registerAddAdminCommand();
        $this->registerMakeScaffoldCommand();
        $this->registerMakeApiDocsCommands();
        $this->registerCleanUploadedTempFilesCommand();
    }
    
    protected function registerInstallCommand(): void
    {
        $this->app->singleton('command.cmf.install', function () {
            return new CmfInstallCommand();
        });
        $this->commands('command.cmf.install');
    }
    
    protected function registerAddSectionCommand(): void
    {
        $this->app->singleton('command.cmf.add-section', function () {
            return new CmfAddSectionCommand();
        });
        $this->commands('command.cmf.add-section');
    }
    
    protected function registerAddAdminCommand(): void
    {
        $this->app->singleton('command.cmf.add-admin', function () {
            return new CmfAddAdminCommand();
        });
        $this->commands('command.cmf.add-admin');
    }
    
    protected function registerMakeScaffoldCommand(): void
    {
        $this->app->singleton('command.cmf.make-scaffold', function () {
            return new CmfMakeScaffoldCommand();
        });
        $this->commands('command.cmf.make-scaffold');
    }
    
    protected function registerInstallHttpRequestsLoggingCommand(): void
    {
        $this->app->singleton('command.cmf.install-http-requests-logging', function () {
            return new CmfInstallHttpRequestsLoggingCommand();
        });
        $this->commands('command.cmf.install-http-requests-logging');
    }
    
    protected function registerInstallHttpRequestsProfilingCommand(): void
    {
        $this->app->singleton('command.cmf.install-http-requests-profiling', function () {
            return new CmfInstallHttpRequestsProfilingCommand();
        });
        $this->commands('command.cmf.install-http-requests-profiling');
    }
    
    protected function registerMakeApiDocsCommands(): void
    {
        $this->app->singleton('command.cmf.make-api-docs', function () {
            return new CmfMakeApiDocCommand();
        });
        $this->commands('command.cmf.make-api-docs');
        
        $this->app->singleton('command.cmf.make-api-method-docs', function () {
            return new CmfMakeApiMethodDocCommand();
        });
        $this->commands('command.cmf.make-api-method-docs');
    }
    
    protected function registerCleanUploadedTempFilesCommand(): void
    {
        $this->app->singleton('command.cmf.clean-uploaded-temp-files', function () {
            return new CmfCleanUploadedTempFilesCommand();
        });
        $this->commands('command.cmf.clean-uploaded-temp-files');
    }
    
    protected function scheduleCommands(): void
    {
        if ($this->runningInConsole()) {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('cmf:clean-uploaded-temp-files')
                ->dailyAt('06:00');
        }
    }
    
    protected function configureTranslations(): void
    {
//        if (!\Lang::has('cmf::test', 'en')) {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cmf');
//        }
    }
    
    protected function configureViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cmf');
    }
    
    protected function declareRoutesAndConfigsForAllCmfSections(): void
    {
        /** @var CmfManager $manager */
        $manager = $this->app->make(CmfManager::class);
        $manager->registerRoutesForAllCmfSections();
        $manager->extendLaravelAppConfigsForAllCmfSections();
    }
    
}
