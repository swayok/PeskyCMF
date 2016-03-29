<?php

namespace PeskyCMF;

use App\AppSiteLoaderInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\DbModel;
use Swayok\Utils\File;

class PeskyCmfServiceProvider extends ServiceProvider {

    /** @var string */
    static protected $cmfConfigsClass = CmfConfig::class;
    /** @var bool */
    protected $sendConfigsToLaravelContainer = false;

    /** @var CmfConfig */
    static protected $cmfConfig = null;

    static protected $alreadyLoaded = false;

    public function __construct(Application $app) {

        parent::__construct($app);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton(CmfConfig::class, function () {
            return static::$cmfConfig;
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

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {

        static::configurePublicFilesRoutes();

        if (self::$alreadyLoaded) {
            return; //< to prevent 2 different providers overwrite each other
        }

        if (static::canBeUsed()) {

            static::$cmfConfig->replaceConfigInstance(CmfConfig::class, static::$cmfConfig);

            $this->loadConfigs();

            // alter auth config
            $this->configureAuth();
            // alter session config
            $this->configureSession();
            // custom configurations
            $this->configure();

            $this->configureViewsLoading();

            $this->configureTranslationsLoading();

            $this->configurePublishes();

            $this->setLocale();

            $this->loadRoutes();

            $this->includeFiles();

            $this->storeConfigsInLaravelContainer();

            self::$alreadyLoaded = true;
        } else {
            $this->whenConfigsShouldNotBeLoaded();
        }
    }

    /**
     * @return \Config
     */
    public function appConfigs() {
        return config();
    }

    /**
     * Sets the locale if it exists in the session and also exists in the locales option
     *
     * @return void
     */
    public function setLocale() {
        $locale = session()->get(static::$cmfConfig->locale_session_key());
        app()->setLocale($locale ?: static::$cmfConfig->default_locale());
    }

    protected function includeFiles() {
        require_once __DIR__ . '/Config/helpers.php';
    }

    protected function loadConfigs() {

    }

    protected function storeConfigsInLaravelContainer() {
        if ($this->sendConfigsToLaravelContainer) {
            $key = 'cmf';
            $config = $this->appConfigs()->get($key, []);
            $this->appConfigs()->set($key, array_merge($config, static::$cmfConfig->toArray()));
        }
    }

    protected function configureAuth() {
        $config = $this->appConfigs()->get('auth', []);
        $this->appConfigs()->set('auth', array_replace_recursive($config, static::$cmfConfig->auth_configs()));
        \Auth::shouldUse(static::$cmfConfig->auth_guard_name());
    }

    protected function configureSession() {
        $config = $this->appConfigs()->get('session', []);
        $config['path'] = static::getBaseUrl();
        $this->appConfigs()->set('session', array_merge($config, static::$cmfConfig->session_configs()));
    }

    /**
     * Custom configurations
     */
    protected function configure() {

    }

    protected function configureViewsLoading() {
        $this->loadViewsFrom(static::$cmfConfig->views_path(), 'cmf');
    }

    protected function configureTranslationsLoading() {
        $this->loadTranslationsFrom(static::$cmfConfig->cmf_dictionaries_path(), 'cmf');
    }

    protected function configurePublishes() {
        $this->publishes([
            base_path('vendor/almasaeed2010/adminlte/dist') => public_path('packages/adminlte'),
            base_path('vendor/almasaeed2010/adminlte/plugins') => public_path('packages/adminlte/plugins'),
            base_path('vendor/almasaeed2010/adminlte/bootstrap') => public_path('packages/bootstrap'),
            base_path('vendor/fortawesome/font-awesome/css') => public_path('packages/font-awesome/css'),
            base_path('vendor/fortawesome/font-awesome/fonts') => public_path('packages/font-awesome/fonts'),
            base_path('vendor/driftyco/ionicons/css') => public_path('packages/ionicons/css'),
            base_path('vendor/driftyco/ionicons/fonts') => public_path('packages/ionicons/fonts'),
            base_path('vendor/nostalgiaz/bootstrap-switch/dist') => public_path('packages/bootstrap/switches'),
            base_path('vendor/eonasdan/bootstrap-datetimepicker/build') => public_path('packages/bootstrap/datetimepicker'),
            __DIR__ . '/public' => public_path('packages/cmf'),
        ], 'public');

    }

    protected function loadRoutes() {
        $this->loadCustomRoutes();
        $this->loadCmfRoutes();
    }

    protected function loadCustomRoutes() {
        foreach (static::$cmfConfig->routes_config_files() as $filePath) {
            require_once $filePath;
        }
    }

    protected function loadCmfRoutes() {
        foreach (static::$cmfConfig->cmf_routes_cofig_files() as $filePath) {
            require_once $filePath;
        }
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

    protected function whenConfigsShouldNotBeLoaded() {

    }

    static public function canBeUsed() {
        return empty($_SERVER['REQUEST_URI']) || starts_with($_SERVER['REQUEST_URI'], self::getBaseUrl());
    }

    static public function getBaseUrl() {
        return '/' . trim(static::$cmfConfig->url_prefix(), '/');
    }

    /**
     * Get default locale code (RU, EN, FR, etc)
     * @return string
     */
    static public function getDefaultLocale() {

    }
}
