<?php

namespace PeskyCMF\Http;

use LaravelSiteLoader\AppSiteLoader;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Event\AdminAuthenticated;
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
        $this->configureAuthorizationGatesAndPolicies();
    }

    /**
     * In this method you should place authorisation gates and policies according to Larave's docs:
     * https://laravel.com/docs/5.4/authorization
     * Predefined authorisation tests are available for:
     * 1. Resources (scaffolds) - use
     *      Gate::resource('resource', 'CmfAccessPolicy', [
                'view' => 'view',
                'details' => 'details',
                'create' => 'create',
                'update' => 'update',
                'delete' => 'delete',
                'update_bulk' => 'update_bulk',
                'delete_bulk' => 'delete_bulk',
            ]);
     *      or Gate::define('resource.{ability}', \Closure) to provide rules for some resource.
     *      List of abilities used in scaffolds:
     *      - 'view' is used for routes named 'cmf_api_get_items' and 'cmf_api_get_templates',
     *      - 'details' => 'cmf_api_get_item',
     *      - 'create' => 'cmf_api_create_item',
     *      - 'update' => 'cmf_api_update_item'
     *      - 'update_bulk' => 'cmf_api_edit_bulk'
     *      - 'delete' => 'cmf_api_delete_item'
     *      - 'delete_bulk' => 'cmf_api_delete_bulk'
     *      For all abilities you will receive $tableName argument and RecordInterface $record or int $itemId argument
     *      for 'details', 'update' and 'delete' abilities.
     *      For KeyValueScaffoldConfig for 'update' ability you will receive $fkValue instead of $itemId/$record.
     *      For 'update_bulk' and 'delete_bulk' you will receive $conditions array.
     *      Note that $tableName passed to abilities is the name of the DB table used in routes and may differ from
     *      the real name of the table provided in TableStructure.
     *      For example: you have 2 resources named 'pages' and 'elements'. Both refer to PagesTable class but
     *      different ScaffoldConfig classes (PagesScaffoldConfig and ElementsScafoldConfig respectively).
     *      In this case $tableName will be 'pages' for PagesScaffoldConfig and 'elements' for ElementsScafoldConfig.
     *      Note: If you forbid 'view' ability - you will forbid everything else
     *      Note: there is no predefined authorization for routes based on 'cmf_item_custom_page'. You need to add it
     *      manually to controller's action that handles that custom page
     * 2. CMF Pages - use Gate::define('cmf_page', 'CmfAccessPolicy@cmf_page')
     *      Abilities will receive $pageName argument - it will contain the value of the {page} property in route
     *      called 'cmf_page' (url is '/{prefix}/page/{page}' by default)
     * 3. Admin profile update - Gate::define('profile.update', \Closure);
     *
     * For any other routes where you resolve authorisation by yourself - feel free to use any naming you want
     */
    protected function configureAuthorizationGatesAndPolicies() {

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
        \Event::listen(AdminAuthenticated::class, AdminAuthorisedEventListener::class);
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
        throw new \BadMethodCallException('This method should not be used. Use relevant CmfConfig::locales');
    }

    public function configureLocale() {
        $config = $this->getAppConfig()->get('lang-detector', []);
        $this->getAppConfig()->set('lang-detector', array_replace_recursive($config, static::getCmfConfig()->language_detector_configs()));
    }

}
