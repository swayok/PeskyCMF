<?php
/**
 * @var \App\Db\Manager\Manager $user
 * @var string $url
 */
echo \PeskyCMF\Config\CmfConfig::transCustom('.forgot_password.email_content', ['url' => $url, 'user' => $user]);
