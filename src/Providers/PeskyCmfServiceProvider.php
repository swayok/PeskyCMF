<?php

declare(strict_types=1);

namespace PeskyCMF\Providers;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\CmfManager;
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
use PeskyCMF\PeskyCmfAppSettings;

class PeskyCmfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerCmfBindings();

        $this->registerRelatedServiceProviders();

        $this->registerFacades();
        $this->registerCommands();
    }

    public function boot(): void
    {
        require_once __DIR__ . '/../Config/helpers.php';

        $this->mergeConfigFrom($this->getConfigFilePath(), 'peskycmf');

        $this->configurePublishes();

        $this->configureTranslations();
        $this->configureViews();

        $this->declareRoutesAndConfigsForAllCmfSections();

        $this->scheduleCommands();
    }

    protected function registerCmfBindings(): void
    {
        $this->app->singleton(CmfManager::class, function ($app) {
            return new CmfManager($app);
        });
        $this->app->alias(CmfManager::class, 'cmf.manager');
        $this->app->singleton(CmfConfig::class, function (Application $app) {
            return $app->make(CmfManager::class)->getCmfConfigForSection(null);
        });

        $this->app->singleton(
            PeskyCmfAppSettings::class,
            $this->getAppConfigs()->get('peskycmf.app_settings_class')
        );
    }

    protected function registerRelatedServiceProviders(): void
    {
        $this->app->register(PeskyCmfPeskyOrmServiceProvider::class);
        $this->app->register(RecaptchaServiceProvider::class);
    }

    protected function registerFacades(): void
    {
        AliasLoader::getInstance()->alias(
            'CmfManager',
            \PeskyCMF\Facades\CmfManager::class
        );
    }

    protected function runningInConsole(): bool
    {
        return $this->app->runningInConsole();
    }

    protected function getAppConfigs(): ConfigRepository
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
        $cmfNpmDistDir = $cmfLibDir . '/../npm/dist';
        $cmfAssets = [
            $cmfNpmDistDir . '/min' => public_path('vendor/peskycmf/min'),
            $cmfNpmDistDir . '/packed' => public_path('vendor/peskycmf/packed'),
            $cmfNpmDistDir . '/raw' => public_path('vendor/peskycmf/raw'),
        ];
        if (stripos(config('peskycmf.assets'), 'src') === 0) {
            $cmfAssets[$cmfNpmDistDir . '/src'] = public_path('vendor/peskycmf/src');
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
        $this->app->singleton('command.cmf.install', function (Application $app) {
            return new CmfInstallCommand($app);
        });
        $this->commands('command.cmf.install');
    }

    protected function registerAddSectionCommand(): void
    {
        $this->app->singleton('command.cmf.add-section', function (Application $app) {
            return new CmfAddSectionCommand($app);
        });
        $this->commands('command.cmf.add-section');
    }

    protected function registerAddAdminCommand(): void
    {
        $this->app->singleton('command.cmf.add-admin', function (Application $app) {
            return new CmfAddAdminCommand($app);
        });
        $this->commands('command.cmf.add-admin');
    }

    protected function registerMakeScaffoldCommand(): void
    {
        $this->app->singleton('command.cmf.make-scaffold', function (Application $app) {
            return new CmfMakeScaffoldCommand($app);
        });
        $this->commands('command.cmf.make-scaffold');
    }

    protected function registerInstallHttpRequestsLoggingCommand(): void
    {
        $this->app->singleton('command.cmf.install-http-requests-logging', function (Application $app) {
            return new CmfInstallHttpRequestsLoggingCommand($app);
        });
        $this->commands('command.cmf.install-http-requests-logging');
    }

    protected function registerInstallHttpRequestsProfilingCommand(): void
    {
        $this->app->singleton('command.cmf.install-http-requests-profiling', function (Application $app) {
            return new CmfInstallHttpRequestsProfilingCommand($app);
        });
        $this->commands('command.cmf.install-http-requests-profiling');
    }

    protected function registerMakeApiDocsCommands(): void
    {
        $this->app->singleton('command.cmf.make-api-docs', function (Application $app) {
            return new CmfMakeApiDocCommand($app);
        });
        $this->commands('command.cmf.make-api-docs');

        $this->app->singleton('command.cmf.make-api-method-docs', function (Application $app) {
            return new CmfMakeApiMethodDocCommand($app);
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
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cmf');
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
