<?php
/**
 * @var array $user
 * @var string $url
 * @var \PeskyCMF\Config\CmfConfig $cmfConfigClass
 */
echo $cmfConfigClass::transCustom('forgot_password.email_content', ['url' => $url] + \Swayok\Utils\Set::flatten($user));
