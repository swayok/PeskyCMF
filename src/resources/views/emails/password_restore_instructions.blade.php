<?php
/**
 * @var array $user
 * @var string $url
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
echo $cmfConfig->transCustom('forgot_password.email_content', ['url' => $url] + \Swayok\Utils\Set::flatten($user));
