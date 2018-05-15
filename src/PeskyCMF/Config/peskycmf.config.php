<?php

return [
    /**
     * Default cmf config to use (name of a key from 'cmf_configs')
     */
    'default_cmf_config' => 'default',

    /**
     * List of classes that extend CmfConfig class
     */
    'cmf_configs' => [
        'default' => \App\Admin\AdminConfig::class
    ],

    /**
     * Application settings helper class
     */
    'app_settings_class' => \PeskyCMF\PeskyCmfAppSettings::class,
];