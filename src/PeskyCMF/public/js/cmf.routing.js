var CmfRoutingHelpers = {
    lastNonModalPageInfo: null,
    $currentContentContainer: Utils.getPageWrapper(),
    $currentContent: null,
    pageExitTransition: function (request, next) {
        Utils.showPreloader(CmfRoutingHelpers.$currentContentContainer);
        if (!CmfRoutingHelpers.lastNonModalPageInfo || !CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers.lastNonModalPageInfo = {
                url: location.pathname + location.search + location.hash,
                page_title: document.title
            };
        }
        next();
    },
    routeHandled: function (request, next) {
        request.handled = true;
        next();
    },
    setCurrentContentContainer: function ($el) {
        if (!CmfRoutingHelpers.$currentContentContainer || !CmfRoutingHelpers.$currentContentContainer.is($el)) {
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
    },
    initModalAndContent: function ($modal) {
        var deferred = $.Deferred();
        if (CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers.$currentContent
                .attr('data-ignore-back', '1')
                .on('hidden.bs.modal', function () {
                    CmfRoutingHelpers.initModalAndContent($modal).done(function () {
                        deferred.resolve($modal);
                    });
                })
                .modal('hide');
            return deferred;
        }
        var $prevContentContainer = CmfRoutingHelpers.$currentContentContainer;
        var $prevContent = CmfRoutingHelpers.$currentContent;
        $modal
            .modal({
                backdrop: 'static',
                show: false
            })
            .on('hidden.bs.modal', function () {
                $modal.remove();
                CmfRoutingHelpers.$currentContent = $prevContent;
                CmfRoutingHelpers.setCurrentContentContainer($prevContentContainer);
                if ($modal.attr('data-ignore-back') !== '1') {
                    if (CmfRoutingHelpers.lastNonModalPageInfo) {
                        page.show(CmfRoutingHelpers.lastNonModalPageInfo.url, null, false);
                        document.title = CmfRoutingHelpers.lastNonModalPageInfo.page_title;
                    } else {
                        page.back();
                    }
                }
            })
            .on('show.bs.modal', function () {
                CmfRoutingHelpers.$currentContent = $modal;
                CmfRoutingHelpers.setCurrentContentContainer($modal.find('.modal-dialog'));
                Utils.updatePageTitleFromH1($modal);
            });
        $(document.body).append($modal);
        return deferred.resolve($modal);
    },
    closeCurrentModalAndReloadDataGrid: function () {
        if (CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers.$currentContent.modal('hide');
            ScaffoldDataGridHelper.reloadCurrentDataGrid();
        }
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
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
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
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
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
                        ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource) + ' resource-page-' + request.params.page,
                        'resource:page',
                        request.params.id
                    );
                });
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            CmfRoutingHelpers.routeHandled(request, next);
        });
};

CmfRouteChange.scaffoldDataGridPage = function (request, next) {
    var bodyClass = ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource);
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
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
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
                var $content = $('<div></div>').html(dotJsTpl(data));
                if ($content !== false) {
                    var $modal = $content.find('.modal');
                    CmfRoutingHelpers
                        .initModalAndContent($modal)
                        .done(function () {
                            initContent($modal);
                            $modal.modal('show');
                            $(document.body).attr('data-modal-opened', '1');
                            // todo: enable prev/next buttons depending on data grid row used
                            $modal.find('button.prev-item').on('click', function () {
                                // todo: handle "prev" button click to scroll across datagrid items
                            });
                            $modal.find('button.next-item').on('click', function () {
                                // todo: handle "next" button click to scroll across datagrid items
                            });
                        });
                }
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            } else {
                data.__modal = false;
                CmfRoutingHelpers
                    .setCurrentContent(
                        dotJsTpl(data),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        Utils.switchBodyClass(
                            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
                            'resource:page',
                            request.params.id
                        );
                        initContent($content);
                    });
            }
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
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
            var initContent = function ($content, isModal) {
                ScaffoldActionsHelper.initActions($content, false);
                ScaffoldFormHelper.initForm($content.find('form'), function (json, $form) {
                    if (json._message) {
                        toastr.success(json._message);
                    }
                    if (json.redirect) {
                        if (json.redirect === 'reload') {
                            if (isModal) {
                                Utils.showPreloader($content);
                                ScaffoldsManager.getResourceItemData(request.params.resource, itemId, false)
                                    .done(function (data) {
                                        var $newContent = $('<div></div>').html(dotJsTpl(data));
                                        $content.html('').append($newContent.find('.modal-content'));
                                        initContent($content, true);
                                    })
                                    .always(function () {
                                        Utils.hidePreloader($content);
                                    })
                            } else {
                                page.reload();
                            }
                        } else {
                            if (isModal) {
                                $content
                                    .attr('data-ignore-back', '1')
                                    .on('hidden.bs.modal', function () {
                                        page.show(json.redirect);
                                    })
                                    .modal('hide');
                            } else {
                                page.show(json.redirect);
                            }
                        }
                    } else {
                        if (isModal) {
                            $content.modal('hide');
                        } else {
                            page.back($form.attr('data-back-url'));
                        }
                    }
                    if (isModal) {
                        ScaffoldDataGridHelper.reloadCurrentDataGrid();
                    }
                });
            };
            data._options = options;
            data._is_creation = !itemId;
            if (data.__modal && Utils.getCurrentSectionName() === 'resource:table') {
                var $content = $('<div></div>').html(dotJsTpl(data));
                if ($content !== false) {
                    var $modal = $content.find('.modal');
                    CmfRoutingHelpers
                        .initModalAndContent($modal)
                        .done(function () {
                            initContent($modal, true);
                            $modal.modal('show');
                            $(document.body).attr('data-modal-opened', '1');
                        });
                }
                Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            } else {
                data.__modal = false;
                CmfRoutingHelpers
                    .setCurrentContent(
                        dotJsTpl(data),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        Utils.switchBodyClass(
                            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
                            'resource:form',
                            itemId
                        );
                        initContent($content, false);
                    });
            }
            CmfRoutingHelpers.routeHandled(request, next);
        })
        .fail(function () {
            Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
            CmfRoutingHelpers.routeHandled(request, next);
        });
};