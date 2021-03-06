$(function () {

    var cmfApp = typeof CmfApp !== 'undefined' ? CmfApp : {};

    if (typeof cmfApp.fixAdminLte === 'function') {
        cmfApp.fixAdminLte();
    } else {
        $('body').layout('fix');
        $('body [data-toggle="tooltip"]').tooltip();
    }

    if (typeof cmfApp.extendRouter === 'function') {
        cmfApp.extendRouter();
    }

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

    if (typeof cmfApp.addRoutes === 'function') {
        cmfApp.addRoutes();
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

    if (typeof cmfApp.beforeStart === 'function') {
        cmfApp.beforeStart();
    }

    page.start({
        decodeURLQuery: true
    });

    if (typeof cmfApp.afterStart === 'function') {
        cmfApp.afterStart();
    }

    if (CmfConfig.enablePing) {
        var pingInterval = setInterval(function () {
            $.ajax({
                    url: CmfConfig.rootUrl + '/ping',
                    method: 'POST',
                    data: {
                        url: page.currentUrl().replace(/[?#].*/g)
                    },
                    type: 'json'
                })
                .done(function (json) {
                    Utils.handleAjaxSuccess(json);
                })
                .fail(function (xhr) {
                    if (xhr.status === 404) {
                        clearInterval(pingInterval);
                    } else if (xhr.status === 401) {
                        // not authorized
                        toastr.error(CmfConfig.getLocalizationString(
                            'error.session_timed_out',
                            'Your session has timed out. Page will be reloaded in 5 seconds.'
                        ));
                        setTimeout(function () {
                            document.location.reload(true);
                        }, 5000);
                        Utils.showPreloader($(document.body));
                        clearInterval(pingInterval);
                    } else if (xhr.status === 419) {
                        // CSRF token missmatch or session expired
                        toastr.error(CmfConfig.getLocalizationString(
                            'error.csrf_token_missmatch',
                            'Your session has timed out. Page will be reloaded in 5 seconds.'
                        ));
                        setTimeout(function () {
                            document.location.reload(true);
                        }, 5000);
                        Utils.showPreloader($(document.body));
                        clearInterval(pingInterval);
                    }
                });
        }, CmfConfig.pingInterval);
    }

});