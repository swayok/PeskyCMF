$(function () {

    fixAdminLte();

    if (CmfSettings) {
        $.extend(CmfConfig, CmfSettings);
    }

    if (CmfConfig.isDebug) {
        Utils.initDebuggingTools();
    }

    Utils.configureAppLibs();

    page.base(CmfConfig.rootUrl);
    var logger = function (context, next) {
        console.log(context);
        next();
    };
    page('/login', logger, CmfRouteChange.authorisationPage);
    page('/forgot_password', logger);
    page('/replace_password', logger);
    page('/logout', function (event, request) {
        Utils.showPreloader(document.body);
        Utils.getPageWrapper().fadeOut(500);
        document.location = request.url;
    });
    page('/page/:uri*', logger);
    page('/*', CmfRoutingHelpers.pageIntroTransition);
    page.exit(CmfRoutingHelpers.pageExitTransition);

    /*var app = window.adminApp = new Pilot({
        el: $(document.body),
        selector:
            'a[href^="' + CmfConfig.rootUrl + '/"],' +
            'a[href^="' + document.location.origin + CmfConfig.rootUrl + '/"],' +
            '[data-nav]',
        production: !CmfConfig.isDebug,
        basePath: CmfConfig.rootUrl,
        useOnlyFirstMatchedRoute: true,
        reloadable: true
        //profile: true
        //useHistory: true
    });

    if (typeof CustomRoutes !== 'undefined' && typeof CustomRoutes.init === 'function') {
        CustomRoutes.init(app);
    }

    app
        .route('login', '/login', CmfControllers.loginController)
        .route('forgot_password', '/forgot_password', CmfControllers.forgotPasswordController)
        .route('replace_password', '/replace_password/:access_key', CmfControllers.replacePasswordController)
        .route('page', '/page/:uri*', CmfControllers.pageController)
        .route('logout', '/logout', function (event, request) {
            app.disableUrlChangeOnce = true;
            Utils.showPreloader(document.body);
            Utils.getPageWrapper().fadeOut(500);
            document.location = request.url;
        });

    ScaffoldsManager.init(app);

    app.on('404', function (event, request) {
        if (request.path === CmfConfig.rootUrl) {
            // only CMF knows where to redirect from root url
            document.location = CmfConfig.rootUrl;
            return;
        }
        if (CmfConfig.isDebug) {
            console.log('No route found for: ' + request.url);
            toastr.error('Page not found')
        }
        app.back(CmfConfig.rootUrl + '/login');
    }).on('routestart', function (event, request) {
        if (request.routeDetected && !app.disableUrlChangeOnce && request.url !== document.location.href) {
            Pilot.setLocation(request);
        }
        app.disableUrlChangeOnce = false;
        $('.modal.in').not('[data-close-on-nav="false"]').modal('hide');
    }).on('routerender', function (event, request) {
        Utils.highlightLinks(request.path);
        // call custom handler if exists
        if (typeof CustomUtils !== 'undefined' && typeof CustomUtils.highlightLinks === 'function') {
            CustomUtils.highlightLinks.call(app, request);
        }
    });

    window.addEventListener('popstate', function(event) {
        if (app.ignoreDocumentHistoryPopStateOnce) {
            app.ignoreDocumentHistoryPopStateOnce = false;
            return;
        }
        if (document.location.pathname.match(new RegExp('^' + CmfConfig.rootUrl + '(/|$)'))) {
            app.nav(Pilot.getLocation());
        } else {
            document.location.reload();
        }
    });

    if (typeof CustomApp !== 'undefined' && typeof CustomApp.beforeStart === 'function') {
        CustomApp.beforeStart(app);
    }

    app.start();

    if (typeof CustomApp !== 'undefined' && typeof CustomApp.afterStart === 'function') {
        CustomApp.afterStart(app);
    }*/

    page();

});

function fixAdminLte() {
    // fix sidebar/content min-height fixer
    $.AdminLTE.layout.fix = function () {
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

    $.AdminLTE.layout.fix();

}