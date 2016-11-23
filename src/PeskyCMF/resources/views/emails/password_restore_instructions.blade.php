<?php
/**
 * @var array $user
 * @var string $url
 */
echo cmfTransCustom('.forgot_password.email_content', ['url' => $url] + \Swayok\Utils\Set::flatten($user));
