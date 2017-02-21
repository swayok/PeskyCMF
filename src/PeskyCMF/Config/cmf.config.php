<?php

return [

    /**
     * Class name for default site loader. You do not need to add this class to 'site_loaders'
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
    'default_scaffold_config' => null,
];