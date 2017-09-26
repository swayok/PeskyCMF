<?php

return [



    /**
     * The name of the class that extends \PeskyCMF\Config\CmfConfig and will be used in CMF/CMS internals
     */
    'cmf_config' => null,

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
     *
     */
    'recaptcha_private_key' => null,
];