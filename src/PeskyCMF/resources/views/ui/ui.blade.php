<aside class="main-sidebar">
    <section class="sidebar">
        @include(\PeskyCMF\Config\CmfConfig::getPrimary()->sidebar_admin_info_view())
        @include(\PeskyCMF\Config\CmfConfig::getPrimary()->menu_view())
    </section>
</aside>

<header class="main-header">
    <a href="{{ route('cmf_start_page', [], false, '/') }}" class="logo">
        <span class="logo-lg">
            {!! \PeskyCMF\Config\CmfConfig::getPrimary()->sidebar_logo() !!}
        </span>
    </a>
</header>

<div class="content-wrapper" id="section-content">

</div>

@include(\PeskyCMF\Config\CmfConfig::getPrimary()->footer_view())

<script type="application/javascript">
    CmfConfig.setLocalizationStrings(<?php echo json_encode(cmfTransGeneral('.ui.js_component'), JSON_UNESCAPED_UNICODE) ?>);
    $(document).ready(function () {
        setTimeout(function () {
            // without timeout it works not correctly
            $.AdminLTE.layout.fix();
            $.AdminLTE.layout.fixSidebar();
        }, 1);
    })
</script>