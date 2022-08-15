<?php
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 * @var \PeskyCMF\UI\CmfUIModule $uiModule
 * @var string $footerView
 * @var string $menuView
 * @var string $userPanelView
 * @var string $sidebarLogo
 * @var string $topNavbarView
 */
?>

<aside class="main-sidebar">
    <section class="sidebar">
        @include($uiModule->getUIView('sidebar_user_info'))
        @include($uiModule->getUIView('sidebar_menu'))
    </section>
</aside>

<header class="main-header">
    <a href="javascript:void(0)" class="sidebar-toggle visible-xs" data-toggle="push-menu" role="button"></a>
    <a href="{{ $cmfConfig::home_page_url() }}" class="logo">
        <span class="logo-lg">
            {!! $sidebarLogo !!}
        </span>
    </a>
    @include($uiModule->getUIView('top_navbar'))
</header>


<div class="content-wrapper" id="section-content">

</div>

@include($uiModule->getUIView('footer'))

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