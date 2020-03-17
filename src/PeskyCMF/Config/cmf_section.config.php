<?php

return [

    /**
     * Prefix used to separate cmf-based site section from other site sections,
     * Example: for prefix "admin" urrl will look like http://localhost/admin
     * You can use '/' inside prefix but it is not tested wel enough to be sure it works correctly
     */
    'url_prefix' => 'admin',

    /**
     * Subfolder name where all CMF files will be stored.
     * Full path will be: app_path(config('{config_name}.app_subfolder'))
     */
    'app_subfolder' => 'Admin',

    'auth' => [
        /**
         * Class that handles every aspect of authorisation and authentication process
         */
        'module' => \PeskyCMF\Auth\CmfAuthModule::class,

        /**
         * Auth Guard configuration.
         * Notes:
         * - you can use string as 'auth_guard_config' or 'auth_guard_config.provider' to use
         *   guard or provider decalred in config/auth.php
         * - use RecordInterface object with Authenticatable interface/trait as 'auth_guard_config.provider.model'
         */
        'guard' => [
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
        'acceess_policy_class' => \PeskyCMF\Auth\CmfAccessPolicy::class,

        /**
         * Enable/disable password restoration
         */
        'is_password_restore_allowed' => true,

        /**
         * Enable/disable registration
         */
        'is_registration_allowed' => true,

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
         * Request current password when saving user profile data when password change is not requested
         */
        'profile_update_requires_current_password' => true,

        /**
         * List of middleware for cmf routes and routes provided in 'routes_files' that require user to be authenticated
         */
        'middleware' => [
            \PeskyCMF\Auth\Middleware\CmfAuth::class
        ],

        /**
         * HTML code
         */
        'login_logo' => null,
    ],

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
     * Namespace for custom controllers of this section.
     * Will be added to routes configs so that you can use controller name without fully qualified namespace.
     * Set to null if you want to disable this option
     */
    'controllers_namespace' => null,

    'ui' => [

        /**
         * Class that handles rendering of section's UI
         */
        'module' => \PeskyCMF\UI\CmfUIModule::class,

        /**
         * Skin for UI. See skins provided by AdminLTE template
         */
        'skin' => 'skin-blue',

        /**
         * Subfolder name in app's 'resources/views' folder that contains custom views for this site section
         * Example: for 'admin' subfolder path will be "/resources/views/admin"
         */
        'views_subfolder' => 'admin',

        /**
         * List of views accessible via '/{url_prefix}/ui/{view}.html' url (route name: 'cmf_custom_ui_view')
         * Key - {view} in url - name of view (example: 'top_menu');
         * Value - view path (example: 'admin.ui.top_menu');
         * Predefined views:
         * 'layout' => 'cmf::layout',
         * 'ui' => 'cmf::ui.ui',
         * 'footer' => 'cmf::ui.footer',
         * 'sidebar_user_info' => 'cmf::ui.sidebar_user_info',
         * 'sidebar_menu' => 'cmf::ui.menu',
         * 'top_navbar' => 'cmf::ui.top_navbar',
         */
        'views' => [

        ],

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
         * JS code blocks to add to CMF layout.
         * Example: google analytics, google firebase, other vendor script blocks.
         * Notes:
         *  - keys of this array are not used - you may use them to label blocks
         *  - all code you provide via this array will be added to layout AS IS.
         *  - you may set value to something like CmfConfig::layout_js_code_blocks() in order to dynamically generate
         *      code blocks; DO NOT USE CLOSURE as a value or php artisan config:cache will fail.
         *      But you can use call_user_func((function () { return [ your code here ] }). Functions must return array.
         */
        'js_code_blocks' => [

        ],

        /**
         * HTML code
         */
        'sidebar_logo' => null,

        /**
         * Configuration for UI templates loading optimization (templates loaded via AJAX)
         * Timeout (minutes): how long optimized templates should be cached. Set to 0 to cache forever
         * Don't forget to clean cache after you change something in CMF.
         * Note: by default cache key is based on user ID because it is not possible to cache more globally due to
         * 'acceess_policy_class'. Though if you use default PeskyCMF\Config\CmfAccessPolicy - then cache key will be
         * more global. If you use role-based access policy (access is not determined by user ID) - you can overwrite
         * getCacheKeyForOptimizedUiTemplates() method in your 'cmf_config' to use roles instead of user IDs
         */
        'optimize_ui_templates' => [
            'enabled' => env('PESKYCMF_OPTIMIZE_UI_TEMPLATES', false),
            'timeout' => 0
        ],

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
         * Base class for scaffold configs generated by 'php artisan cmf:make-scaffold' command
         */
        //'scaffold_configs_base_class' => \PeskyCMF\Scaffold\NormalTableScaffoldConfig::class,

        /**
         * Base class for scaffold configs generated by 'php artisan cmf:make-scaffold --keyvalue' command
         */
        //'scaffold_configs_base_class_for_key_value_tables' => \PeskyCMF\Scaffold\KeyValueTableScaffoldConfig::class,

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
     * Class name for HTTP requests logger.
     * Class must implement ScaffoldLoggerInterface
     */
    'http_requests_logger_class_name' => null,

    /**
     * API documentation settings
     * Linked commands:
     * @see \PeskyCMF\Console\Commands\CmfMakeApiDocCommand - makes API documentation page class (wiki-like)
     * @see \PeskyCMF\Console\Commands\CmfMakeApiMethodDocCommand - makes API method documentation class
     */
    'api_documentation' => [

        /**
         * Class that handles everything related to API docs section
         */
        'module' => \PeskyCMF\ApiDocs\CmfApiDocumentationModule::class,

        /**
         * List of class names that extend \PeskyCMF\ApiDocs\CmfApiMethodDocumentation or \PeskyCMF\ApiDocs\CmfApiDocumentation class
         * Note: there is a possibility to load classes automatically using 'api_documentation_classes_folder'. More details
         * in CmfApiDocumentationModule->loadApiMethodsDocumentationClassesFromFileSystem()
         */
        'classes' => [

        ],

        /**
         * Absolute path to folder where api docs classes will be stored when using
         * 'cmf:make-api-doc' and 'cmf:make-api-method-doc' artisan command
         */
        'folder' => null,

        /**
         * Base class for api method documentation. Used in 'cmf:make-api-method-doc' artisan command
         * and in CmfConfig::loadApiMethodsDocumentationClassesFromFileSystem()
         */
        'base_class_for_method' => \PeskyCMF\ApiDocs\CmfApiMethodDocumentation::class,

        /**
         * Suffix for method documentation class name.
         * Used in 'cmf:make-api-doc' and 'cmf:make-api-method-doc' artisan command
         */
        'class_suffix' => 'Documentation'
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
     * Enable/disable periodical pinging in order to
     * prolong session or detect if session or auth expired.
     * Set interval > 0 to enable pinging.
     * Interval metrics is in seconds.
     */
    'ping_interval' => 0,
];
