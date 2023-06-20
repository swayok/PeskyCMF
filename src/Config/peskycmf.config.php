<?php

declare(strict_types=1);

return [
    /**
     * Default cmf config to use (name of a key from 'cmf_configs')
     */
    'default_cmf_config' => 'default',

    /**
     * List of classes that extend CmfConfig class
     */
    'cmf_configs' => [
        'default' => \PeskyCMF\Config\CmfConfig::class
    ],

    /**
     * Application settings helper class
     */
    'app_settings_class' => \PeskyCMF\PeskyCmfAppSettings::class,

    /**
     * Which versions of .js and .css files to include into layout
     * Modes: null, false, true, 'min', 'packed', 'core', 'all'
     * - 'min', null, false:    use minified versions of all files
     * - 'packed':              use packed but not minified versions of all files
     * - 'src-core':            use source files only for cmf-related files while all libs will be 'packed'
     * - 'src':                 use source files for all files including cmf-related files and all libs
     *
     * After changing a value to 'src-*' you need to republish files using
     * php artisan vendor:publish --tag=public --force
     */
    'assets' => env('CMF_ASSETS') ?: 'min',

    /**
     * Configs related to CmfHttpRequestLog class
     */
    'http_request_logs' => [
        /**
         * Max size of value in request data in bytes.
         * Used in CmfHttpRequestLog::getMinifiedRequestData() when
         * no CmfHttpRequestLog->requestDataMinifier provided.
         * Values that exceed this limit will be shortened to limit.
         * Use 0 to disable.
         */
        'max_request_value_size' => 0,

        /**
         * Max size of logged response in bytes.
         * Used in CmfHttpRequestLog::getMinifiedResponseContent() when
         * no CmfHttpRequestLog->responseContentMinifier provided.
         * Data that exceeds this limit will be shortened to limit.
         * This will prevent too big logs being saved to DB and causing php memory limit being exceeded.
         * Use 0 to disable.
         */
        'max_response_size' => 3145728,
    ]
];
