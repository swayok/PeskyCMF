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
    setCurrentContent: function ($el, $container) {
        var deferred = $.Deferred();
        $el.hide();
        if ($container) {
            CmfRoutingHelpers.setCurrentContentContainer($container);
        }
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
            var $content = $(html);
            CmfRoutingHelpers.setCurrentContent($content, Utils.getPageWrapper());
            Utils.switchBodyClass(
                'login-page cmf-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                'authorisation'
            );
            Utils.updatePageTitleFromH1($content);
            var $form = $content.find('form');
            if ($form.length) {
                FormHelper.initForm($form, CmfRoutingHelpers.$currentContentContainer, function (json) {
                    Utils.cleanCache();
                    Utils.handleAjaxSuccess(json);
                });
            }
            CmfRoutingHelpers.routeHandled(request, next);
        });
};

CmfRouteChange.logout = function (request, next) {
    Utils.showPreloader(document.body);
    Utils.getPageWrapper().fadeOut(500);
    document.location = request.url;
};

CmfRouteChange.showPage = function (request, next) {
    $.when(
            Utils.downloadHtml(request.canonicalPath, true, false),
            AdminUI.showUI()
        )
        .done(function (html) {
            var $content = $(html);
            CmfRoutingHelpers.setCurrentContent($content, Utils.getPageWrapper());
            Utils.switchBodyClass(
                'login-page cmf-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                'authorisation'
            );
            Utils.updatePageTitleFromH1($content);
            var $form = $content.find('form');
            if ($form.length) {
                FormHelper.initForm($form, CmfRoutingHelpers.$currentContentContainer, function (json) {
                    Utils.cleanCache();
                    Utils.handleAjaxSuccess(json);
                });
            }
            CmfRoutingHelpers.routeHandled(request, next);
        });
};