$(function () {

    fixAdminLte();

    extendRouter();

    if (typeof CmfSettings === 'object') {
        // import localization strings
        if (CmfSettings.localizationStrings && $.isPlainObject(CmfSettings.localizationStrings)) {
            CmfConfig.setLocalizationStrings(CmfSettings.localizationStrings);
        }
        delete CmfSettings.localizationStrings;
        // merge with default configs
        $.extend(CmfConfig, CmfSettings);
    }

    if (typeof CmfTemplates === 'object') {
        // import templates
        ScaffoldsManager.importTemplatesFromCmfSettings(CmfTemplates);
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

    if (typeof CmfApp !== 'undefined' && typeof CmfApp.addRoutes === 'function') {
        CmfApp.addRoutes();
    }

    page.route('/login', CmfRouteChange.authorizationPage);
    page.route('/register', CmfRouteChange.authorizationPage);
    page.route('/forgot_password', CmfRouteChange.authorizationPage);
    page.route('/replace_password/:uid*', CmfRouteChange.authorizationPage);
    page.route('/logout', CmfRouteChange.logout, CmfRoutingHelpers.routeHandled);
    page.route('/page/:uri*', CmfRouteChange.showPage);

    page.route('/resource/:resource', CmfRouteChange.scaffoldDataGridPage);
    page.route('/resource/:resource/details/:id', CmfRouteChange.scaffoldItemDetailsPage);
    page.route('/resource/:resource/create', CmfRouteChange.scaffoldItemFormPage);
    page.route('/resource/:resource/edit/:id', CmfRouteChange.scaffoldItemFormPage);
    page.route('/resource/:resource/clone/:id', CmfRouteChange.scaffoldItemClone);
    page.route('/resource/:resource/page/:page', CmfRouteChange.scaffoldResourceCustomPage);
    page.route('/resource/:resource/:id/page/:page', CmfRouteChange.scaffoldResourceCustomPage);

    page.route('*', function (request) {
        $(document.body).trigger('change.page', {request: request});
        if (CmfRoutingHelpers.$currentContentContainer) {
            CmfRoutingHelpers.$currentContentContainer
                .find('[data-toggle="tooltip"]')
                .each(function () {
                    if ($(this).attr('data-container')) {
                        $(this).tooltip();
                    } else {
                        $(this).tooltip({
                            container: CmfRoutingHelpers.$currentContentContainer
                        });
                    }
                });
        }
    });

    page.error('*', function (request) {
        request.errorHandled = false;
        CmfRoutingHelpers.hideContentContainerPreloader();
    });

    if (CmfConfig.isDebug) {
        page.notFound('*', function (request) {
            if (request.path !== '/') {
                console.warn('Route not found', request);
            }
        });
    }

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

    if (typeof CmfApp !== 'undefined' && typeof CmfApp.beforeStart === 'function') {
        CmfApp.beforeStart();
    }

    page.start({
        decodeURLQuery: true
    });

    if (typeof CmfApp !== 'undefined' && typeof CmfApp.afterStart === 'function') {
        CmfApp.afterStart();
    }

});

function fixAdminLte() {
    $('body').layout('fix');
    $('body [data-toggle="tooltip"]').tooltip();
}

function extendRouter() {

}