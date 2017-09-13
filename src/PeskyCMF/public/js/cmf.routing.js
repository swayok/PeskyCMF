var CmfRoutingHelpers = {
    lastNonModalPageRequest: null,
    $currentContentContainer: Utils.getPageWrapper(),
    $currentContent: null,
    pageExitTransition: function (prevRequest, currentRequest) {
        if (!currentRequest.env().is_restore) {
            Utils.showPreloader(CmfRoutingHelpers.$currentContentContainer);
        }
        return CmfRoutingHelpers.hideModals();
    },
    hideContentContainerPreloader: function () {
        Utils.hidePreloader(Utils.getPageWrapper());
        Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
    },
    cleanupHangedElementsInBody: function () {
        $('body > .tooltip, body > .bootstrap-select').remove();
    },
    hideModals: function () {
        var deferred = $.Deferred();
        if (CmfRoutingHelpers.$currentContent && CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers.$currentContent
                .data('closed-automatically', '1')
                .on('hidden.bs.modal', function () {
                    deferred.resolve();
                })
                .modal('hide');
        } else {
            deferred.resolve();
        }
        return deferred;
    },
    routeHandled: function (request) {
        request.title = document.title;
        if (!CmfRoutingHelpers.$currentContent.hasClass('modal') && !request.env().is_restore) {
            CmfRoutingHelpers.lastNonModalPageRequest = request;
            Utils.highlightLinks(request);
        }
        CmfRoutingHelpers.hideContentContainerPreloader();
    },
    setCurrentContentContainer: function ($el) {
        if (!CmfRoutingHelpers.$currentContentContainer || !CmfRoutingHelpers.$currentContentContainer.is($el)) {
            if (Utils.hasActivePreloader(CmfRoutingHelpers.$currentContentContainer)) {
                CmfRoutingHelpers.hideContentContainerPreloader();
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
                CmfRoutingHelpers.hideContentContainerPreloader();
            });
        };
        if (CmfRoutingHelpers.$currentContent) {
            if (CmfRoutingHelpers.$currentContent.is($el)) {
                CmfRoutingHelpers.hideContentContainerPreloader();
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
    initModalAndContent: function ($modal, request) {
        var deferred = $.Deferred();
        // request.push = false;
        request.convertToSubrequest();
        if (!CmfRoutingHelpers.$currentContent) {
            CmfConfig.getDebugDialog().showDebug(
                'Unexpected application behavior detected',
                'Current content is not defined yet. Possibly you\'re trying to show content in modal dialog instead of current page. Trace printed to console.'
            );
            console.trace();
            deferred.reject();
            return deferred;
        }
        if (CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers
                .hideModals()
                .done(function () {
                    CmfRoutingHelpers
                        .initModalAndContent($modal, request)
                        .done(function () {
                            deferred.resolve($modal);
                        });
                });
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
                var closedAutomatically = !!$modal.data('closed-automatically');
                $modal.remove();
                CmfRoutingHelpers.$currentContent = $prevContent;
                CmfRoutingHelpers.setCurrentContentContainer($prevContentContainer);
                if (!closedAutomatically) {
                    request.restoreParentRequest(true);
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
        CmfRoutingHelpers.hideModals();
        ScaffoldDataGridHelper.reloadCurrentDataGrid();
    }
};

var CmfRouteChange = {

};

CmfRouteChange.authorizationPage = function (request) {
    return $.when(
            Utils.downloadHtml(request.pathname, true, false),
            AdminUI.destroyUI()
        )
        .done(function (html) {
            CmfRoutingHelpers.setCurrentContent(html, Utils.getPageWrapper())
                .done(function ($content) {
                    Utils.switchBodyClass(
                        'login-page cmf-' + request.path.replace(/[^a-zA-Z0-9]+/, '-').toLowerCase() + '-page',
                        'authorization'
                    );
                    var $form = $content.find('form');
                    if ($form.length) {
                        FormHelper.initForm($form, CmfRoutingHelpers.$currentContentContainer, function (json) {
                            Utils.cleanCache();
                            Utils.handleAjaxSuccess(json);
                        });
                    }
                });
            CmfRoutingHelpers.routeHandled(request);
        })
        .promise();
};

CmfRouteChange.logout = function (request) {
    Utils.showPreloader(document.body);
    Utils.getPageWrapper().fadeOut(500);
    document.location = request.fullUrl();
};

CmfRouteChange.showPage = function (request) {
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        return;
    }
    return $.when(
            Utils.downloadHtml(request.pathname, false, false),
            AdminUI.showUI()
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
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldItemCustomPage = function (request) {
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        return;
    }
    $.when(
            Utils.downloadHtml(request.pathname, false, false),
            AdminUI.showUI()
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
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldDataGridPage = function (request) {
    if (
        request.customData.is_state_save
        || request.env().is_restore
        || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))
    ) {
        CmfRoutingHelpers.routeHandled(request);
        return;
    }
    if (
        !request.env().is_click
        && !request.env().is_reload
        && CmfRoutingHelpers.lastNonModalPageRequest
        && CmfRoutingHelpers.lastNonModalPageRequest.pathname === request.pathname
    ) {
        Utils.updatePageTitleFromH1(CmfRoutingHelpers.$currentContent);
        CmfRoutingHelpers.hideContentContainerPreloader();
        if (CmfRoutingHelpers.lastNonModalPageRequest.querystring !== request.querystring) {
            // different state of data grid - needs data grid state replace and data reload
            ScaffoldDataGridHelper.reloadStateOfCurrentDataGrid(function () {
                CmfRoutingHelpers.routeHandled(request);
            });
        } else {
            CmfRoutingHelpers.routeHandled(request);
        }
    } else {
        return $.when(
                ScaffoldsManager.getDataGridTpl(request.params.resource),
                AdminUI.showUI()
            )
            .done(function (html) {
                CmfRoutingHelpers
                    .setCurrentContent(html, Utils.getContentContainer())
                    .done(function () {
                        Utils.switchBodyClass(
                            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
                            'resource:table'
                        );
                    });
                CmfRoutingHelpers.routeHandled(request);
            });
    }
};

CmfRouteChange.scaffoldItemDetailsPage = function (request) {
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        return;
    }
    return $.when(
            ScaffoldsManager.getItemDetailsTpl(request.params.resource),
            ScaffoldsManager.getResourceItemData(request.params.resource, request.params.id, true),
            AdminUI.showUI()
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
            if ((data.__modal && request.env().is_click) || request.isSubRequest()) {
                data.__modal = true;
                var $content = $('<div></div>').html(dotJsTpl(data));
                if ($content !== false) {
                    var $modal = $content.find('.modal');
                    CmfRoutingHelpers
                        .initModalAndContent($modal, request)
                        .done(function () {
                            initContent($modal);
                            $modal.modal('show');
                            $(document.body).attr('data-modal-opened', '1');
                            var datagridApi = ScaffoldDataGridHelper.getCurrentDataGridApi();
                            if (datagridApi && data.DT_RowId && datagridApi.settings()[0].resourceName === request.params.resource) {
                                var api = ScaffoldDataGridHelper.getCurrentDataGridApi();
                                var rowIndex = api.row('#' + data.DT_RowId).index();
                                var $prevItemBtn = $modal.find('button.prev-item');
                                var $nextItemBtn = $modal.find('button.next-item');
                                // enable next item button
                                var shiftNext = 1;
                                do {
                                    var nextRow = api.row(rowIndex + shiftNext);
                                    if (!nextRow.length || (nextRow.data().___details_allowed && nextRow.data().___details_url)) {
                                        break;
                                    }
                                    shiftNext++;
                                } while (nextRow.length);
                                if (nextRow.length) {
                                    $nextItemBtn
                                        .prop('disabled', false)
                                        .on('click', function () {
                                            event.preventDefault();
                                            $prevItemBtn.prop('disabled', true);
                                            $nextItemBtn.prop('disabled', true);
                                            page.show(nextRow.data().___details_url);
                                        });
                                }
                                // enable prev item button
                                var shiftPrev = 1;
                                do {
                                    var prevRow = api.row(rowIndex - shiftPrev);
                                    if (!prevRow.length || (prevRow.data().___details_allowed && prevRow.data().___details_url)) {
                                        break;
                                    }
                                    shiftNext++;
                                } while (prevRow.length);
                                if (prevRow.length) {
                                    $prevItemBtn
                                        .prop('disabled', false)
                                        .on('click', function (event) {
                                            event.preventDefault();
                                            $prevItemBtn.prop('disabled', true);
                                            $nextItemBtn.prop('disabled', true);
                                            page.show(prevRow.data().___details_url);
                                        });
                                }
                            }
                        });
                }
                CmfRoutingHelpers.hideContentContainerPreloader();
            } else {
                data.__modal = false;
                CmfRoutingHelpers
                    .setCurrentContent(
                        dotJsTpl(data),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        Utils.switchBodyClass(
                            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
                            'resource:details',
                            request.params.id
                        );
                        initContent($content);
                    });
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldItemFormPage = function (request) {
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        return;
    }
    var itemId = !request.params.id || request.params.id === 'create' ? null : request.params.id;
    var resource = request.params.resource;
    return $.when(
            ScaffoldsManager.getItemFormTpl(resource),
            ScaffoldsManager.getResourceItemData(resource, itemId, false),
            ScaffoldFormHelper.loadOptions(resource, itemId),
            AdminUI.showUI()
        )
        .done(function (dotJsTpl, data, options) {
            var initContent = function ($content, isModal) {
                ScaffoldActionsHelper.initActions($content, false);
                ScaffoldFormHelper.initForm($content.find('form'), function (json, $form) {
                    ScaffoldFormHelper.cleanOptions(resource, itemId);
                    if (json._message) {
                        toastr.success(json._message);
                    }
                    if (json.redirect) {
                        if (json.redirect === 'reload') {
                            if (isModal) {
                                Utils.showPreloader($content);
                                ScaffoldsManager.getResourceItemData(resource, itemId, false)
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
            data._is_creation = !itemId;
            if (data._is_creation) {
                // add alowed query args to data so that parogrammer can pass default values for inputs through query args
                for (var argName in request.query) {
                    if (argName.match(/^_/)) {
                        continue;
                    }
                    if (data.hasOwnProperty(argName)) {
                        data[argName] = request.query[argName];
                    }
                }
            }
            data._options = options;
            if ((data.__modal && request.env().is_click) || request.isSubRequest()) {
                data.__modal = true;
                var $content = $('<div></div>').html(dotJsTpl(data));
                if ($content !== false) {
                    var $modal = $content.find('.modal');
                    CmfRoutingHelpers
                        .initModalAndContent($modal, request)
                        .done(function () {
                            initContent($modal, true);
                            $modal.modal('show');
                            $(document.body).attr('data-modal-opened', '1');
                        });
                }
                CmfRoutingHelpers.hideContentContainerPreloader();
            } else {
                data.__modal = false;
                CmfRoutingHelpers
                    .setCurrentContent(
                        dotJsTpl(data),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        Utils.switchBodyClass(
                            ScaffoldActionsHelper.makeResourceBodyClass(resource),
                            'resource:form',
                            itemId
                        );
                        initContent($content, false);
                    });
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};