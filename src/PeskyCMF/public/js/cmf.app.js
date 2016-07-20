$(document).ready(function () {

    fixAdminLte();

    if (CmfSettings) {
        $.extend(GlobalVars, CmfSettings);
    }

    if (GlobalVars.isDebug) {
        Utils.initDebuggingTools();
    }

    Utils.configureAppLibs();

    Pilot.pushState = true;

    var app = window.adminApp = new Pilot({
        el: $(document.body),
        selector:
            'a[href^="' + GlobalVars.rootUrl + '/"],' +
            'a[href^="' + document.location.origin + GlobalVars.rootUrl + '/"],' +
            '[data-nav]',
        production: !GlobalVars.isDebug,
        basePath: GlobalVars.rootUrl,
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
        if (request.path === GlobalVars.rootUrl) {
            // only CMF knows where to redirect from root url
            document.location = GlobalVars.rootUrl;
            return;
        }
        if (GlobalVars.isDebug) {
            console.log('No route found for: ' + request.url);
            toastr.error('Page not found')
        }
        app.back(GlobalVars.rootUrl + '/login');
    }).on('routestart', function (event, request) {
        if (request.routeDetected && !app.disableUrlChangeOnce && request.url !== document.location.href) {
            Pilot.setLocation(request);
        }
        Utils.highlightLinks(request.path);
        app.disableUrlChangeOnce = false;
        $('.modal.in').not('[data-close-on-nav="false"]').modal('hide');
    });

    window.addEventListener('popstate', function(event) {
        if (document.location.pathname.match(new RegExp('^' + GlobalVars.rootUrl + '(/|$)'))) {
            app.nav(Pilot.getLocation());
        } else {
            document.location.reload();
        }
    });

    app.start();

});

function fixAdminLte() {
    // fix sidebar/content min-height fixer
    $.AdminLTE.layout.fix = function () {
        // Get heights
        var headerHeight = $('.main-header').outerHeight() || 0;
        var headerLogoHeight = $('.main-header .logo').outerHeight() || 0;
        var footerHeight = $('.main-footer').outerHeight() || 0;
        var windowHeight = $(window).height();
        var sidebarHeight = $(".sidebar").outerHeight() + headerLogoHeight;
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

}