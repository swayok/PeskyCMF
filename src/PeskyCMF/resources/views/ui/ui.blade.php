<aside class="main-sidebar">
    <section class="sidebar">
        @include(\PeskyCMF\Config\CmfConfig::getInstance()->sidebar_admin_info_view())
        @include(\PeskyCMF\Config\CmfConfig::getInstance()->menu_view())
    </section>
</aside>

<header class="main-header">
    <a href="{{ route('cmf_start_page', [], false, '/') }}" class="logo">
        <span class="logo-lg">
            {!! \PeskyCMF\Config\CmfConfig::getInstance()->sidebar_logo() !!}
        </span>
    </a>
</header>

<div class="content-wrapper" id="section-content">

</div>

@include(\PeskyCMF\Config\CmfConfig::getInstance()->footer_view())

<script type="application/javascript">
    GlobalVars.setLocalizationStrings(<?php echo json_encode(\PeskyCMF\Config\CmfConfig::transBase('.ui.js_component'), JSON_UNESCAPED_UNICODE) ?>);
    $(document).ready(function () {
        $.AdminLTE.tree('.sidebar');
        setTimeout(function () {
            // without timeout it works not correctly
            $.AdminLTE.layout.fix();
            $.AdminLTE.layout.fixSidebar();
        }, 1);
    })
</script>