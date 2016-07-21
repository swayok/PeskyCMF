<?php
/**
 * @var string $header
 * @var bool|null $addBackUrl
 * @var string|null $defaultBackUrl
 */
?>
<div class="content-header">
    <h1>{!! $header !!}</h1>
    @include('cmf::ui.default_breadcrumbs')
</div>