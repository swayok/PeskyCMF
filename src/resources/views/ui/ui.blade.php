<?php

declare(strict_types=1);
/**
 * @var \PeskyCMF\Config\CmfConfig $cmfConfig
 * @var \PeskyCMF\UI\CmfUIModule   $uiModule
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
    <a href="{{ $cmfConfig->homePageUrl() }}" class="logo">
        <span class="logo-lg">
            {!! $uiModule->getSidebarLogo() !!}
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
