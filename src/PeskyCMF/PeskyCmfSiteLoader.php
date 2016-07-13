<?php

namespace PeskyCMF;

use LaravelSiteLoader\AppSiteLoader;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthorised;
use PeskyCMF\Listeners\AdminAuthorisedEventListener;
use PeskyORM\DbModel;
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
        if (empty(static::$cmfConfig)) {
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
        $this->configurePublishes();
        static::setLocale();
        $this->loadRoutes();
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
        $this->app->singleton(DbModel::class, function () {
            return CmfConfig::getInstance()->base_db_model_class();
        });
    }

    public function provides() {
        return [
            CmfConfig::class,
            \PeskyCMF\Http\Request::class,
            DbModel::class
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

    protected function configurePublishes() {
        $this->provider->publishes([
            // cmf
            __DIR__ . '/public/css' => public_path('packages/cmf/css'),
            __DIR__ . '/public/js' => public_path('packages/cmf/js'),
            __DIR__ . '/public/less' => public_path('packages/cmf/less'),
            __DIR__ . '/public/img' => public_path('packages/cmf/img'),
            __DIR__ . '/public/cmf-vendors' => public_path('packages/cmf-vendors'),
            // AdminLTE
            base_path('vendor/almasaeed2010/adminlte/dist') => public_path('packages/adminlte'),
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
            // libs
            base_path('vendor/datatables/datatables/media') => public_path('packages/cmf-vendors/datatables'),
            base_path('vendor/ckeditor/ckeditor') => public_path('packages/cmf-vendors/ckeditor'),
            base_path('vendor/mistic100/jquery-querybuilder/dist') => public_path('packages/cmf-vendors/db-query-builder'),
            base_path('vendor/grimmlink/toastr/build') => public_path('packages/cmf-vendors/toastr'),
            base_path('vendor/moment/moment/moment.js') => public_path('packages/cmf-vendors/moment/moment.js'),
            base_path('vendor/moment/moment/min/moment.min.js') => public_path('packages/cmf-vendors/moment/moment.min.js'),
            base_path('vendor/moment/moment/locale') => public_path('packages/cmf-vendors/moment/locale'),
            base_path('vendor/malsup/form/jquery.form.js') => public_path('packages/cmf-vendors/jquery.form.js'),
            base_path('vendor/afarkas/html5shiv/dist/html5shiv.min.js') => public_path('packages/cmf-vendors/html5shiv.min.js'),

        ], 'public');
    }

    protected function loadRoutes() {
        $this->loadCustomRoutes();
        $this->loadCmfRoutes();
    }

    protected function loadCustomRoutes() {
        foreach (static::getCmfConfig()->routes_config_files() as $filePath) {
            require_once $filePath;
        }
    }

    protected function loadCmfRoutes() {
        foreach (static::getCmfConfig()->cmf_routes_cofig_files() as $filePath) {
            require_once $filePath;
        }
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

    static public function setLocale() {
        $locale = session()->get(static::$cmfConfig->locale_session_key());
        app()->setLocale($locale ?: static::getDefaultLocale());
    }

}
