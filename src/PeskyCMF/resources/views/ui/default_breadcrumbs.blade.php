<?php
/**
 * @var string|null $defaultBackUrl
 * @var bool|null $addBackUrl
 */
if (!isset($addBackUrl)) {
    $addBackUrl = true;
}
?>
<ol class="breadcrumb">
    @if (!empty($addBackUrl))
    <li>
        <a href="#" data-nav="back" @if(!empty($defaultBackUrl))data-default-url="{!! $defaultBackUrl !!}"@endif>
            <i class="glyphicon fa fa-reply"></i>
            {{ \PeskyCMF\Config\CmfConfig::transBase('.action.back') }}
        </a>
    </li>
    @endif
    <li>
        <a href="#" data-nav="reload">
            <i class="glyphicon glyphicon-refresh"></i>
            {{ \PeskyCMF\Config\CmfConfig::transBase('.action.reload_page') }}
        </a>
    </li>
</ol>