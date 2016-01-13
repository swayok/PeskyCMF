<aside class="main-sidebar">
    <section class="sidebar">
        <div id="user-panel">
            <div class="info">

            </div>
            <div class="actions">
                <a href="{{ route('cmf_profile', [], false) }}"><i class="fa fa-fw fa-user"></i>{{ trans('cmf::cmf.user.profile_label') }}</a>
                <a href="{{ route(\PeskyCMF\Config\CmfConfig::getInstance()->logout_route(), [], false) }}"><i class="fa fa-fw fa-sign-out"></i>{{ trans('cmf::cmf.user.logout_label') }}</a>
            </div>
        </div>
        <script type="text/html" id="user-panel-tpl">
            <div class="name">@{{? it.name.length }}@{{= it.name }}@{{??}}@{{= it.role }}@{{?}}</div>
            <div class="email">@{{= it.email }}</div>
        </script>

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
    GlobalVars.setLocalizationStrings(<?php echo json_encode(trans('cmf::cmf.ui.js_component')) ?>);
    $(document).ready(function () {
        $.AdminLTE.tree('.sidebar');
        setTimeout(function () {
            // without timeout it works not correctly
            $.AdminLTE.layout.fix();
            $.AdminLTE.layout.fixSidebar();
        }, 1);
    })
</script>