$(function () {

    fixAdminLte();

    extendRouter();

    if (CmfSettings) {
        $.extend(CmfConfig, CmfSettings);
    }

    if (CmfConfig.isDebug) {
        Utils.initDebuggingTools();
    }

    Utils.configureAppLibs();

    page.base(CmfConfig.rootUrl);
    page.exit(CmfRoutingHelpers.pageExitTransition);
    page.exit(function (request, next) {
        CmfRoutingHelpers.cleanupHangedElementsInBody();
        next();
    });

    if (typeof CustomRoutes !== 'undefined' && typeof CustomRoutes.init === 'function') {
        CustomRoutes.init();
    }

    page('*', function (request, next) {
        request.query = qs.parse(request.querystring);
        window.request = request;
        next();
    });
    page.route('/login', CmfRouteChange.authorizationPage);
    page.route('/forgot_password', CmfRouteChange.authorizationPage);
    page.route('/replace_password', CmfRouteChange.authorizationPage);
    page.route('/logout', CmfRouteChange.logout, CmfRoutingHelpers.routeHandled);
    page.route('/page/:uri*', CmfRouteChange.showPage);

    page.route('/resource/:resource', CmfRouteChange.scaffoldDataGridPage);
    page.route('/resource/:resource/details/:id', CmfRouteChange.scaffoldItemDetailsPage);
    page.route('/resource/:resource/create', CmfRouteChange.scaffoldItemFormPage);
    page.route('/resource/:resource/edit/:id', CmfRouteChange.scaffoldItemFormPage);
    page.route('/resource/:resource/:id/page/:page', CmfRouteChange.scaffoldItemCustomPage);

    $(document).on('click', '[data-nav]', function (event) {
        event.preventDefault();
        var $btn = $(this);
        var navigateTo = $btn.attr('data-nav');
        var fallbackUrl = $btn.attr('data-default-url');
        switch (navigateTo.toLowerCase()) {
            case 'back':
                page.back(fallbackUrl);
                break;
            case 'reload':
                page.reload();
                break;
            case 'page':
            default:
                page.show(fallbackUrl ? fallbackUrl : navigateTo);
                break;
        }
        return false;
    });

    if (typeof CustomApp !== 'undefined' && typeof CustomApp.beforeStart === 'function') {
        CustomApp.beforeStart();
    }

    page.start();

    if (typeof CustomApp !== 'undefined' && typeof CustomApp.afterStart === 'function') {
        CustomApp.afterStart();
    }

    page.route('*', function (request, next) {
        // handle 404 requests
        if (!request.handled && CmfConfig.isDebug) {
            console.error('No route found for: ' + request.pathname);
        }
        if (!request.error) {
            next(); //< use default behavior (use usual redirect to requested url)
        }
    });

});

function fixAdminLte() {
    // fix sidebar/content min-height fixer
    /*$.AdminLTE.layout.fix = function () {
        $('body, html, .wrapper').css('height', '100vh');
        // Get heights
        var headerHeight = $('.main-header').outerHeight(true) || 0;
        var headerLogoHeight = $('.main-header .logo').outerHeight(true) || 0;
        var footerHeight = $('.main-footer').outerHeight(true) || 0;
        var windowHeight = $(window).height();
        var sidebarHeight = $(".sidebar").outerHeight(true) + headerLogoHeight;
        var $elements = $(".content-wrapper, .right-side");
        // Set the min-height of the content and sidebar based on the the height of the document.
        if ($("body").hasClass("fixed")) {
            $elements.css('min-height', windowHeight - footerHeight);
        } else {
            var newHeight;
            if (windowHeight >= sidebarHeight) {
                newHeight = windowHeight - headerHeight - footerHeight;
            } else {
                newHeight = sidebarHeight - headerHeight - footerHeight;
            }
            $elements.css('min-height', newHeight);

            //Fix for the control sidebar height
            var controlSidebar = $($.AdminLTE.options.controlSidebarOptions.selector);
            if (typeof controlSidebar !== "undefined") {
                if (controlSidebar.height() > newHeight) {
                    $elements.css('min-height', controlSidebar.height());
                }
            }
        }
    };

    // to fix sidebar menu dropdown closing
    var original = $.AdminLTE.tree;
    $.AdminLTE.tree = function (menu) {
        original.call(this, menu);
        $(menu).on('click', 'li a', function (e) {
            $.AdminLTE.layout.fix();
            setTimeout($.AdminLTE.layout.fix, $.AdminLTE.options.animationSpeed + 10);
        });
    };

    $.AdminLTE.layout.fix();*/

}

function extendRouter() {

}