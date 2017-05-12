<?php

return [

    /**
     * Class name for default site loader. You do not need to add this class to 'additional_site_loaders'
     */
    'default_site_loader' => null,

    /**
     * List names of classes that extend SiteLoader
     * All this loaders are additional to default_site_loader
     */
    'additional_site_loaders' => [

    ],

    /**
     * Siteloader for console commands.
     * It is better to use class that extends PeskyCmfSiteLoader so that CMF commands will work correctly
     */
    'console_site_loader' => null,

    /**
     * Default config class to be used in CMS DB classes
     * Must be an instance of a class that extends \PeskyCMF\Config\CmfConfig
     */
    'default_cmf_config' => null,

    /**
     * Class that helps to get application settings from CmsSettingsTable
     * Used by setting($name, $default) helper function
     */
    'app_settings_class' => \PeskyCMF\CMS\CmsAppSettings::class,

    /**
     * Alter umask()
     * Use 0000 to disable umask (allows to set any access rights to any file/folder created by app)
     */
    'file_access_mask' => null
];