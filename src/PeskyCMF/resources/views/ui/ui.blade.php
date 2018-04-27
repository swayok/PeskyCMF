<aside class="main-sidebar">
    <section class="sidebar">
        @include(cmfConfig()->sidebar_admin_info_view())
        @include(cmfConfig()->menu_view())
    </section>
</aside>

<header class="main-header">
    <a href="javascript:void(0)" class="sidebar-toggle visible-xs" data-toggle="push-menu" role="button"></a>
    <a href="{{ cmfConfig()->home_page_url() }}" class="logo">
        <span class="logo-lg">
            {!! cmfConfig()->sidebar_logo() !!}
        </span>
    </a>
</header>

<div class="content-wrapper" id="section-content">

</div>

@include(cmfConfig()->footer_view())

<script type="application/javascript">
    $(document).ready(function () {
        setTimeout(function () {
            // without timeout it works not correctly
            $('body')
                .layout('fix')
                .layout('fixSidebar');
        }, 1);
    })
</script>