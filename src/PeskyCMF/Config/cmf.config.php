<?php

return [

    /**
     * The name of the class that extends \PeskyCMF\Config\CmfConfig and will be used in CMF/CMS internals
     * Note: use "php artisan cmf:install" to create new config file.
     * For 'admin' url prefix \App\Admin\AdminConfig class will be created
     */
    'cmf_config' => \PeskyCMF\Config\CmfConfig::class,

    /**
     * Application settings helper class
     */
    'app_settings_class' => \PeskyCMF\PeskyCmfAppSettings::class,

    /**
     * Prefix used to separate cmf-based site section from other site sections,
     * Example: for prefix "admin" urrl will look like http://localhost/admin
     * You can use '/' inside prefix but it is not tested wel enough to be sure it works correctly
     */
    'url_prefix' => 'admin',

    /**
     * Path to files with custom routes for this section
     * Notes:
     * - path to files must be relative to base_path()
     * - routes file is created by "php artisan cmf:install"
     */
    'routes_files' => [
        //'/routes/admin.php'
    ],

    /**
     * List of middleware for cmf routes and routes provided in 'routes_files'
     */
    'routes_middleware' => [
        'web'
    ],

    /**
     * List of middleware for cmf routes and routes provided in 'routes_files' that require user to be authenticated
     */
    'routes_auth_middleware' => [
        \PeskyCMF\Http\Middleware\ValidateCmfUser::class
    ],

    /**
     * Namespace for custom controllers of this section.
     * Will be added to routes configs so that you can use controller name without fully qualified namespace.
     * Set to null if you want to disable this option
     */
    'controllers_namespace' => null,

    /**
     * Subfolder name in app's 'resources/views' folder that contains custom views for this site section
     * Example: for 'admin' subfolder path will be "/resources/views/admin"
     */
    'views_subfolder' => 'admin',

    /**
     * CSS files to add to app
     * Note: file '/packages/{underscored_url_prefix}/css/{underscored_url_prefix}.custom.css' is created by "php artisan cmf:install"
     */
    'css_files' => [
        //'/packages/admin/css/admin.custom.css'
    ],

    /**
     * JS files to add to app
     * Note: file '/packages/{underscored_url_prefix}/css/{underscored_url_prefix}.custom.js' is created by "php artisan cmf:install"
     */
    'js_files' => [
        //'/packages/admin/js/admin.custom.js'
    ],

    /**
     * Session storage configs to be used within CMF/CMS pages.
     * Notes:
     * - you can overwrite any key present in 'config/session.php' to configure session the way you need
     * - by default a 'path' key will be set to '/{url_prefix}'
     */
    'session' => [
        'table' => 'cmf_sessions',
        'cookie' => 'cmf_session',
        'connection' => 'default',
        'lifetime' => 1440,
        'expire_on_close' => true,
    ],

    /**
     * Auth Guard configuration.
     * Notes:
     * - you can use string as 'auth_guard_config' or 'auth_guard_config.provider' to use
     *   guard or provider decalred in config/auth.php
     * - use RecordInterface object with Authenticaable interface/trait as 'auth_guard_config.provider.model'
     */
    'auth_guard' => [
        'name' => 'cmf',
        'driver' => 'session',
        'provider' => [
            'driver' => 'peskyorm',
            'model' => null
        ]
    ],

    /**
     * DB Record class for users
     * For CMS use \PeskyCMF\Db\Admins\CmfAdmin::class
     */
    'user_record_class' => \PeskyCMF\Db\Admins\CmfAdmin::class,

    /**
     * Column that is used as user's identifier
     * Usually: 'email' or 'login'
     */
    'user_login_column' => 'email',

    /**
     * Access policy to use for authorisation
     */
    'acceess_policy_class' => \PeskyCMF\Config\CmfAccessPolicy::class,

    /**
     * Skin for UI. See skins provided by AdminLTE template
     */
    'ui_skin' => 'skin-blue',

    /**
     * Enable/disable password restoration
     */
    'is_password_restore_allowed' => true,

    /**
     * Dictionary to use for section's scaffolds (CmfConfig::transCustom())
     * Usage: trans('{dictionary}.path.to.translation'), example: trans('admin.path.to.translation')
     */
    'dictionary' => 'admin',

    /**
     * Default locale for this section
     */
    'locale' => 'en',

    /**
     * List of locales and locale redirects.
     * Note: you can redirect locales using key as 'locale to redirect from' and value as 'locale to redirect to'
     * Must be acceptable by https://github.com/vluzrmos/laravel-language-detector
     */
    'locales' => [
        'en'
    ],

    /**
     * List of roles
     */
    'roles' => [
        'admin'
    ],

    /**
     * Default role for user
     */
    'default_role' => 'admin',

    /**
     * HTML code
     */
    'login_logo' => null,

    /**
     * HTML code
     */
    'sidebar_logo' => null,

    /**
     * List of resources. Format:
     * - key = resource name (alt name: table name for routes)
     * - value = ScaffoldConfig class name
     */
    'resources' => [
        \PeskyCMF\Db\Admins\CmfAdminsScaffoldConfig::class,
        \PeskyCMF\Db\Settings\CmfSettingsScaffoldConfig::class,
    ],

    /**
     * Class name for HTTP requests logger.
     * Class must implement ScaffoldLoggerInterface
     */
    'http_requests_logger_class_name' => null,

    /**
     * List of class names that extend \PeskyCMF\ApiDocs\CmfApiDocsSection class
     */
    'api_docs_class_names' => [

    ],

    /**
     * Alter umask()
     * Use 0000 to disable umask (allows to set any access rights to any file/folder created by app)
     */
    'file_access_mask' => null,

    /**
     * Email address that is used to send emails to users ('From' header).
     * Default: 'noreply@' . request()->getHost()
     */
    'system_email_address' => null,

    /**
     * Base class for scaffold configs generated by 'php artisan cmf:make-scaffold' command
     */
    'scaffold_configs_base_class' => \PeskyCMF\Scaffold\NormalTableScaffoldConfig::class,

    /**
     * Base class for scaffold configs generated by 'php artisan cmf:make-scaffold --keyvalue' command
     */
    'scaffold_configs_base_class_for_key_value_tables' => \PeskyCMF\Scaffold\KeyValueTableScaffoldConfig::class,
];