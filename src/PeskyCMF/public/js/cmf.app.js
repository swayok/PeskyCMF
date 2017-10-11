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
    page.exit(function (prevRequest, currentRequest) {
        CmfRoutingHelpers.cleanupHangedElementsInBody();
        CmfRoutingHelpers.pageExitTransition(prevRequest, currentRequest);
    });

    if (typeof CustomRoutes !== 'undefined' && typeof CustomRoutes.init === 'function') {
        CustomRoutes.init();
    }

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

    page.error('*', function (request) {
        request.errorHandled = false;
        CmfRoutingHelpers.hideContentContainerPreloader();
    });

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

    page.start({
        decodeURLQuery: true
    });

    if (typeof CustomApp !== 'undefined' && typeof CustomApp.afterStart === 'function') {
        CustomApp.afterStart();
    }

});

function fixAdminLte() {
    /*

    // to fix sidebar menu dropdown closing
    var original = $.AdminLTE.tree;
    $.AdminLTE.tree = function (menu) {
        original.call(this, menu);
        $(menu).on('click', 'li a', function (e) {
            $.AdminLTE.layout.fix();
            setTimeout($.AdminLTE.layout.fix, $.AdminLTE.options.animationSpeed + 10);
        });
    };*/

    $('body').layout('fix');
}

function extendRouter() {

}