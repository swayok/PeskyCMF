<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 * @var string $dbClassesAppSubfolder
 * @var array $scaffolds
 */
if (empty($urlPrefix)) {
    $urlPrefix = strtolower($sectionName);
}
$lowercasedSectionName = snake_case($sectionName);
echo "<?php \n";
?>
return [

    /**
     * Class that extends CmfConfig class
     */
    'config_class' => {{ $sectionName }}Config::class,

    'url_prefix' => '{{ $urlPrefix }}',

    /**
     * Path to files with custom routes for this section
     */
    'routes_files' => [
        base_path('routes/{{ $lowercasedSectionName }}.php')
    ],

    /**
     * Subfolder name in app's 'resources' folder that contains custom views for this site section
     */
    'views_subfolder' => '{{ $lowercasedSectionName }}',

    /**
     * CSS files to add to app
     */
    'layout_css_includes' => [
        '/packages/{{ $lowercasedSectionName }}/css/{{ $lowercasedSectionName }}.custom.css'
    ],

    /**
     * JS files to add to app
     */
    'layout_js_includes' => [
        '/packages/{{ $lowercasedSectionName }}/js/{{ $lowercasedSectionName }}.custom.js'
    ],

    /**
     * Name of the authentification guard to use in this section
     * Note: you do not need to add it to config/auth.php - it will be added by CmfConfig related to this section
     */
    'auth_guard_name' => '{{ $lowercasedSectionName }}',

    /**
     * User provider name to use for authorisation
     */
    'auth_user_provider_name' => 'peskyorm',

    /**
     * DB Record class for users
     */
    'user_object_class' => null,

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
     */
    'dictionary' => '{{ $lowercasedSectionName }}',

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
        @foreach($scaffolds as $resourceName => $scaffoldClassName)
        '{{ $resourceName }}' => {{ $scaffoldClassName }}::class,
        @endforeach
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

    ]
];
