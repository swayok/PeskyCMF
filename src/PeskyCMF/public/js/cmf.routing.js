var CmfRoutingHelpers = {
    pageIntroTransition: function (request, next) {
        console.log('intro');
        Utils.showPreloader(Utils.getContentContainer);
        next();
    },
    pageExitTransition: function (request, next) {
        console.log('outro');
        Utils.showPreloader(Utils.getContentContainer);
        next();
    },
    $currentContentContainer: Utils.getPageWrapper(),
    $currentContent: null,
    setCurrentContentContainer: function ($el) {
        if (
            CmfRoutingHelpers.$currentContentContainer.is($el)
            && Utils.hasActivePreloader(CmfRoutingHelpers.$currentContentContainer)
        ) {
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            Utils.showPreloader($el);
        }
        CmfRoutingHelpers.$currentContentContainer = $el;
    },
    setCurrentContent: function ($el, $container) {
        var deferred = $.Deferred();
        if (CmfRoutingHelpers.$currentContent) {
            if ($container) {
                CmfRoutingHelpers.setCurrentContentContainer($container);
            }
            if (CmfRoutingHelpers.$currentContent.is($el)) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                deferred.resolve($el);
            } else {
                Utils.fadeOut(CmfRoutingHelpers.$currentContent, function () {
                    CmfRoutingHelpers.$currentContent.remove();
                    CmfRoutingHelpers.$currentContent = $el;
                    CmfRoutingHelpers.$currentContent.fadeOut();
                    CmfRoutingHelpers.$currentContentContainer.append($el);
                    Utils.fadeIn(CmfRoutingHelpers.$currentContent, function () {
                        Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                        deferred.resolve($el);
                    });
                });
            }
        } else {
            CmfRoutingHelpers.$currentContent = $el;
            Utils.fadeOut(CmfRoutingHelpers.$currentContent, function () {
                CmfRoutingHelpers.$currentContentContainer.append($el);
                Utils.fadeIn(CmfRoutingHelpers.$currentContent, function () {
                    Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                    deferred.resolve($el);
                });
            });

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
        .done(function (html, b) {
            var $content = $(html[0]);
            CmfRoutingHelpers.setCurrentContent($content, Utils.getPageWrapper());
            Utils.switchBodyClass(
                'login-page ' + 'the-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                'not-authorised'
            );
            Utils.updatePageTitleFromH1($content);
            var $form = $content.find('form');
            if ($form.length) {
                FormHelper.initForm($form, CmfRoutingHelpers.$currentContentContainer, function (json) {
                    Utils.cleanCache();
                    Utils.handleAjaxSuccess(json);
                });
            }
            next();
        });
};