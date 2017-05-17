<?php

namespace PeskyCMF\Providers;

use LaravelSiteLoader\DummySiteLoader;
use LaravelSiteLoader\Providers\AppSitesServiceProvider;
use PeskyCMF\CMS\CmsFrontendUtils;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Console\Commands\CmfAddAdmin;
use PeskyCMF\Console\Commands\CmfInstall;
use PeskyCMF\Console\Commands\CmfMakeDbClasses;
use PeskyCMF\Console\Commands\CmfMakeScaffold;
use PeskyCMF\Console\Commands\CmsInstall;
use PeskyCMF\Http\PeskyCmfSiteLoader;

class PeskyCmfServiceProvider extends AppSitesServiceProvider {

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app) {
        $this->defaultSiteLoaderClass = config('cmf.default_site_loader');
        $this->consoleSiteLoaderClass = config('cmf.console_site_loader');
        $this->additionalSiteLoaderClasses = (array)config('cmf.additional_site_loaders', []);

        parent::__construct($app);
    }

    public function boot() {
        if (config('cmf.file_access_mask') !== null) {
            umask(config('cmf.file_access_mask'));
        }
        CmsFrontendUtils::registerBladeDirectiveForStringTemplateRendering();
        $this->configurePublishes();
        /** @var CmfConfig|string $defaultCmfConfig */
        $defaultCmfConfig = config('cmf.default_cmf_config');
        if (!empty($defaultCmfConfig)) {
            $defaultCmfConfig::getInstance()->useAsDefault();
        }
        require_once __DIR__ . '/../Config/helpers.php';
        parent::boot();
        $this->configureDefaultCmfTranslations();
    }

    public function register() {
        parent::register();
        $this->app->register(PeskyOrmServiceProvider::class);
        $this->app->register(PeskyValidationServiceProvider::class);
        $this->app->register(SuppressLaravelDatabaseServiceProvider::class);

        if ($this->app->environment() === 'local') {
            if (class_exists('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider')) {
                $this->app->register('\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            }
            if (class_exists('\Barryvdh\Debugbar\ServiceProvider')) {
                $this->app->register('\Barryvdh\Debugbar\ServiceProvider');
            }
        }

        $this->registerCommands();
    }

    protected function configurePublishes() {
        $cmfPublicDir = __DIR__ . '/..';
        $this->publishes([
            // cmf
            $cmfPublicDir . '/public/css' => public_path('packages/cmf/css'),
            $cmfPublicDir . '/public/js' => public_path('packages/cmf/js'),
            $cmfPublicDir . '/public/less' => public_path('packages/cmf/less'),
            $cmfPublicDir . '/public/img' => public_path('packages/cmf/img'),
            // AdminLTE
            base_path('vendor/almasaeed2010/adminlte/dist/js/app.js') => public_path('packages/adminlte/js/app.js'),
            base_path('vendor/almasaeed2010/adminlte/dist/js/app.min.js') => public_path('packages/adminlte/js/app.min.js'),
            base_path('vendor/almasaeed2010/adminlte/dist/img/boxed-bg.jpg') => public_path('packages/adminlte/img/boxed-bg.jpg'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/AdminLTE.css') => public_path('packages/adminlte/css/AdminLTE.css'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/AdminLTE.min.css') => public_path('packages/adminlte/css/AdminLTE.min.css'),
            base_path('vendor/almasaeed2010/adminlte/dist/css/skins') => public_path('packages/adminlte/css/skins'),
            base_path('vendor/almasaeed2010/adminlte/plugins/fastclick/fastclick.min.js') => public_path('packages/adminlte/plugins/fastclick/fastclick.min.js'),
//            base_path('vendor/almasaeed2010/adminlte/plugins') => public_path('packages/adminlte/plugins'),
            // bootstrap
            base_path('vendor/twbs/bootstrap/dist') => public_path('packages/cmf-vendors/bootstrap'),
            base_path('vendor/eonasdan/bootstrap-datetimepicker/build') => public_path('packages/cmf-vendors/bootstrap/datetimepicker'),
            base_path('vendor/nostalgiaz/bootstrap-switch/dist') => public_path('packages/cmf-vendors/bootstrap/switches'),
            base_path('vendor/bootstrap-select/bootstrap-select/dist') => public_path('packages/cmf-vendors/bootstrap/select'),
            base_path('vendor/vitalets/x-editable/dist/bootstrap3-editable') => public_path('packages/cmf-vendors/bootstrap/x-editale'),
            base_path('vendor/kartik-v/bootstrap-fileinput/js') => public_path('packages/cmf-vendors/bootstrap/fileinput/js'),
            base_path('vendor/kartik-v/bootstrap-fileinput/css') => public_path('packages/cmf-vendors/bootstrap/fileinput/css'),
            base_path('vendor/kartik-v/bootstrap-fileinput/img') => public_path('packages/cmf-vendors/bootstrap/fileinput/img'),
            // font icons
            base_path('vendor/fortawesome/font-awesome/css') => public_path('packages/cmf-vendors/fontions/font-awesome/css'),
            base_path('vendor/fortawesome/font-awesome/fonts') => public_path('packages/cmf-vendors/fontions/font-awesome/fonts'),
            base_path('vendor/driftyco/ionicons/css') => public_path('packages/cmf-vendors/fontions/ionicons/css'),
            base_path('vendor/driftyco/ionicons/fonts') => public_path('packages/cmf-vendors/fontions/ionicons/fonts'),
            // jquery
            base_path('vendor/components/jquery/jquery.js') => public_path('packages/cmf-vendors/jquery/jquery.js'),
            base_path('vendor/components/jquery/jquery.min.js') => public_path('packages/cmf-vendors/jquery/jquery.min.js'),
            base_path('vendor/components/jquery/jquery.min.map') => public_path('packages/cmf-vendors/jquery/jquery.min.map'),
            base_path('vendor/bower-asset/jquery/dist/jquery.js') => public_path('packages/cmf-vendors/jquery3/jquery.js'),
            base_path('vendor/bower-asset/jquery/dist/jquery.min.js') => public_path('packages/cmf-vendors/jquery3/jquery.min.js'),
            base_path('vendor/bower-asset/jquery/dist/jquery.min.map') => public_path('packages/cmf-vendors/jquery3/jquery.min.map'),
            base_path('vendor/jquery-form/form/dist') => public_path('packages/cmf-vendors/jquery-form'),
            base_path('vendor/mistic100/jquery-querybuilder/dist') => public_path('packages/cmf-vendors/db-query-builder'),
            // ckeditor
//            base_path('vendor/ckeditor/ckeditor/ckeditor.js') => public_path('packages/cmf-vendors/ckeditor/ckeditor.js'),
//            base_path('vendor/ckeditor/ckeditor/styles.js') => public_path('packages/cmf-vendors/ckeditor/styles.js'),
//            base_path('vendor/ckeditor/ckeditor/contents.css') => public_path('packages/cmf-vendors/ckeditor/contents.css'),
//            base_path('vendor/ckeditor/ckeditor/adapters') => public_path('packages/cmf-vendors/ckeditor/adapters'),
//            base_path('vendor/ckeditor/ckeditor/lang') => public_path('packages/cmf-vendors/ckeditor/lang'),
//            base_path('vendor/ckeditor/ckeditor/plugins') => public_path('packages/cmf-vendors/ckeditor/plugins'),
//            base_path('vendor/ckeditor/ckeditor/skins') => public_path('packages/cmf-vendors/ckeditor/skins'),
            // libs
            base_path('vendor/npm-asset/page/page.js') => public_path('packages/cmf-vendors/router/page.js'),
            base_path('vendor/datatables/datatables/media') => public_path('packages/cmf-vendors/datatables'),
            base_path('vendor/grimmlink/toastr/build') => public_path('packages/cmf-vendors/toastr'),
            base_path('vendor/moment/moment/moment.js') => public_path('packages/cmf-vendors/moment/moment.js'),
            base_path('vendor/moment/moment/min/moment.min.js') => public_path('packages/cmf-vendors/moment/moment.min.js'),
            base_path('vendor/moment/moment/locale') => public_path('packages/cmf-vendors/moment/locale'),
            base_path('vendor/moment/moment/locale/en-gb.js') => public_path('packages/cmf-vendors/moment/locale/en.js'),
            base_path('vendor/afarkas/html5shiv/dist/html5shiv.min.js') => public_path('packages/cmf-vendors/html5shiv.min.js'),
            base_path('vendor/select2/select2/dist') => public_path('packages/cmf-vendors/select2'),
            base_path('vendor/robinherbots/jquery.inputmask/dist/jquery.inputmask.bundle.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.js'),
            base_path('vendor/robinherbots/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js'),
            base_path('vendor/robinherbots/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js') => public_path('packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js'),
            base_path('vendor/bower-asset/cropper/dist') => public_path('packages/cmf-vendors/cropperjs'),
            // additions to vendors
            $cmfPublicDir . '/public/cmf-vendors' => public_path('packages/cmf-vendors'),

        ], 'public');

        $this->publishes([
            $cmfPublicDir . '/Config/cmf.config.php' => config_path('cmf.php'),
            $cmfPublicDir . '/Config/ru_validation_translations.php' => resource_path('lang/ru/validation.php'),
        ], 'config');
    }

    protected function registerCommands() {
        $this->registerInstallCommand();
        $this->registerAddAdminCommand();
        $this->registerMakeDbClassesCommand();
        $this->registerMakeScaffoldCommand();
        $this->registerCmsInstallCommand();
    }

    protected function registerInstallCommand() {
        $this->app->singleton('command.cmf.install', function() {
            return new CmfInstall();
        });
        $this->commands('command.cmf.install');
    }

    protected function registerAddAdminCommand() {
        $this->app->singleton('command.cmf.add-admin', function() {
            return new CmfAddAdmin();
        });
        $this->commands('command.cmf.add-admin');
    }

    protected function registerMakeDbClassesCommand() {
        $this->app->singleton('command.cmf.make-db-classes', function() {
            return new CmfMakeDbClasses();
        });
        $this->commands('command.cmf.make-db-classes');
    }

    protected function registerMakeScaffoldCommand() {
        $this->app->singleton('command.cmf.make-scaffold', function() {
            return new CmfMakeScaffold();
        });
        $this->commands('command.cmf.make-scaffold');
    }

    protected function registerCmsInstallCommand() {
        $this->app->singleton('command.cms.install', function() {
            return new CmsInstall();
        });
        $this->commands('command.cms.install');
    }

    protected function configureDefaultCmfTranslations() {
        if (!\Lang::has('cmf::test', 'en')) {
            $this->loadTranslationsFrom(CmfConfig::cmf_dictionaries_path(), 'cmf');
        }
    }


}