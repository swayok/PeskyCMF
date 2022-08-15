<?php
/**
 * @var string $header
 * @var bool|null $addBackUrl
 * @var string|null $defaultBackUrl
 * @var bool|null $stickToTop
 */
$stickToTop = !empty($stickToTop);
?>
<div class="content-header {{ $stickToTop ? 'stick-to-top' : '' }}">
    <h1>{!! $header !!}</h1>
    @include('cmf::ui.default_breadcrumbs')
</div>
@if ($stickToTop)
    <div class="sticked-content-header-placeholder"></div>
@endif