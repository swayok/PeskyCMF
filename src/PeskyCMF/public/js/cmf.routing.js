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
    wrapContent: function (html) {
        if (typeof html === 'string') {
            return $('<div>').addClass(CmfConfig.contentWrapperCssClass).html(html);
        } else if (typeof html === 'function') {
            if (html.jquery) {
                return html;
            } else {
                return $('<div>').addClass(CmfConfig.contentWrapperCssClass).html(html());
            }
        }
        console.error('CmfRoutingHelpers.wrapContent(): html argument is not a string, jquery element of function');
        return false;
    },
    /**
     * @param html - string or funciton (jquery element or function that renders html)
     * @param $container - jquery element
     * @return {Deferred}
     */
    setCurrentContent: function (html, $container) {
        var deferred = $.Deferred();
        var $el = CmfRoutingHelpers.wrapContent(html);
        if ($el === false) {
            return deferred.reject();
        }
        $el.hide();
        if ($container) {
            CmfRoutingHelpers.setCurrentContentContainer($container);
        }
        Utils.updatePageTitleFromH1($el);
        var switchContent = function ($el, deferred) {
            CmfRoutingHelpers.$currentContent = $el;
            CmfRoutingHelpers.$currentContentContainer.append($el);
            deferred.resolve(CmfRoutingHelpers.$currentContent);
            Utils.fadeIn(CmfRoutingHelpers.$currentContent, function () {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
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
            CmfRoutingHelpers.setCurrentContent(html, Utils.getPageWrapper())
                .done(function ($content) {
                    Utils.switchBodyClass(
                        'login-page cmf-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                        'authorisation'
                    );
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
            CmfRoutingHelpers
                .setCurrentContent(html, Utils.getContentContainer())
                .done(function () {
                    Utils.switchBodyClass(
                        'page-' + request.params.uri.replace(/[^a-zA-Z0-9]+/, '-'),
                        'page'
                    );
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

CmfRouteChange.scaffoldItemCustomPage = function (request, next) {
    $.when(
            Utils.downloadHtml(request.canonicalPath, false, false),
            AdminUI.showUI(request.canonicalPath)
        )
        .done(function (html) {
            CmfRoutingHelpers
                .setCurrentContent(html, Utils.getContentContainer())
                .done(function () {
                    Utils.switchBodyClass(
                        'resource-' + request.params.resource + '-page-' + request.params.page,
                        'resource:page',
                        request.params.id
                    );
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

CmfRouteChange.scaffoldDataGridPage = function (request, next) {
    var bodyClass = 'resource-' + request.params.resource;
    var $body = $(document.body);
    if (
        $body.attr('data-modal-opened') === '1'
        && Utils.getCurrentSectionName() === 'resource:table'
        && Utils.getBodyClass() === bodyClass
    ) {
        // modal was closed
        $body.attr('data-modal-opened', '0');
        Utils.updatePageTitleFromH1(CmfRoutingHelpers.$currentContent);
        Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
        CmfRoutingHelpers.routeHandled(request, next);
    } else {
        $.when(
                ScaffoldsManager.getDataGridTpl(request.params.resource),
                AdminUI.showUI(request.canonicalPath)
            )
            .done(function (html) {
                CmfRoutingHelpers
                    .setCurrentContent(html, Utils.getContentContainer())
                    .done(function () {
                        Utils.switchBodyClass(bodyClass, 'resource:table');
                    });
                CmfRoutingHelpers.routeHandled(request, next);
            })
            .fail(function () {
                if (CmfRoutingHelpers.$currentContentContainer) {
                    Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                }
                CmfRoutingHelpers.routeHandled(request, next);
            });
    }
};

CmfRouteChange.scaffoldItemDetailsPage = function (request, next) {
    $.when(
            ScaffoldsManager.getItemDetailsTpl(request.params.resource),
            ScaffoldsManager.getResourceItemData(request.params.resource, request.params.id, true),
            AdminUI.showUI(request.canonicalPath)
        )
        .done(function (dotJsTpl, data) {
            var initContent = function ($content) {
                ScaffoldActionsHelper.initActions($content);
                var customInitiator = $content.find('.item-details-tabsheet-container').attr('data-initiator');
                if (customInitiator && customInitiator.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
                    eval('customInitiator = ' + customInitiator);
                    if (typeof customInitiator === 'function') {
                        customInitiator.call($content);
                    }
                }
            };
            if (data.__modal && Utils.getCurrentSectionName() === 'resource:table') {
                var $content = CmfRoutingHelpers.wrapContent(dotJsTpl(data));
                if ($content !== false) {
                    var $modal = $content.find('.modal').modal({
                        backdrop: 'static',
                        show: false
                    });
                    $(document.body).append($modal);
                    $modal.on('hidden.bs.modal', function () {
                        $modal.remove();
                        page.back();
                    });
                    initContent($modal);
                    Utils.updatePageTitleFromH1($modal);
                    $modal.modal('show');
                    $(document.body).attr('data-modal-opened', '1');
                }
                if (CmfRoutingHelpers.$currentContentContainer) {
                    Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
                }
            } else {
                data.__modal = false;
                CmfRoutingHelpers
                    .setCurrentContent(
                        dotJsTpl(data),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        Utils.switchBodyClass(
                            'resource-' + request.params.resource,
                            'resource:page',
                            request.params.id
                        );
                        initContent($content);
                    });
            }
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            if (CmfRoutingHelpers.$currentContentContainer) {
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            }
            CmfRoutingHelpers.routeHandled(request, next);
        });
};

CmfRouteChange.scaffoldItemFormPage = function (request, next) {
    var itemId = !request.params.id || request.params.id === 'create' ? null : request.params.id;
    $.when(
            ScaffoldsManager.getItemFormTpl(request.params.resource),
            ScaffoldsManager.getResourceItemData(request.params.resource, itemId, false),
            ScaffoldFormHelper.loadOptions(request.params.resource, itemId),
            AdminUI.showUI(request.canonicalPath)
        )
        .done(function (dotJsTpl, data, options) {
            CmfRoutingHelpers
                .setCurrentContent(
                    function () {
                        data._options = options;
                        data._is_creation = !itemId;
                        return dotJsTpl(data);
                    },
                    Utils.getContentContainer()
                ).done(function ($content) {
                    Utils.switchBodyClass(
                        'resource-' + request.params.resource,
                        'resource:form',
                        itemId
                    );
                    ScaffoldActionsHelper.initActions($content);
                    ScaffoldFormHelper.initForm($content.find('form'), function (json, $form) {
                        if (json._message) {
                            toastr.success(json._message);
                        }
                        if (json.redirect) {
                            if (json.redirect === 'reload') {
                                page.reload();
                            } else {
                                page.show(json.redirect);
                            }
                        } else {
                            page.back($form.attr('data-back-url'));
                        }
                    });
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