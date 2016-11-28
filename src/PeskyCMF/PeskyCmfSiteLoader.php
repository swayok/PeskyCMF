<?php

namespace PeskyCMF;

use LaravelSiteLoader\AppSiteLoader;
use LaravelSiteLoader\Providers\AppSitesServiceProvider;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthorised;
use PeskyCMF\Listeners\AdminAuthorisedEventListener;
use PeskyORM\ORM\Table;
use Swayok\Utils\File;

abstract class PeskyCmfSiteLoader extends AppSiteLoader {

    /** @var CmfConfig */
    static protected $cmfConfig;
    /** @var string */
    static protected $cmfConfigsClass = CmfConfig::class;
    /** @var bool */
    protected $sendConfigsToLaravelContainer = false;

    /**
     * @return CmfConfig
     */
    static public function getCmfConfig() {
        if (static::$cmfConfig === null) {
            static::$cmfConfig = new static::$cmfConfigsClass;
        }
        return static::$cmfConfig;
    }

    static public function getBaseUrl() {
        return '/' . trim(static::getCmfConfig()->url_prefix(), '/');
    }

    public function boot() {

        static::getCmfConfig()->replaceConfigInstance(CmfConfig::class, static::getCmfConfig());

        $this->loadConfigs();
        // alter auth config
        $this->configureAuth();
        // alter session config
        $this->configureSession();
        // custom configurations
        $this->configure();
        $this->configureLocale();
        $this->includeFiles();
        $this->storeConfigsInLaravelContainer();

        $this->configureViewsLoading();
        $this->configureTranslationsLoading();

        $this->configureEventListeners();
    }

    /**
     * Overwrite this method if you need to load some custom config files, app configs, etc
     */
    protected function loadConfigs() {

    }

    /**
     * Custom configurations
     */
    protected function configure() {

    }

    public function register() {
        $this->app->singleton(CmfConfig::class, function () {
            return static::getCmfConfig();
        });
        $this->app->singleton(\PeskyCMF\Http\Request::class, function () {
            return new \PeskyCMF\Http\Request(request());
        });
        $this->app->singleton(Table::class, function () {
            return CmfConfig::getInstance()->base_db_table_class();
        });
    }

    public function provides() {
        return [
            CmfConfig::class,
            Table::class
        ];
    }

    protected function includeFiles() {
        require_once __DIR__ . '/Config/helpers.php';
    }

    protected function storeConfigsInLaravelContainer() {
        if ($this->sendConfigsToLaravelContainer) {
            $key = 'cmf';
            $config = $this->getAppConfig()->get($key, []);
            $this->getAppConfig()->set($key, array_merge($config, static::getCmfConfig()->toArray()));
        }
    }

    protected function configureAuth() {
        $config = $this->getAppConfig()->get('auth', []);
        $this->getAppConfig()->set('auth', array_replace_recursive($config, static::getCmfConfig()->auth_configs()));
        \Auth::shouldUse(static::getCmfConfig()->auth_guard_name());
    }

    public function configureSession($connection = null, $lifetime = null) {
        $config = $this->getAppConfig()->get('session', []);
        $config['path'] = static::getBaseUrl();
        $this->getAppConfig()->set('session', array_merge($config, static::getCmfConfig()->session_configs()));
    }

    protected function configureViewsLoading() {
        $this->provider->loadViewsFrom(static::getCmfConfig()->views_path(), 'cmf');
    }

    protected function configureTranslationsLoading() {
        $this->provider->loadTranslationsFrom(static::getCmfConfig()->cmf_dictionaries_path(), 'cmf');
    }

    static public function configurePublishes(AppSitesServiceProvider $provider) {
        $provider->publishes([
            // cmf
            __DIR__ . '/public/css' => public_path('packages/cmf/css'),
            __DIR__ . '/public/js' => public_path('packages/cmf/js'),
            __DIR__ . '/public/less' => public_path('packages/cmf/less'),
            __DIR__ . '/public/img' => public_path('packages/cmf/img'),
            __DIR__ . '/public/cmf-vendors' => public_path('packages/cmf-vendors'),
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
            // font icons
            base_path('vendor/fortawesome/font-awesome/css') => public_path('packages/cmf-vendors/fontions/font-awesome/css'),
            base_path('vendor/fortawesome/font-awesome/fonts') => public_path('packages/cmf-vendors/fontions/font-awesome/fonts'),
            base_path('vendor/driftyco/ionicons/css') => public_path('packages/cmf-vendors/fontions/ionicons/css'),
            base_path('vendor/driftyco/ionicons/fonts') => public_path('packages/cmf-vendors/fontions/ionicons/fonts'),
            // jquery
            base_path('vendor/components/jquery/jquery.js') => public_path('packages/cmf-vendors/jquery/jquery.js'),
            base_path('vendor/components/jquery/jquery.min.js') => public_path('packages/cmf-vendors/jquery/jquery.min.js'),
            base_path('vendor/components/jquery/jquery.min.map') => public_path('packages/cmf-vendors/jquery/jquery.min.map'),
            base_path('vendor/malsup/form/jquery.form.js') => public_path('packages/cmf-vendors/jquery.form.js'),
            base_path('vendor/mistic100/jquery-querybuilder/dist') => public_path('packages/cmf-vendors/db-query-builder'),
            // ckeditor
            base_path('vendor/ckeditor/ckeditor/ckeditor.js') => public_path('packages/cmf-vendors/ckeditor/ckeditor.js'),
            base_path('vendor/ckeditor/ckeditor/styles.js') => public_path('packages/cmf-vendors/ckeditor/styles.js'),
            base_path('vendor/ckeditor/ckeditor/contents.css') => public_path('packages/cmf-vendors/ckeditor/contents.css'),
            base_path('vendor/ckeditor/ckeditor/adapters') => public_path('packages/cmf-vendors/ckeditor/adapters'),
            base_path('vendor/ckeditor/ckeditor/lang') => public_path('packages/cmf-vendors/ckeditor/lang'),
            base_path('vendor/ckeditor/ckeditor/plugins') => public_path('packages/cmf-vendors/ckeditor/plugins'),
            base_path('vendor/ckeditor/ckeditor/skins') => public_path('packages/cmf-vendors/ckeditor/skins'),
            __DIR__ . '/public/cmf-vendors/ckeditor/config.empty.js' => public_path('packages/cmf-vendors/ckeditor/config.js'),
            // libs
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

        ], 'public');
    }

    static public function loadRoutes() {
        $groupConfig = static::getRoutesGroupConfig();
        $files = static::getCmfConfig()->routes_config_files();
        if (count($files) > 0) {
            \Route::group($groupConfig, function () use ($files) {
                foreach ($files as $filePath) {
                    include $filePath;
                }
            });
        }
        $files = static::getCmfConfig()->cmf_routes_config_files();
        if (count($files) > 0) {
            unset($groupConfig['namespace']); //< cmf routes should be able to use controllers from vendors dir
            \Route::group($groupConfig, function () use ($files) {
                foreach ($files as $filePath) {
                    include $filePath;
                }
            });
        }
    }

    static protected function getRoutesGroupConfig() {
        return [
            'prefix' => static::getCmfConfig()->url_prefix(),
            //'namespace' => '\App\Section\Http\Controllers',
            'middleware' => ['web'],
        ];
    }

    protected function configureEventListeners() {
        \Event::listen(AdminAuthorised::class, AdminAuthorisedEventListener::class);
    }

    static public function configurePublicFilesRoutes() {
        \Route::get('packages/cmf/{file_path}', function ($filePath) {
            $filePath = __DIR__ . '/public/' . $filePath;
            if (File::exist($filePath)) {
                return response(File::contents(), 200, ['Content-Type' => File::load()->mime()]);
            } else {
                return response('File not found');
            }
        })->where(['file_path' => '(js|css|img)\/.+\.[a-z0-9]+$']);
    }

    static public function getDefaultLocale() {
        return static::getCmfConfig()->default_locale();
    }

    static public function getAllowedLocales() {
        return static::getCmfConfig()->locales();
    }

    static public function getLocaleSessionKey() {
        return static::getCmfConfig()->locale_session_key();
    }

}
