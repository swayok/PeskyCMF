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
        Utils.hidePreloader(Utils.getContentContainer());
        Utils.hidePreloader(CmfRoutingHelpers.$currentContentContainer);
    },
    cleanupHangedElementsInBody: function () {
        $('body > .tooltip, body > .bs-container.bootstrap-select').remove();
        $('body > div > .g-recaptcha-bubble-arrow').parent().remove();
    },
    cleanupHangedElementsInContentWrapper: function () {
        Utils.getContentContainer().find('.tooltip, .bs-container.bootstrap-select').remove();
    },
    hideModals: function () {
        var deferred = $.Deferred();
        if (CmfRoutingHelpers.$currentContent && CmfRoutingHelpers.$currentContent.hasClass('modal')) {
            CmfRoutingHelpers.$currentContent
                .data('closed-automatically', '1')
                .one('hidden.bs.modal', function () {
                    deferred.resolve();
                })
                .modal('hide');
        } else {
            deferred.resolve();
        }
        return deferred.promise();
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
            deferred.reject(new Error('Failed to wrap content'));
            return deferred.promise();
        }
        $el.hide();
        if ($container) {
            CmfRoutingHelpers.setCurrentContentContainer($container);
        }
        Utils.updatePageTitleFromH1($el);
        var switchContent = function ($el, deferred) {
            CmfRoutingHelpers.$currentContent = $el;
            CmfRoutingHelpers.$currentContentContainer.html('').append($el);
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
        return deferred.promise();
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
            deferred.reject(new Error('Current content is not defined yet'));
            return deferred.promise();
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
            return deferred.promise();
        }
        var $prevContentContainer = CmfRoutingHelpers.$currentContentContainer;
        var $prevContent = CmfRoutingHelpers.$currentContent;
        $modal
            .modal({
                backdrop: 'static',
                show: false
            })
            .on('hide.bs.modal', function () {
                var closedAutomatically = !!$modal.data('closed-automatically');
                CmfRoutingHelpers.$currentContent = $prevContent;
                CmfRoutingHelpers.setCurrentContentContainer($prevContentContainer);
                if (!closedAutomatically) {
                    request.restoreParentRequest(true);
                }
            })
            .on('hidden.bs.modal', function () {
                $modal.remove();
            })
            .on('show.bs.modal', function () {
                CmfRoutingHelpers.$currentContent = $modal;
                CmfRoutingHelpers.setCurrentContentContainer($modal.find('.modal-dialog'));
                Utils.updatePageTitleFromH1($modal);
            })
            .on('shown.bs.modal', function () {
                $('body').addClass('modal-open');
            });
        $(document.body).append($modal);
        deferred.resolve($modal);
        return deferred.promise();
    },
    closeCurrentModalAndReloadDataGrid: function () {
        CmfRoutingHelpers.hideModals();
        ScaffoldDataGridHelper.reloadCurrentDataGrid();
    },
    renderModalFromHtml: function (request, html, modalId, defaultModalSize, allowReload) {
        var $content = CmfRoutingHelpers.wrapContent(html);
        var $footer = $content.find('.modal-footer').remove();
        var title = Utils.getTitleFromContent($content);
        $content.find('.content-header').remove();
        $content.find('h1').remove();
        var modalSize = null;
        if ($content.find('.content').length) {
            $content = $content.find('.content').removeClass('content');
            modalSize = $content.attr('data-modal-size');
        }
        if (!modalSize) {
            modalSize = defaultModalSize || 'large';
        }
        var $modal = Utils.makeModal(
            title,
            $content[0].outerHTML,
            $footer.length ? $footer.html() : null,
            modalSize,
            modalId + 'modal'
        );
        if (allowReload) {
            $modal.find('.reload-url-button').removeClass('hidden');
        }
        CmfRoutingHelpers
            .initModalAndContent($modal, request)
            .done(function () {
                $modal
                    .find('.reload-url-button, .open-url-in-new-tab-button')
                        .attr('href', request.makeUrlToUseItInParentRequest())
                        .attr('data-modal', '1');
                ScaffoldActionsHelper.initActions($modal);
                $modal.modal('show');
                $(document.body).attr('data-modal-opened', '1');
            });
        CmfRoutingHelpers.hideContentContainerPreloader();
    }
};

var CmfRouteChange = {

};

CmfRouteChange.authorizationPage = function (request) {
    if (request.hasSubRequest()) {
        request.removeSubRequest();
        request.saveState();
    }
    return $.when(
            Utils.downloadHtml(request.pathname, true, false, request.querystring),
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
    var switchBodyClass = function () {
        Utils.switchBodyClass(
            'page-' + request.params.uri.replace(/[^a-zA-Z0-9]+/, '-'),
            'page'
        );
    };

    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        switchBodyClass();
        Utils.updatePageTitleFromH1(Utils.getContentContainer());
        return;
    }

    var isModal = request.isSubRequest();
    if (!isModal && request.env().is_click && request.env().target && $(request.env().target).attr('data-modal')) {
        isModal = true;
    }

    var queryString = request.querystring;
    if (isModal) {
        if (queryString && queryString.length > 1) {
            queryString += '&modal=1';
        } else {
            queryString = 'modal=1';
        }
    }

    return $.when(
            Utils.downloadHtml(request.pathname, false, false, queryString),
            AdminUI.showUI()
        )
        .done(function (html) {
            if (!isModal) {
                CmfRoutingHelpers
                    .setCurrentContent(html, Utils.getContentContainer())
                    .done(function () {
                        switchBodyClass();
                    });
            } else {
                var modalId = 'page-' + request.params.page;
                CmfRoutingHelpers.renderModalFromHtml(request, html, modalId, 'large', true);
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldResourceCustomPage = function (request) {
    var switchBodyClass = function () {
        if (request.params.id) {
            Utils.switchBodyClass(
                ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource) + ' resource-item-page-' + request.params.page,
                'resource:item-page',
                request.params.id
            );
        } else {
            Utils.switchBodyClass(
                ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource) + ' resource-page-' + request.params.page,
                'resource:page'
            );
        }
    };

    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        switchBodyClass();
        Utils.updatePageTitleFromH1(Utils.getContentContainer());
        return;
    }

    var isModal = request.isSubRequest();
    if (!isModal && request.env().is_click && request.env().target && $(request.env().target).attr('data-modal')) {
        isModal = true;
    }

    var queryString = request.querystring;
    if (isModal) {
        if (queryString && queryString.length > 1) {
            queryString += '&modal=1'
        } else {
            queryString = 'modal=1'
        }
    }

    return $.when(
            Utils.downloadHtml(request.pathname, false, false, queryString),
            AdminUI.showUI()
        )
        .done(function (html) {
            if (!isModal) {
                CmfRoutingHelpers
                    .setCurrentContent(html, Utils.getContentContainer())
                    .done(function () {
                        switchBodyClass();
                    });
            } else {
                var modalId = ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource);
                if (request.params.id) {
                    modalId += '-item';
                }
                modalId += '-page-' + request.params.page;
                CmfRoutingHelpers.renderModalFromHtml(request, html, modalId, 'large', true);
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldDataGridPage = function (request) {
    var switchBodyClass = function () {
        Utils.switchBodyClass(
            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
            'resource:table'
        );
    };
    if (
        request.customData.is_state_save
        || request.env().is_restore
        || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))
    ) {
        CmfRoutingHelpers.routeHandled(request);
        switchBodyClass();
        Utils.updatePageTitleFromH1(Utils.getContentContainer());
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
        return;
    } else {
        return $.when(
                ScaffoldsManager.getDataGridTpl(request.params.resource),
                AdminUI.showUI()
            )
            .done(function (html) {
                CmfRoutingHelpers
                    .setCurrentContent(html, Utils.getContentContainer())
                    .done(function () {
                        switchBodyClass();
                    });
                CmfRoutingHelpers.routeHandled(request);
            });
    }
};

CmfRouteChange.scaffoldItemDetailsPage = function (request) {
    var switchBodyClass = function () {
        Utils.switchBodyClass(
            ScaffoldActionsHelper.makeResourceBodyClass(request.params.resource),
            'resource:details',
            request.params.id
        );
    };
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        switchBodyClass();
        Utils.updatePageTitleFromH1(Utils.getContentContainer());
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
            if (
                request.isSubRequest()
                || (
                    data.__modal
                    && request.env().is_click
                    && request.env().is_modal !== false
                )
            ) {
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
                                            page.show(
                                                nextRow.data().___details_url,
                                                null,
                                                true,
                                                true,
                                                {env: {is_click: true, target: $nextItemBtn[0], clarify: 'next-item-details'}}
                                            );
                                        });
                                } else {
                                    $nextItemBtn.prop('disabled', true);
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
                                            page.show(
                                                prevRow.data().___details_url,
                                                null,
                                                true,
                                                true,
                                                {env: {is_click: true, target: $prevItemBtn[0], clarify: 'prev-item-details'}}
                                            );
                                        });
                                } else {
                                    $prevItemBtn.prop('disabled', true);
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
                        switchBodyClass();
                        initContent($content);
                    });
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldItemFormPage = function (request) {
    var switchBodyClass = function () {
        Utils.switchBodyClass(
            ScaffoldActionsHelper.makeResourceBodyClass(resource),
            'resource:form',
            itemId
        );
    };
    if (request.env().is_restore || (request.env().is_history && request.hasSamePathAs(page.previousRequest()))) {
        CmfRoutingHelpers.routeHandled(request);
        switchBodyClass();
        Utils.updatePageTitleFromH1(Utils.getContentContainer());
        return;
    }
    var itemId = !request.params.id || request.params.id === 'create' ? null : request.params.id;
    var resource = request.params.resource;
    var isClone = !!request.env().is_clone;
    return $.when(
            ScaffoldsManager.getItemFormTpl(resource),
            ScaffoldsManager.getResourceItemData(resource, itemId, false),
            ScaffoldFormHelper.loadOptions(resource, itemId),
            AdminUI.showUI()
        )
        .done(function (dotJsTpl, data, options) {
            data._is_cloning = isClone;
            data._is_creation = data._is_cloning || !itemId;
            var renderTpl = function (data, options, isModal) {
                data.__modal = !!isModal;
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
                return dotJsTpl(data);
            };
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
                                Utils.showPreloader($content.find('.modal-dialog'));
                                $.when(
                                    ScaffoldsManager.getResourceItemData(resource, itemId, false),
                                    ScaffoldFormHelper.loadOptions(resource, itemId)
                                )
                                    .done(function (data, options) {
                                        data._options = options;
                                        data._is_creation = !itemId;
                                        var $newContent = $('<div></div>').html(renderTpl(data, options, true));
                                        initContent(
                                            $content
                                                .find('.modal-dialog')
                                                .html('')
                                                .append($newContent.find('.modal-content')),
                                            true
                                        );
                                    })
                                    .always(function () {
                                        Utils.hidePreloader($content.find('.modal-dialog'));
                                    })
                            } else {
                                page.reload();
                            }
                        } else if (json.redirect === 'back') {
                            if (isModal) {
                                $content.modal('hide');
                            } else {
                                page.back(json.redirect_fallback || $form.attr('data-back-url') || CmfConfig.rootUrl);
                            }
                        } else {
                            Utils.hidePreloader($form);
                            page.show(json.redirect, null, true, true, {env: {is_ajax_response: true, is_modal: isModal}});
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
            if (
                request.isSubRequest()
                || (
                    data.__modal
                    && request.env().is_modal !== false
                    && (
                        request.env().is_click
                        || request.env().is_ajax_response
                    )
                )
            ) {
                var $content = $('<div></div>').html(renderTpl(data, options, true));
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
                CmfRoutingHelpers
                    .setCurrentContent(
                        renderTpl(data, options, false),
                        Utils.getContentContainer()
                    ).done(function ($content) {
                        switchBodyClass();
                        initContent($content, false);
                    });
            }
            CmfRoutingHelpers.routeHandled(request);
        });
};

CmfRouteChange.scaffoldItemClone = function (request) {
    request.env().is_clone = true;
    return CmfRouteChange.scaffoldItemFormPage(request);
};
