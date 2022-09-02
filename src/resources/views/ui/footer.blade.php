<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 */
?>
<footer class="main-footer">
    <strong>Copyright &copy;2015-{{ date('Y') }} Filippov Alexander. All rights reserved</strong>
    <div class="pull-right">
        <a href="{{ \PeskyCMF\CmfUrl::toPage('about', [], false, $cmfConfig) }}">{{ $cmfConfig->transCustom('page.about.link_label') }}</a>
    </div>
</footer>