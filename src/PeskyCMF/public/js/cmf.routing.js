var CmfRoutingHelpers = {
    pageExitTransition: function (request, next) {
        console.log('outro');
        Utils.showPreloader(CmfRoutingHelpers.$currentContentContainer);
        next();
    },
    routeHandled: function (request, next) {
        request.handled = true;
        next();
    },
    $currentContentContainer: Utils.getPageWrapper(),
    $currentContent: null,
    setCurrentContentContainer: function ($el) {
        if (!CmfRoutingHelpers.$currentContentContainer.is($el)) {
            if (Utils.hasActivePreloader(CmfRoutingHelpers.$currentContentContainer)) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                Utils.showPreloader($el);
            }
            CmfRoutingHelpers.$currentContentContainer = $el;
        }
    },
    /**
     * @param html - string or funciton (jquery element or function that renders html)
     * @param $container - jquery element
     * @return {Deferred}
     */
    setCurrentContent: function (html, $container) {
        var deferred = $.Deferred();
        var $el;
        if (typeof html === 'string') {
            $el = $('<div>').addClass(CmfConfig.contentWrapperCssClass).html(html);
        } else if (typeof html === 'function') {
            if (html.jquery) {
                $el = html;
            } else {
                $el = $('<div>').addClass(CmfConfig.contentWrapperCssClass).html(html());
            }
        } else {
            console.error('CmfRoutingHelpers.setCurrentContent(): html argument is not a string, jquery element of function');
            deferred.reject();
            return deferred;
        }
        $el.hide();
        if ($container) {
            CmfRoutingHelpers.setCurrentContentContainer($container);
        }
        Utils.updatePageTitleFromH1($el);
        var switchContent = function ($el, deferred) {
            CmfRoutingHelpers.$currentContent = $el;
            CmfRoutingHelpers.$currentContentContainer.append($el);
            Utils.fadeIn(CmfRoutingHelpers.$currentContent, function () {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                deferred.resolve(CmfRoutingHelpers.$currentContent);
            });
        };
        if (CmfRoutingHelpers.$currentContent) {
            if (CmfRoutingHelpers.$currentContent.is($el)) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                deferred.resolve($el);
            } else {
                Utils.fadeOut(CmfRoutingHelpers.$currentContent, function () {
                    CmfRoutingHelpers.$currentContent.remove();
                    switchContent($el, deferred);
                });
            }
        } else {
            switchContent($el, deferred);
        }
        return deferred;
    }
};

var CmfRouteChange = {

};

CmfRouteChange.authorisationPage = function (request, next) {
    $.when(
            Utils.downloadHtml(request.canonicalPath, true, false),
            AdminUI.destroyUI()
        )
        .done(function (html) {
            Utils.switchBodyClass(
                'login-page cmf-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                'authorisation'
            );
            CmfRoutingHelpers.setCurrentContent(html, Utils.getPageWrapper())
                .done(function ($content) {
                    var $form = $content.find('form');
                    if ($form.length) {
                        FormHelper.initForm($form, CmfRoutingHelpers.$currentContentContainer, function (json) {
                            Utils.cleanCache();
                            Utils.handleAjaxSuccess(json);
                        });
                    }
                });
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            if (CmfRoutingHelpers.$currentContentContainer) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            }
            CmfRoutingHelpers.routeHandled(request, next);
        });
};

CmfRouteChange.logout = function (request, next) {
    Utils.showPreloader(document.body);
    Utils.getPageWrapper().fadeOut(500);
    document.location = request.canonicalPath;
};

CmfRouteChange.showPage = function (request, next) {
    $.when(
            Utils.downloadHtml(request.canonicalPath, false, false),
            AdminUI.showUI(request.canonicalPath)
        )
        .done(function (html) {
            Utils.switchBodyClass(
                'page-' + request.params.uri.replace(/[^a-zA-Z0-9]+/, '-'),
                'page'
            );
            CmfRoutingHelpers.setCurrentContent(html, Utils.getContentContainer());
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            if (CmfRoutingHelpers.$currentContentContainer) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            }
            CmfRoutingHelpers.routeHandled(request, next);
        });
};