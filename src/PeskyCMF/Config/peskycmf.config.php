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