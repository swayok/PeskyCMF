<?php
/**
 * @var string $header
 * @var string|null $addBackUrl
 * @var bool|null $defaultBackUrl
 */
?>
<div class="content-header">
    <h1>{!! $header !!}</h1>
    @include('cmf::ui.default_breadcrumbs')
</div>