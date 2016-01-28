<?php
/**
 * @var array $user
 * @var string $url
 */
echo \PeskyCMF\Config\CmfConfig::transCustom('.forgot_password.email_content', ['url' => $url] + \Swayok\Utils\Set::flatten($user));
