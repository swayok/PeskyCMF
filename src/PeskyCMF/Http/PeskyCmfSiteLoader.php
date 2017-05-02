<?php

namespace PeskyCMF\Http;

use LaravelSiteLoader\AppSiteLoader;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthorised;
use PeskyCMF\Listeners\AdminAuthorisedEventListener;
use Swayok\Utils\File;

abstract class PeskyCmfSiteLoader extends AppSiteLoader {

    /** @var CmfConfig */
    static protected $cmfConfig;
    /** @var string */
    static protected $cmfConfigsClass;
    /** @var bool */
    static protected $useCmfConfigAsDefault = true;

    /**
     * @return CmfConfig
     */
    static public function getCmfConfig() {
        if (static::$cmfConfig === null) {
            /** @var CmfConfig $cmfConfigsClass */
            $cmfConfigsClass = static::$cmfConfigsClass;
            static::$cmfConfig = $cmfConfigsClass::getInstance();
        }
        return static::$cmfConfig;
    }

    static public function getBaseUrl() {
        return '/' . trim(static::getCmfConfig()->url_prefix(), '/');
    }

    public function boot() {
        static::getCmfConfig()->useAsPrimary();

        // custom configurations
        $this->configure();
        // alter auth config
        $this->configureAuth();
        // alter session config
        $this->configureSession();

        $this->configureDefaultLocale();
        $this->configureLocale();
        $this->includeFiles();

        $this->configureViewsLoading();
        $this->configureTranslationsLoading();

        $this->configureEventListeners();
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
    }

    public function provides() {
        return [
            CmfConfig::class,
        ];
    }

    static public function configureDefaults() {

    }

    protected function includeFiles() {

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
            \Route::group(array_diff_key($groupConfig, ['middleware' => '']), function () {
                \Route::get('ckeditor/config.js', [
                    'as' => 'cmf_ckeditor_config_js',
                    'uses' => CmfConfig::getPrimary()->cmf_general_controller_class() . '@getCkeditorConfigJs'
                ]);
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

    public function configureLocale() {
        static::getCmfConfig()->setLocale(static::getCmfConfig()->getLocale());
    }

}
