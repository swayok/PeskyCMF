var ScaffoldsManager = {
    cacheTemplates: true
};

ScaffoldsManager.getResourceBaseUrl = function (resourceName, additionalParameter) {
    return CmfConfig.rootUrl + '/' + CmfConfig.scaffoldApiUrlSection + '/' + ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)
};

ScaffoldsManager.buildResourceUrlSuffix = function (resourceName, additionalParameter) {
    return resourceName + (additionalParameter ? '/' + additionalParameter : '');
};

ScaffoldsManager.isValidResourceName = function (resourceName) {
    return typeof resourceName == 'string' && String(resourceName).match(/^[a-zA-Z_][a-zA-Z_0-9]+$/);
};

ScaffoldsManager.validateResourceName = function (resourceName, additionalParameter) {
    if (!ScaffoldsManager.isValidResourceName(resourceName)) {
        console.trace();
        throw 'Invalid REST resource name: ' + resourceName;
    }
    if (typeof additionalParameter !== 'undefined' && typeof additionalParameter !== 'string') {
        console.trace();
        throw 'Additional parameter must be a string: ' + (typeof additionalParameter) + ' received';
    }
};

ScaffoldsManager.findResourceNameInUrl = function (url) {
    var matches = url.match(/\/resource\/([^\/]+)/i);
    return !matches ? false : matches[0];
};

/* ============ Templates ============ */

$.extend(CmfCache, {
    rawTemplates: {},
    compiledTemplates: {
        itemForm: {},
        bulkEditForm: {},
        itemDetails: {}
    }
});

ScaffoldsManager.loadTemplates = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    if (!ScaffoldsManager.cacheTemplates || !ScaffoldsManager.isTemplatesLoaded(resourceName, additionalParameter)) {
        var resourceUrl = ScaffoldsManager.getResourceBaseUrl(resourceName, additionalParameter);
        $.ajax({
            url: resourceUrl + '/service/templates',
            method: 'GET',
            cache: false,
            type: 'html'
        }).done(function (html) {
            ScaffoldsManager.setResourceTemplates(resourceName, additionalParameter, html);
            deferred.resolve(resourceName, additionalParameter);
        }).fail(Utils.handleAjaxError);
    } else {
        deferred.resolve(resourceName, additionalParameter);
    }
    return deferred;
};

ScaffoldsManager.setResourceTemplates = function (resourceName, additionalParameter, html) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var templates = $('<div id="templates">' + html + '</div>');
    var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
    CmfCache.rawTemplates[resourceId] = {
        datagrid: false,
        itemForm: false,
        bulkEditForm: false,
        itemdetails: false
    };
    var dataGridTpl = templates.find('#data-grid-tpl');
    if (dataGridTpl.length) {
        CmfCache.rawTemplates[resourceId].datagrid = dataGridTpl.html();
    }
    var itemFormTpl = templates.find('#item-form-tpl');
    if (itemFormTpl.length) {
        CmfCache.rawTemplates[resourceId].itemForm = itemFormTpl.html();
    }
    var bulkEditFormTpl = templates.find('#bulk-edit-form-tpl');
    if (bulkEditFormTpl.length) {
        CmfCache.rawTemplates[resourceId].bulkEditForm = bulkEditFormTpl.html();
    }
    var itemDetailsTpl = templates.find('#item-details-tpl');
    if (itemDetailsTpl.length) {
        CmfCache.rawTemplates[resourceId].itemDetails = itemDetailsTpl.html();
    }
};

ScaffoldsManager.isTemplatesLoaded = function (resourceName, additionalParameter) {
    return !!CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)];
};

ScaffoldsManager.hasDataGridTemplate = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    return (
        ScaffoldsManager.isTemplatesLoaded(resourceName, additionalParameter)
        && CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].datagrid
    )
};

ScaffoldsManager.hasItemFormTemplate = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    return (
        ScaffoldsManager.isTemplatesLoaded(resourceName, additionalParameter)
        && CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].itemForm
    );
};

ScaffoldsManager.hasBulkEditFormTemplate = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    return (
        ScaffoldsManager.isTemplatesLoaded(resourceName, additionalParameter)
        && CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].bulkEditForm
    );
};

ScaffoldsManager.hasItemDetailsTemplate = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    return (
        ScaffoldsManager.isTemplatesLoaded(resourceName, additionalParameter)
        && CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].itemDetails
    );
};

ScaffoldsManager.getDataGridTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName, additionalParameter).done(function () {
        if (!ScaffoldsManager.hasDataGridTemplate(resourceName, additionalParameter)) {
            throw 'There is no data grid template for resource [' + resourceName + ']'
                + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
        }
        deferred.resolve(
            CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].datagrid
        );
    });
    return deferred;
};

ScaffoldsManager.getItemFormTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName, additionalParameter).done(function () {
        ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
        if (!ScaffoldsManager.hasItemFormTemplate(resourceName, additionalParameter)) {
            throw 'There is no item form template for resource [' + resourceName + ']'
                + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
        }
        var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
        if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.itemForm[resourceId]) {
            CmfCache.compiledTemplates.itemForm[resourceId] = Utils.makeTemplateFromText(
                CmfCache.rawTemplates[resourceId].itemForm,
                'Item form template for ' + resourceId
            );
        }
        deferred.resolve(CmfCache.compiledTemplates.itemForm[resourceId]);
    });
    return deferred;
};

ScaffoldsManager.getBulkEditFormTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName ,additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName, additionalParameter).done(function () {
        ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
        if (!ScaffoldsManager.hasBulkEditFormTemplate(resourceName, additionalParameter)) {
            throw 'There is no bulk edit form template for resource [' + resourceName + ']'
                + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
        }
        var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
        if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.bulkEditForm[resourceId]) {
            CmfCache.compiledTemplates.bulkEditForm[resourceId] = Utils.makeTemplateFromText(
                CmfCache.rawTemplates[resourceId].bulkEditForm,
                'Bulk edit form template for ' + resourceId
            );
        }
        deferred.resolve(CmfCache.compiledTemplates.bulkEditForm[resourceId]);
    });
    return deferred;
};

ScaffoldsManager.getItemDetailsTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName, additionalParameter).done(function () {
        ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
        if (!ScaffoldsManager.hasItemDetailsTemplate(resourceName, additionalParameter)) {
            throw 'There is no item details template for resource [' + resourceName + ']'
                + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
        }
        var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
        if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.itemDetails[resourceId]) {
            CmfCache.compiledTemplates.itemDetails[resourceId] = Utils.makeTemplateFromText(
                CmfCache.rawTemplates[resourceId].itemDetails,
                'Item details template for ' + resourceId
            );
        }
        deferred.resolve(CmfCache.compiledTemplates.itemDetails[resourceId]);
    });
    return deferred;
};

ScaffoldsManager.getResourceItemData = function (resourceName, itemId, forDetailsViewer) {
    var deferred = $.Deferred();
    if (!itemId) {
        if (forDetailsViewer) {
            console.error('ScaffoldsManager.getDataForItem(): itemId argument is requred when argument forDetailsViewer == true');
            return deferred.reject();
        } else if (!ScaffoldFormHelper.deafults[resourceName]) {
            itemId = 'service/defaults';
        } else {
            return deferred.resolve(ScaffoldFormHelper.deafults[resourceName]);
        }
    }
    $.ajax({
        url: ScaffoldsManager.getResourceBaseUrl(resourceName) + '/' + itemId + '?details=' + (forDetailsViewer ? '1' : '0'),
        method: 'GET',
        cache: false
    }).done(function (data) {
        data.formUUID = Base64.encode(this.url + (new Date()).getTime());
        if (itemId === 'service/defaults') {
            data.isCreation = true;
            ScaffoldFormHelper.deafults[resourceName] = data;
        }
        deferred.resolve(data);
    }).fail(function (xhr) {
        deferred.reject();
        Utils.handleAjaxError(xhr);
    });
    return deferred;
};

var ScaffoldActionsHelper = {
    makeResourceBodyClass: function (resourceName) {
        return 'resource-' + resourceName;
    },
    initActions: function (container, useLiveEvents) {
        var $container = $(container);
        var clickHandler = function (event) {
            ScaffoldActionsHelper.handleDataAction(this, $container);
            return false;
        };
        if (useLiveEvents) {
            $container.on('click tap', '[data-action]', clickHandler);
        } else {
            $container.find('[data-action]').on('click tap', clickHandler);
        }
        /*if ($container.hasClass('modal')) {
            var closeModalHandler = function () {
                $container.modal('hide');
            };
            var reloadDataGridHandler = function () {
                ScaffoldDataGridHelper.reloadCurrentDataGrid();
            };
            if (useLiveEvents) {
                $container
                    .on('click', 'a[data-close-modal="1"], button[data-close-modal="1"]', function (event) {
                        if (!$(this).attr('data-action')) {
                            closeModalHandler(event);
                        }
                    })
                    .on('click', 'a[data-reload-datagrid="1"], button[data-reload-datagrid="1"]', function (event) {
                        if (!$(this).attr('data-action')) {
                            reloadDataGridHandler(event);
                        }
                    });
            } else {
                $container
                    .find('a[data-close-modal="1"], button[data-close-modal="1"]')
                        .not('[data-action]')
                        .on('click', closeModalHandler);
                $container
                    .find('a[data-reload-datagrid="1"], button[data-reload-datagrid="1"]')
                        .not('[data-action]')
                        .on('click', reloadDataGridHandler);
            }
        }*/
    },
    beforeDataActionHandling: function (el, container) {
        var callbackRet = null;
        var callback = $(el).attr('data-before-action');
        if (callback) {
            if (callback.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
                eval('callback = ' + callback);
                if (typeof callback === 'function') {
                    callbackRet = callback($(el));
                }
            }
        }
        return callbackRet === false ? false : true;
    },
    handleDataAction: function (el, container) {
        if (ScaffoldActionsHelper.beforeDataActionHandling(el, container) === false) {
            return;
        }
        var $el = $(el);
        var $container = $(container);
        var action = String($el.attr('data-action')).toLowerCase();
        var isModal = $container.hasClass('modal');
        switch (action) {
            case 'request':
                Utils.showPreloader($container);
                ScaffoldActionsHelper.handleRequestAction($el, ScaffoldActionsHelper.onSuccess)
                    .done(function () {
                        if (isModal) {
                            if ($el.attr('data-close-modal') === '1') {
                                $container.modal('hide');
                            }
                            if ($el.attr('data-reload-datagrid') === '1') {
                                ScaffoldDataGridHelper.reloadCurrentDataGrid();
                            }
                        }
                    })
                    .always(function () {
                        Utils.hidePreloader($container);
                    });
                break;
            case 'redirect':
                if (isModal) {
                    $container.modal('hide');
                }
                page.show($el.attr('data-url'));
                break;
            case 'reload':
                page.reload();
                break;
            case 'back':
                if (isModal) {
                    $container.modal('hide');
                } else {
                    var defaultUrl = $el.attr('data-url') || CmfConfig.rootUrl;
                    page.back(defaultUrl);
                }
                break;
        }
    },
    handleRequestAction: function ($el, onSuccess) {
        var url = $el.attr('data-url') || $el.attr('href');
        if (!url || url.length < 2) {
            return $.Deferred().reject();
        }
        if ($el.attr('data-confirm')) {
            var accepted = window.confirm($el.attr('data-confirm'));
            if (!accepted) {
                return $.Deferred().reject();
            }
        }
        var data = $el.attr('data-data') || $el.data('data') || '';
        var method = String($el.attr('data-method') || 'get').toLowerCase();
        var baseMethod;
        if ($.inArray(method, ['post', 'put', 'delete']) < 0) {
            baseMethod = 'GET';
        } else {
            baseMethod = 'POST';
            if (method !== 'post') {
                if ($.isPlainObject(data)) {
                    data._method = method.toUpperCase();
                } else {
                    data += (data.length ? '&' : '') + '_method=' + method.toUpperCase();
                }
            }
        }
        return $.ajax({
                url: url,
                data: data,
                method: baseMethod,
                cache: false,
                dataType: $el.attr('data-response-type') || 'json'
            })
            .done(function (json) {
                var ret = null;
                var callback = $el.attr('data-on-success');
                if (callback) {
                    if (callback.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
                        eval('callback = ' + callback);
                        if (typeof callback === 'function') {
                            ret = callback(json, $el, function () {
                                return onSuccess(json);
                            });
                        }
                    }
                }
                if (ret !== false) {
                    onSuccess(json);
                }
            })
            .fail(Utils.handleAjaxError);
    },
    onSuccess: Utils.handleAjaxSuccess
};

var ScaffoldDataGridHelper = {
    defaultConfig: {
        filter: true,
        stateSave: true,
        dom: "<'row'<'col-sm-12'<'#query-builder'>>><'row'<'col-xs-12 col-md-5'<'filter-toolbar btn-toolbar text-left'>><'col-xs-12 col-md-7'<'toolbar btn-toolbar text-right'>>><'row'<'col-sm-12'tr>><'row'<'col-sm-3 hidden-xs hidden-sm'i><'col-xs-12 col-md-6'p><'col-sm-3 hidden-xs hidden-sm'l>>",
        stateSaveCallback: function (settings, state) {
            if (settings.iDraw > 1) {
                ScaffoldDataGridHelper.hideRowActions($(settings.nTable));
                if (state.search && state.search.search && state.search.search.length > 0) {
                    var newUrl = window.request.canonicalPath + '?' + settings.sTableId + '=' + rison.encode_object(state);
                    page.show(newUrl, null, false);
                }
            }
        },
        stateLoadCallback: function (settings) {
            if (window.request.query[settings.sTableId]) {
                try {
                    return rison.decode_object(window.request.query[settings.sTableId]);
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.log('Invalid Rison object');
                    }
                }
            } else if (window.request.query.filter) {
                try {
                    var filters = JSON.parse(window.request.query.filter);
                    if (filters) {
                        var search = DataGridSearchHelper.convertKeyValueFiltersToRules(filters);
                        if (search) {
                            this.api().search(search);
                        }
                        return {};
                    }
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.log('Invalid json for "filter" query arg');
                    }
                }

            }
            return {};
        }
    },
    $currentDataGrid: null,
    setCurrentDataGrid: function ($table) {
        ScaffoldDataGridHelper.$currentDataGrid = $table;
    },
    getCurrentDataGrid: function () {
        if (ScaffoldDataGridHelper.$currentDataGrid && !document.contains(ScaffoldDataGridHelper.$currentDataGrid[0])) {
            ScaffoldDataGridHelper.$currentDataGrid = null;
        }
        return ScaffoldDataGridHelper.$currentDataGrid;
    },
    getCurrentDataGridApi: function () {
        return ScaffoldDataGridHelper.getCurrentDataGrid() ? ScaffoldDataGridHelper.$currentDataGrid.dataTable().api() : null;
    },
    reloadCurrentDataGrid: function () {
        if (ScaffoldDataGridHelper.getCurrentDataGrid()) {
            ScaffoldDataGridHelper.getCurrentDataGridApi().ajax.reload();
        }
    },
    init: function (dataGrid, configs) {
        var $dataGrid = $(dataGrid);
        if ($dataGrid.length) {
            if (!$.isPlainObject(configs)) {
                configs = {};
            }
            var tableOuterHtml = $dataGrid[0].outerHTML;
            var mergedConfigs = $.extend(
                true,
                {language: CmfConfig.getLocalizationStringsForComponent('data_tables')},
                ScaffoldDataGridHelper.defaultConfig,
                configs
            );
            if (mergedConfigs.ajax) {
                mergedConfigs.ajax = {
                    url: mergedConfigs.ajax,
                    error: Utils.handleAjaxError
                }
            }
            var configsBackup = $.extend(true, {}, mergedConfigs);
            $dataGrid.DataTable(mergedConfigs)
                .on('init', function (event, settings) {
                    var $table = $(settings.nTable);
                    var $tableWrapper = $(settings.nTableWrapper);
                    $table.data('configs', mergedConfigs);
                    ScaffoldDataGridHelper.initToolbar($tableWrapper, configs);
                    if (configs.queryBuilderConfig) {
                        DataGridSearchHelper.init(configs.queryBuilderConfig, configs.defaultSearchRules, $table);
                    }
                    ScaffoldDataGridHelper.initClickEvents($tableWrapper, $table, configs);
                    ScaffoldDataGridHelper.initRowActions($table, configs);
                    ScaffoldDataGridHelper.initMultiselect($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkLinks($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkEditing($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initNestedView($table, $tableWrapper, configsBackup, tableOuterHtml);
                }).on('preXhr', function (event, settings) {
                    ScaffoldDataGridHelper.hideRowActions($(settings.nTable));
                });
            return $dataGrid;
        } else {
            throw 'Invalid data grid id: ' + $dataGrid
        }
    },
    initToolbar: function ($tableWrapper, configs) {
        var $toolbarEl = $tableWrapper.find('.toolbar');
        var $filterToolbar = $tableWrapper.find('.filter-toolbar');
        var $reloadBtn = $('<button class="btn btn-default" data-action="reload"></button>')
            .html(CmfConfig.getLocalizationStringsForComponent('data_tables').toolbar.reloadData);
        if ($filterToolbar.length) {
            $filterToolbar.prepend($reloadBtn);
        }
        if ($.isArray(configs.filterToolbarItems)) {
            for (var i = 0; i < configs.filterToolbarItems.length; i++) {
                $filterToolbar.append(configs.filterToolbarItems[i]);
            }
        }
        if ($toolbarEl.length) {
            if (!$filterToolbar.length) {
                $toolbarEl.prepend($reloadBtn);
            }
            if ($.isArray(configs.toolbarItems)) {
                for (i = 0; i < configs.toolbarItems.length; i++) {
                    $toolbarEl.append(configs.toolbarItems[i]);
                }
            }
        }
        if (configs.stickyToolbar) {
            // todo: test sticky data-grid toolbar
            var $toolbarContainer = $toolbarEl.closest('.toolbar-container');
            $toolbarContainer.affix({
                offset: {
                    top: function () {
                        return (
                            this.top = (
                                $('header').filter('.main-header').outerHeight(true)
                                + $('#section-content').find('> .section-content-wrapper > .content-header').outerHeight(true)
                            )
                        )
                    },
                    bottom: function () {
                        return (this.bottom = $('footer').filter('.main-footer').outerHeight(true))
                    }
                }
            })
        }
    },
    initClickEvents: function ($tableWrapper, $table, configs) {
        var api = $table.dataTable().api();
        $tableWrapper.on('click tap', '[data-action]', function (event) {
            event.preventDefault();
            var $el = $(this);
            if ($el.hasClass('disabled')) {
                return false;
            }
            if (ScaffoldActionsHelper.beforeDataActionHandling($el, $table) === false) {
                return false;
            }
            var action = String($el.attr('data-action')).toLowerCase();
            switch (action) {
                case 'reload':
                    api.ajax.reload();
                    break;
                case 'bulk-filtered':
                    $el.data('data', {conditions: api.search()});
                    $el.attr('data-block-datagrid', '1');
                    // no break here!!!
                case 'bulk-selected':
                    $el.attr('data-block-datagrid', '1');
                    // no break here!!!
                case 'request':
                    var blockDataGrid = !!$el.attr('data-block-datagrid');
                    if (blockDataGrid) {
                        Utils.showPreloader($tableWrapper);
                    }
                    ScaffoldActionsHelper.handleRequestAction($el, function (json) {
                            if (
                                json.redirect
                                && (
                                    json.redirect === 'back'
                                    || json.redirect === 'reload'
                                    // todo: restore this
                                    // || json.redirect === ScaffoldsManager.app.activeRequest.href
                                    // || json.redirect === ScaffoldsManager.app.activeRequest.path
                                )
                            ) {
                                api.ajax.reload();
                                delete json.redirect;
                            }
                            ScaffoldActionsHelper.onSuccess(json);
                        })
                        .always(function () {
                            if (blockDataGrid) {
                                Utils.hidePreloader($tableWrapper);
                            }
                        });
                    break;
            }
            return false;
        });
        if (configs && configs.doubleClickUrl) {
            $table.on('dblclick dbltap', 'tbody tr', function (event) {
                event.preventDefault();
                var targetTagName =  event.target.tagName;
                if (targetTagName === 'I' || targetTagName === 'SPAN') {
                    // usually this is icon or text iside <a> or <button>
                    targetTagName = $(event.target).parent()[0].tagName;
                }
                if (
                    !$(event.target).hasClass('select-checkbox')
                    && targetTagName !== 'A'
                    && targetTagName !== 'BUTTON'
                    && targetTagName !== 'INPUT'
                ) {
                    var data = api.row($(this)).data();
                    if (data) {
                        page.show(configs.doubleClickUrl(api.row($(this)).data()));
                    }
                }
                return false;
            });
        }
    },
    initRowActions: function ($table, configs) {
        if (!configs || !configs.rowActions) {
            return;
        }
        var $actionsContainer = $('<div class="row-actions-container hidden"></div>');
        $table.data('rowActionsContainer', $actionsContainer);
        $table.parent().append($actionsContainer);

        var $actionsBoxEl = $('<div class="row-actions-block box box-primary"></div>');
        $table.data('rowActionsBoxEl', $actionsBoxEl);
        $actionsContainer.append($actionsBoxEl);

        var blockHout = false;
        $actionsContainer.on('click tap', 'a,[data-action]', function () {
            ScaffoldDataGridHelper.hideRowActions($table);
            $table.find('tr.selected').removeClass('selected');
        }).on('mouseenter', function () {
            blockHout = true;
        }).on('mouseleave', function () {
            blockHout = false;
        });

        var allowHover = true;
        var closingTimeout = false;
        $table.on('click tap', 'tbody tr', function (event) {
            if (event.target.tagName.toLowerCase() === 'a') {
                return true;
            }
            var $row = $(this);
            if ($row.hasClass('selected')) {
                $row.removeClass('selected');
                ScaffoldDataGridHelper.hideRowActions($table);
                allowHover = true;
            } else {
                $table.find('tr.selected').removeClass('selected');
                $row.addClass('selected');
                ScaffoldDataGridHelper.showRowActions($row, $table);
                allowHover = false;
            }
            return false;
        }).on('mouseenter', 'tbody tr', function () {
            if (closingTimeout) {
                clearTimeout(closingTimeout);
            }
            if (allowHover) {
                $table.find('tr.selected').removeClass('selected');
                ScaffoldDataGridHelper.showRowActions($(this), $table);
            }
        }).on('mouseleave', 'tbody tr', function () {
            closingTimeout = setTimeout(function () {
                if (allowHover && !blockHout) {
                    $table.find('tr.selected').removeClass('selected');
                    ScaffoldDataGridHelper.hideRowActions($table);
                }
            }, 50);
        }).on('mousemove', 'tbody tr', function (event) {
            if (!$actionsContainer.hasClass('hidden')) {
                var left = $actionsBoxEl.offset().left;
                var width = $actionsBoxEl.width();
                if (event.pageX < left || event.pageX > left + width) {
                    var mouseXRelative = event.pageX - $actionsContainer.offset().left;
                    $actionsContainer.css({'text-align': 'left'});
                    $actionsBoxEl.css({left: Math.max(0, Math.round(mouseXRelative - width / 2))});
                }
            }
        });
    },
    showRowActions: function ($row, $table, mouseX) {
        var configs = $table.data('configs');
        var rowActionsContainer = $table.data('rowActionsContainer');
        if (configs && configs.rowActions && rowActionsContainer) {
            var data = $table.dataTable().api().row($row).data();
            if (data) {
                var $actionsEl = $(configs.rowActions(data));
                if ($actionsEl.find('a, button').length) {
                    var position = $row.position();
                    position.top += $row.height();
                    //position.left = 0;
                    $table.data('rowActionsBoxEl').empty().append($actionsEl);
                    rowActionsContainer.removeClass('hidden').width();
                    rowActionsContainer.css(position);
                } else {
                    rowActionsContainer.addClass('hidden');
                }
            } else {
                ScaffoldDataGridHelper.hideRowActions($table);
            }
        }
    },
    hideRowActions: function ($table) {
        var configs = $table.data('configs');
        if (configs && configs.rowActions && $table.data('rowActionsContainer')) {
            $table.data('rowActionsContainer').addClass('hidden');
        }
    },
    initMultiselect: function ($table, $tableWrapper, configs) {
        if (!configs || !configs.multiselect) {
            return;
        }
        var api = $table.dataTable().api();
        $tableWrapper.addClass('multiselect');
        $tableWrapper.on('click', 'th .rows-selection-options ul a', function () {
            var $el = $(this);
            if ($el.hasClass('select-all')) {
                api.rows().select();
            } else if ($el.hasClass('select-none')) {
                api.rows().deselect();
            } else if ($el.hasClass('invert-selection')) {
                var selected = api.rows({selected: true});
                api.rows({selected: false}).select();
                selected.deselect();
            }
        });
    },
    initBulkLinks: function ($table, $tableWrapper, configs) {
        var $selectionLinks = $tableWrapper.find(
            '[data-action="bulk-selected"], [data-action="bulk-edit-selected"], [data-type="bulk-selected"], [data-type="bulk-edit-selected"]'
        );
        var $fitleringLinks = $tableWrapper.find(
            '[data-action="bulk-filtered"], [data-action="bulk-edit-filtered"], [data-type="bulk-filtered"], [data-type="bulk-edit-filtered"]'
        );
        if (!$selectionLinks.length && !$fitleringLinks.length) {
            return;
        }
        var api = $table.dataTable().api();
        $selectionLinks.each(function () {
            $(this).data('label-tpl', $(this).html());
        });
        $fitleringLinks.each(function () {
            $(this).data('label-tpl', $(this).html());
        });
        var updateCounter = function ($links, count) {
            $links.each(function () {
                var $link = $(this);
                $link.html($link.data('label-tpl').replace(/:count/g, String(count)));
                var $parent = $link.parent('li');
                if (count === 0) {
                    $link.addClass('disabled');
                    if ($parent.length) {
                        $parent.addClass('disabled');
                    }
                } else {
                    $link.removeClass('disabled');
                    if ($parent.length) {
                        $parent.removeClass('disabled');
                    }
                }
            });
        };
        // selected items
        if (configs && configs.multiselect && $selectionLinks.length) {
            var updateSelectedCountInLabelAndCollectIds = function () {
                var selectedRows = api.rows({selected: true});
                var count = selectedRows.count() || 0;
                updateCounter($selectionLinks, count);
                var rowsData = selectedRows.data();
                $selectionLinks.each(function () {
                    var idKey = $(this).attr('data-id-field') || 'id';
                    var ids = [];
                    rowsData.each(function (rowData) {
                        ids.push(rowData[idKey]);
                    });
                    $(this).data('data', {'ids': ids});
                    $selectionLinks.trigger('selectionchange.dt', api);
                });
            };
            updateSelectedCountInLabelAndCollectIds();
            $table.on('select.dt deselect.dt', function (event, api, type) {
                if (type === 'row') {
                    updateSelectedCountInLabelAndCollectIds();
                }
            });
            $table.on('draw.dt', function () {
                updateSelectedCountInLabelAndCollectIds();
            });
            $selectionLinks.on('click', function () {
                if ($(this).parent('li').parent('ul.dropdown-menu').length) {
                    $(this).parent().parent().parent().find('[data-toggle="dropdown"]').dropdown("toggle");
                }
            });
        } else {
            $selectionLinks
                .addClass('disabled')
                .parent('li')
                    .addClass('disabled')
        }
        // fitlered items
        if ($fitleringLinks.length) {
            var updateFilteredCountInLabel = function () {
                var count = api.page.info().recordsTotal || 0;
                updateCounter($fitleringLinks, count);
            };
            updateFilteredCountInLabel();
            $table.on('draw.dt', function () {
                updateFilteredCountInLabel();
            });
            $fitleringLinks.on('click', function () {
                if ($(this).parent('li').parent('ul.dropdown-menu').length) {
                    $(this).parent().parent().parent().find('[data-toggle="dropdown"]').dropdown("toggle");
                }
            });
        }
    },
    initBulkEditing: function ($table, $tableWrapper, configs) {
        var $links = $tableWrapper.find('[data-action="bulk-edit-selected"], [data-action="bulk-edit-filtered"]');
        var api = $table.dataTable().api();
        $links.on('click', function () {
            var $link = $(this);
            if ($link.hasClass('disabled')) {
                return false;
            }
            ScaffoldFormHelper.handleBulkEditForm($link, configs.resource_name, api);
            return false;
        })
    },
    initNestedView: function ($table, $tableWrapper, configs, tableOuterHtml) {
        if (configs.nested_data_grid) {
            var api = $table.dataTable().api();
            var subTableConfigs = $.extend(true, {}, configs, {
                dom: "<tr><'children-data-grid-pagination container-fluid'<'row'<'col-md-3 hidden-xs hidden-sm'i><'col-xs-12 col-md-9'p>>>",
                stateSave: false,
                fixedHeader: {
                    header: false,
                    footer: false
                }
            });
            delete subTableConfigs.scrollY;
            delete subTableConfigs.scrollX;
            delete subTableConfigs.fixedColumns;
            $tableWrapper
                .on('click', 'a.show-children', function () {
                    // console.log(subTableConfigs);
                    var $tr = $(this).closest('tr');
                    var row = api.row($tr);
                    $(this).addClass('hidden');
                    if (!$tr.hasClass('has-children-table')) {
                        $tr.addClass('has-children-table');
                        var parentId = row.data()[subTableConfigs.nested_data_grid.value_column];
                        //console.log(parentId, tableOuterHtml);
                        var $subTable = $(tableOuterHtml);
                        $subTable
                            .attr('id', $subTable.attr('id') + '-children-for-' + parentId)
                            .addClass('table-condensed');
                        row.child($subTable).show();
                        var configs = $.extend(true, {}, subTableConfigs);
                        configs.ajax.url += '?parent=' + parentId;
                        $subTable
                            .DataTable(configs)
                            .on('init', function (event, settings) {
                                var $subTable = $(settings.nTable);
                                var $subTableWrapper = $(settings.nTableWrapper);
                                $subTableWrapper
                                    .addClass('children-data-grid-table-container')
                                    .parent()
                                        .addClass('pn pb5 children-data-grid-cell')
                                        .closest('tr')
                                            .addClass('children-data-grid-row');
                                $subTable
                                    .data('configs', configs)
                                    .addClass('children-data-grid-table');
                                ScaffoldDataGridHelper.initClickEvents($subTableWrapper, $subTable, configs);
                                ScaffoldDataGridHelper.initRowActions($subTable, configs);
                                ScaffoldDataGridHelper.initNestedView($subTable, $subTableWrapper, subTableConfigs, tableOuterHtml);
                            }).on('preXhr', function (event, settings) {
                                ScaffoldDataGridHelper.hideRowActions($(settings.nTable));
                            }).on('draw', function (event, settings) {
                                var $subTableWrapper = $(settings.nTableWrapper);
                                if ($(settings.nTable).dataTable().api().page.info().recordsTotal === 0) {
                                    $subTableWrapper.find('thead').hide();
                                    $subTableWrapper.find('.children-data-grid-pagination').hide();
                                    $subTable.addClass('empty');
                                } else {
                                    $subTableWrapper.find('thead').show();
                                    $subTableWrapper.find('.children-data-grid-pagination').show();
                                    $subTable.removeClass('empty');
                                }
                            });
                    } else {
                        row.child.show();
                    }
                    $tr
                        .addClass('children-table-opened')
                        .find('a.hide-children')
                            .removeClass('hidden');
                })
                .on('click', 'a.hide-children', function () {
                    var $tr = $(this).closest('tr');
                    var row = api.row($tr);
                    $(this).addClass('hidden');
                    if (row.child.isShown()) {
                        row.child.hide();
                    }
                    $tr
                        .removeClass('children-table-opened')
                        .find('a.show-children')
                            .removeClass('hidden');
                });
        }
    }
};

var DataGridSearchHelper = {
    id: 'query-builder',
    containerId: 'query-builder-container',
    defaultConfig: {
        plugins: ['bt-tooltip-errors', 'bt-checkbox', 'bt-selectpicker', 'bt-selectpicker-values'],
        allow_empty: true
    },
    resetButton: '#query-builder-reset',
    locale: {
        submit: 'Search',
        reset: 'Reset',
        header: 'Search rules'
    },
    emptyRules: {
        condition: 'AND',
        rules: [
        ]
    },
    init: function (config, defaultRules, $dataGrid) {
        if (config && config.filters && config.filters.length > 0) {
            var $builder = $('#' + DataGridSearchHelper.id);
            $builder.prepend('<h4>' + DataGridSearchHelper.locale.header + '</h4>');
            var $builderContent = $('<div></div>').attr('id' , DataGridSearchHelper.id + '-content');
            $builder.append($builderContent);
            var tableApi = $dataGrid.dataTable().api();
            for (var i in config.filters) {
                if (
                    (config.filters[i].input === 'radio' || config.filters[i].input === 'checkbox')
                    && !config.filters[i].color
                ) {
                    config.filters[i].color = 'primary';
                }
            }
            var builderConfig = {rules: []};
            if ($.isArray(defaultRules) && defaultRules.length) {
                builderConfig = {rules: defaultRules};
            } else if (defaultRules.rules) {
                builderConfig = $.extend({}, defaultRules);
            }
            builderConfig = $.extend(builderConfig, DataGridSearchHelper.defaultConfig, config);
            $builderContent.queryBuilder(builderConfig);
            if (tableApi.search().length) {
                try {
                    var currentSearch = JSON.parse(tableApi.search());
                    var decoded = DataGridSearchHelper.decodeRulesForDataTable(
                        currentSearch,
                        DataGridSearchHelper.getFieldNameToFilterIdMap(config.filters)
                    );
                    if ($.isPlainObject(decoded) && $.isArray(decoded.rules)) {
                        $builderContent.queryBuilder('setRules', decoded);
                    } else {
                        $builderContent.queryBuilder('setRules', builderConfig.rules);
                    }
                } catch (ignore) {
                    console.warn('invalid filter rules: ' + tableApi.search());
                }
            }
            var $runFilteringBtn = $('<button class="btn btn-success" type="button"></button>')
                .text(DataGridSearchHelper.locale.submit);
            $runFilteringBtn.on('click', function () {
                // clean empty filters
                $builderContent.find('.rule-container').each(function () {
                    var model = $builderContent.queryBuilder('getModel', $(this));
                    if (model && !model.filter) {
                        model.drop();
                    }
                });
                // clean empty filter groups
                $builderContent.find('.rules-group-container').each(function () {
                    var group = $builderContent.queryBuilder('getModel', $(this));
                    if (group && group.length() <= 0 && !group.isRoot()) {
                        var parentGroup = group.parent;
                        group.drop();
                        while (parentGroup && parentGroup.length() <= 0 && !parentGroup.isRoot()) {
                            var parent = parentGroup.parent;
                            parentGroup.drop();
                            parentGroup = parent;
                        }
                    }
                });
                if ($builderContent.queryBuilder('validate')) {
                    var rules = $builderContent.queryBuilder('getRules');
                    var encoded;
                    if (!rules.rules) {
                        // empty rules set
                        $builderContent.queryBuilder('reset');
                        encoded = DataGridSearchHelper.encodeRulesForDataTable(DataGridSearchHelper.emptyRules)
                    } else if ($builderContent.queryBuilder('validate')) {
                        encoded = DataGridSearchHelper.encodeRulesForDataTable(rules);
                    }
                    tableApi.search(encoded).draw();
                }
            });
            var $resetFilteringBtnInToolbar = $('<button class="btn btn-danger" type="button"></button>')
                .text(DataGridSearchHelper.locale.reset);
            $resetFilteringBtnInToolbar
                .hide()
                .on('click', function () {
                    $builderContent.queryBuilder('reset');
                    $builderContent.queryBuilder('setRules', builderConfig.rules);
                });
            var $resetFilteringBtnInFilter = $resetFilteringBtnInToolbar.clone(true).show();
            var $toolbar = $builder
                .closest('.dataTables_wrapper')
                .find('.filter-toolbar');
            if (config.is_opened) {
                $resetFilteringBtnInToolbar.show();
                $toolbar
                    .append($runFilteringBtn)
                    .append($resetFilteringBtnInToolbar);
            } else {
                var $counterBadge = $('<span class="counter label label-success ml10"></span>');
                var $filterToggleButton = $('<button class="btn btn-default" type="button"></button>')
                    .text(DataGridSearchHelper.locale.toggle)
                    .append($counterBadge)
                    .attr('data-toggle','collapse')
                    .attr('data-target', '#' + DataGridSearchHelper.id);
                $builder.addClass('collapse');
                var changeCountInBadge = function () {
                    var rules = $builderContent.queryBuilder('getRules');
                    var count = DataGridSearchHelper.countRules(rules);
                    if (count) {
                        $counterBadge.text(count).show();
                        $resetFilteringBtnInToolbar.show();
                    } else {
                        $counterBadge.hide();
                    }
                };
                changeCountInBadge();
                $runFilteringBtn
                    .on('click', function () {
                        changeCountInBadge();
                        $filterToggleButton.click();
                    })
                    .hide();
                $filterToggleButton
                    .on('click', function () {
                        $filterToggleButton.toggleClass('active');
                        if ($filterToggleButton.hasClass('active')) {
                            $runFilteringBtn.show();
                            if (DataGridSearchHelper.countRules($builderContent.queryBuilder('getRules')) > 0) {
                                $resetFilteringBtnInToolbar.show();
                            }
                        } else {
                            $runFilteringBtn.hide();
                            if (DataGridSearchHelper.countRules($builderContent.queryBuilder('getRules')) === 0) {
                                $resetFilteringBtnInToolbar.hide();
                            }
                        }
                    });
                $toolbar.append($filterToggleButton);
                var $filterCloseButton = $('<button type="button" class="btn btn-default btn-sm">')
                    .text(DataGridSearchHelper.locale.close)
                    .on('click', function () {
                        $filterToggleButton.click();
                    });
                $builder.append(
                    $('<div id="query-builder-controls">')
                        .append($filterCloseButton.addClass('pull-left'))
                        .append($runFilteringBtn.addClass('btn-sm'))
                        .append($resetFilteringBtnInFilter.addClass('btn-sm'))
                );
                $toolbar.append($resetFilteringBtnInToolbar);
            }
        }
    },
    encodeRulesForDataTable: function (rules, asObject) {
        if (!$.isPlainObject(rules)) {
            return {
                c: 'AND',
                r: []
            };
        }
        var ret = {
            c: rules.condition || 'AND',
            r: []
        };
        if (rules.rules) {
            for (var i = 0; i < rules.rules.length; i++) {
                var rule = rules.rules[i];
                if (rule.condition) {
                    ret.r.push(DataGridSearchHelper.encodeRulesForDataTable(rule, true));
                } else {
                    ret.r.push({
                        f: rule.field,
                        o: rule.operator,
                        v: rule.value
                    });
                }
            }
        }
        return !asObject ? JSON.stringify(ret) : ret;
    },
    getFieldNameToFilterIdMap: function (filters) {
        var map = {};
        for (var i = 0; i < filters.length; i++) {
            map[filters[i].field] = filters[i].id;
        }
        return map;
    },
    decodeRulesForDataTable: function (rules, fieldNameTofilterIdMap) {
        if (!rules || !$.isArray(rules.r)) {
            return null;
        }
        var ret = {
            condition: rules.c,
            rules: []
        };
        for (var i = 0; i < rules.r.length; i++) {
            var rule = rules.r[i];
            if (rule.c) {
                var subrules = DataGridSearchHelper.decodeRulesForDataTable(rule, fieldNameTofilterIdMap);
                if (subrules !== null) {
                    ret.rules.push(subrules);
                }
            } else {
                if (!fieldNameTofilterIdMap[rule.f]) {
                    continue;
                }
                ret.rules.push({
                    id: fieldNameTofilterIdMap[rule.f],
                    operator: rule.o,
                    value: rule.v
                });
            }
        }
        return ret;
    },
    countRules: function (rules) {
        if (!rules) {
            return 0;
        }
        rules = DataGridSearchHelper.encodeRulesForDataTable(rules, true);
        var count = 0;
        for (var i = 0; i < rules.r.length; i++) {
            var rule = rules.r[i];
            if (rule.c) {
                count += DataGridSearchHelper.countRules(rule);
            } else {
                count++;
            }
        }
        return count;
    },
    convertKeyValueFiltersToRules: function (filters) {
        var rules = [];
        if (!filters) {
            return false;
        }
        for (var field in filters) {
            if (typeof field === 'string') {
                rules.push({
                    field: field,
                    operator: 'equal',
                    value: filters[field]
                });
            }
        }
        if (rules.length) {
            return DataGridSearchHelper.encodeRulesForDataTable({
                condition: 'AND',
                rules: rules
            });
        } else {
            return false;
        }
    }
};

var ScaffoldFormHelper = {
    models: {},
    options: {},
    optionsTs: {},
    deafults: {},
    getModel: function (resourceName) {
        if (!ScaffoldFormHelper.models[resourceName]) {
            var model = $.observable({});
            model.fetch = function () {
                var deferred = $.Deferred();
                var itemId = model.get('id');
                if (!itemId) {
                    if (!ScaffoldFormHelper.deafults[resourceName]) {
                        itemId = 'service/defaults';
                    } else {
                        model(ScaffoldFormHelper.deafults[resourceName]);
                        model.trigger('fetch', [model]);
                        return deferred.resolve(model);
                    }
                }
                var isDetails = model.get('_is_details');
                $.ajax({
                    url: ScaffoldsManager.getResourceBaseUrl(resourceName) + '/' + itemId + '?details=' + (isDetails ? '1' : '0'),
                    method: 'GET',
                    cache: false
                }).done(function (data) {
                    if (itemId === 'service/defaults') {
                        data.isCreation = true;
                        ScaffoldFormHelper.deafults[resourceName] = data;
                    }
                    data.formUUID = Base64.encode(this.url + (new Date()).getTime());
                    model(data);
                    model.trigger('fetch', [model]);
                    deferred.resolve(model);
                }).fail(Utils.handleAjaxError);
                return deferred;
            };
            ScaffoldFormHelper.models[resourceName] = model;
        }
        ScaffoldFormHelper.models[resourceName]({});
        return ScaffoldFormHelper.models[resourceName];
    },
    loadOptions: function (resourceName, itemId) {
        var deferred = $.Deferred();
        var query = itemId ? '?id=' + itemId : '';
        var cacheKey = resourceName + (itemId ? '' : String(itemId));
        if (
            !ScaffoldFormHelper.options[cacheKey]
            || ScaffoldFormHelper.optionsTs[cacheKey] + 30000 < Date.now()
        ) {
            $.ajax({
                url: ScaffoldsManager.getResourceBaseUrl(resourceName) + '/service/options' + query,
                method: 'GET',
                cache: false
            }).done(function (data) {
                ScaffoldFormHelper.optionsTs[cacheKey] = Date.now();
                ScaffoldFormHelper.options[cacheKey] = data;
                deferred.resolve(ScaffoldFormHelper.options[cacheKey]);
            }).fail(Utils.handleAjaxError);
        } else {
            deferred.resolve(ScaffoldFormHelper.options[cacheKey]);
        }
        return deferred;
    },
    initForm: function ($form, successCallback) {
        $form.find('select[data-value!=""]').each(function () {
            if (this.multiple) {
                try {
                    var json = JSON.parse(this.getAttribute('data-value'));
                    $(this).val(json);
                } catch (exc) {
                    $(this).val(this.getAttribute('data-value'));
                }
            } else {
                $(this).val(this.getAttribute('data-value'));
            }
        });
        FormHelper.initForm($form, $form, successCallback);
    },
    handleBulkEditForm: function ($link, resourceName, api) {
        try {
            var deferred = ScaffoldsManager.getBulkEditFormTpl(resourceName);
        } catch (exc) {
            toastr.error(exc);
        }
        $('.modal.in').modal('hide'); //< hide any opened modals
        Utils.showPreloader(CmfControllerHelpers.$currentContentContainer);
        var timeout = setTimeout(function () {
            Utils.hidePreloader(CmfControllerHelpers.$currentContentContainer);
            toastr.info('Server response timed out');
        }, 20000);
        $.when(deferred, ScaffoldFormHelper.loadOptions(resourceName, 'bulk-edit'))
            .done(function (modalTpl, optionsResponse) {
                var tplData = {_options: optionsResponse};
                // collect ids or conditions
                if ($link.attr('data-action') === 'bulk-edit-selected') {
                    tplData._ids = [];
                    var idKey = $link.attr('data-id-field') || 'id';
                    api.rows({selected: true}).data().each(function (rowData) {
                        tplData._ids.push(rowData[idKey]);
                    });
                    if (!tplData._ids) {
                        toastr.error('No rows selected'); //< this should not happen, message is for developer to fix situation
                        return false;
                    }
                } else {
                    tplData._conditions = api.search();
                }
                var $bulkEditModal = $(modalTpl(tplData));
                var $bulkEditForm = $bulkEditModal.find('form');
                // add special classes for containers of checkboxes radios inputs
                $bulkEditForm
                    .find('input, select, textarea')
                    .not('[type="hidden"]')
                    .not('.bulk-edit-form-input-enabler-switch')
                        .prop('disabled', true)
                        .filter('[type="checkbox"]')
                            .not('.switch')
                                .closest('.bulk-edit-form-input-container')
                                    .addClass('checkbox')
                                .end()
                            .end()
                            .filter('.switch')
                                .closest('.bulk-edit-form-input-container')
                                    .addClass('checkbox-switch')
                                .end()
                            .end()
                        .end()
                        .filter('[type="radio"]')
                            .not('.switch')
                                .closest('.bulk-edit-form-input-container')
                                    .addClass('radio')
                                .end()
                            .end()
                            .filter('.switch')
                                .closest('.bulk-edit-form-input-container')
                                    .addClass('radio-switch')
                                .end()
                            .end()
                        ;
                // switch editing on/off by clicking on input's label
                $bulkEditForm
                    .find('.bulk-edit-form-input label')
                    .on('click', function () {
                        var $inputOrLabel = $(this);
                        $inputOrLabel
                            .closest('.bulk-edit-form-input-container')
                                .find('.bulk-edit-form-input-enabler-switch')
                                    .prop('checked', true)
                                    .change();
                    });
                // switch editing on/off by changing enabler input
                $bulkEditForm
                    .find('.bulk-edit-form-input-enabler-switch')
                    .on('change switchChange.bootstrapSwitch', function () {
                        var $inputs = $(this)
                            .closest('.bulk-edit-form-input-container')
                            .find('.bulk-edit-form-input')
                            .find('input, textarea, select');
                        $inputs.prop('disabled', !this.checked);
                        setTimeout(function () {
                            $inputs.not('[type="hidden"], .switch').focus();
                        }, 50);
                        $inputs.filter('.switch').bootstrapSwitch('disabled', !this.checked);
                        $inputs.filter('select.selectpicker').selectpicker('refresh');
                        $inputs.trigger('toggle.bulkEditEnabler', !this.checked);
                        $bulkEditForm.find('[type="submit"]').prop(
                            'disabled',
                            $bulkEditForm.find('.bulk-edit-form-input-enabler-switch:checked').length === 0
                        );
                    })
                    .change();

                // add resulting html to page and open modal
                $(document.body).append($bulkEditModal);
                $bulkEditModal
                    .on('hidden.bs.modal', function () {
                        $bulkEditModal.remove();
                    })
                    .on('show.bs.modal', function () {
                        $bulkEditForm.on('submit', function () {
                            return $bulkEditForm.find('.bulk-edit-form-input-enabler-switch:checked').length > 0;
                        });
                        ScaffoldFormHelper.initForm($bulkEditForm, function (json, $form, $container) {
                            if (json._message) {
                                toastr.success(json._message);
                            }
                            $bulkEditModal.modal('hide');
                            api.draw();
                        });
                    })
                    .modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
            })
            .always(function () {
                clearTimeout(timeout);
                Utils.hidePreloader(CmfControllerHelpers.$currentContentContainer);
            });
    },
    initWysiwyg: function (textareaSelector, config) {
        if (!$.isPlainObject(config)) {
            config = {}
        }
        if (config.data_inserts && $.isArray(config.data_inserts)) {
            config = ScaffoldFormHelper.addDataInsertsPluginToWysiwyg(config);
        }
        $(textareaSelector).ckeditor(config || {});
    },
    addDataInsertsPluginToWysiwyg: function (curentWysiwygConfig) {
        var allowedContent = 'span(wysiwyg-data-insert)[title];div(wysiwyg-data-insert)[title]';
        var pluginName = 'cmf_scaffold_data_inserter';
        if (curentWysiwygConfig.extraAllowedContent) {
            curentWysiwygConfig.extraAllowedContent += ';' + allowedContent;
        } else {
            curentWysiwygConfig.extraAllowedContent = allowedContent;
        }
        if (curentWysiwygConfig.extraPlugins) {
            curentWysiwygConfig.extraPlugins += ',' + pluginName;
        } else {
            curentWysiwygConfig.extraPlugins = pluginName;
        }
        curentWysiwygConfig.dialog_noConfirmCancel = true;
        if (!CKEDITOR.plugins.get(pluginName)) {
            var comboboxPanelCss = 'body{font-family:Arial,sans-serif;font-size:14px;}';
            var locale = CmfConfig.getLocalizationStringsForComponent('ckeditor');
            var insertTpl = doT.template(
                '<{{= it.__tag }} title="{{! it.title }}" class="wysiwyg-data-insert">{{= it.code }}</{{= it.__tag }}>'
            );
            CKEDITOR.plugins.add(pluginName, {
                requires: 'widget',
                allowedContent: allowedContent,
                init: function (editor) {
                    editor.ui.addRichCombo('cmfScaffoldDataInserter', {
                        label: locale.cmf_scaffold_inserts_plugin_title,
                        title: locale.cmf_scaffold_inserts_plugin_title,
                        toolbar: 'insert',
                        className: 'cke_combo_full_width',
                        multiSelect: false,
                        panel: {
                            css: [CKEDITOR.skin.getPath("editor")].concat(comboboxPanelCss + (editor.contentsCss || '')),
                            multiSelect: false
                        },
                        init: function () {
                            var combobox = this;
                            for (var i = 0; i < editor.config.data_inserts.length; i++) {
                                var optionValue;
                                var insertInfo = editor.config.data_inserts[i];
                                if (insertInfo.args_options && $.isPlainObject(insertInfo.args_options)) {
                                    insertInfo.args_options.__tag = {
                                        type: 'select',
                                        label: locale.cmf_scaffold_inserts_dialog_insert_tag_name,
                                        options: {
                                            span: locale.cmf_scaffold_inserts_dialog_insert_tag_is_span,
                                            div: locale.cmf_scaffold_inserts_dialog_insert_tag_is_div
                                        },
                                        value: 'span'
                                    };
                                    var dialogName = editor.id + '_dialog_for_data_insert_' + i;
                                    var dialogAdded = (function (insertInfo, dialogName) {
                                        return ScaffoldFormHelper.makeWysiwygDialogForDataInserts(
                                            insertInfo.args_options,
                                            dialogName,
                                            insertInfo.title,
                                            function (data, optionsOfAllSelects) {
                                                // this === dialog
                                                var tplData = $.extend({}, insertInfo);
                                                tplData.__tag = data.__tag || 'span';
                                                delete data.__tag;
                                                for (var argName in data) {
                                                    tplData.code = tplData.code.replace(':' + argName, data[argName]);
                                                }
                                                if (insertInfo.widget_title_tpl) {
                                                    tplData.title = tplData.widget_title_tpl;
                                                    for (argName in data) {
                                                        if (insertInfo.args_options[argName] && insertInfo.args_options[argName].type === 'select') {
                                                            // select
                                                            tplData.title = tplData.title.replace(
                                                                ':' + argName + '.value',
                                                                data[argName]
                                                            );
                                                            if (optionsOfAllSelects[argName]) {
                                                                tplData.title = tplData.title.replace(
                                                                    ':' + argName + '.label',
                                                                    optionsOfAllSelects[argName][data[argName]] || ''
                                                                );
                                                            }
                                                        } else {
                                                            // text or checkbox
                                                            tplData.title = tplData.title.replace(':' + argName, data[argName]);
                                                        }
                                                    }
                                                }
                                                /*if (data.__title) {
                                                    insertInfo.title = data
                                                }*/
                                                editor.focus();
                                                editor.fire('saveSnapshot');
                                                editor.insertHtml(insertTpl(tplData));
                                                editor.fire('saveSnapshot');
                                            }
                                        )
                                    })(insertInfo, dialogName);
                                    if (!dialogAdded) {
                                        continue; //< skip invalid option
                                    }
                                    optionValue = 'dialog:' + dialogName;
                                } else {
                                    insertInfo.__tag = insertInfo.is_block ? 'div' : 'span';
                                    optionValue = insertTpl(insertInfo);
                                }
                                combobox.add(optionValue, insertInfo.title, insertInfo.title);
                                // combobox._.committed = 0;
                                // combobox.commit(); //< ty good people of web!!!
                            }
                        },
                        onClick: function (value) {
                            var matches = value.match(/^dialog:(.*)$/);
                            if (matches !== null) {
                                editor.openDialog(matches[1]);
                            } else {
                                editor.focus();
                                editor.fire('saveSnapshot');
                                editor.insertHtml(value);
                                editor.fire('saveSnapshot');
                            }
                        }
                    });
                    editor.widgets.add('CmfScaffoldInsertedData', {
                        allowedContent: allowedContent,
                        requiredContent: allowedContent,
                        upcast: function (element) {
                            return (element.name === 'span' || element.name === 'div') && element.hasClass('wysiwyg-data-insert');
                        }
                    });
                },
                onLoad: function () {
                    CKEDITOR.addCss(
                        '.wysiwyg-data-insert{font-size:0}'
                        + 'span.wysiwyg-data-insert{display:inline-block;}'
                        + '.wysiwyg-data-insert:before{content:attr(title);position:static;display:block;font-size:12px;padding:0 5px;background:#DDD;border-radius:2px;-moz-border-radius:2px;-webkit-border-radius:2px;border:1px solid #555;white-space:nowrap;cursor:pointer;text-align:center;}'
                        + 'span.wysiwyg-data-insert:before{display: inline-block}'
                    );
                }
            });
        }
        return curentWysiwygConfig;
    },
    makeWysiwygDialogForDataInserts: function (inputsConfigs, dialogName, dialogHeader, onSubmit) {
        var argsAreValid = true;
        var argsCount = 0;
        var dialogElements = [];
        var optionsOfAllSelects = {};
        for (var inputName in inputsConfigs) {
            var inputConfig = inputsConfigs[inputName];
            if (!inputConfig.label) {
                console.error(dialogHeader + ': label is required for each selector config for input "' + inputName + '"');
                argsAreValid = false;
                break;
            }
            if (!inputConfig.type) {
                inputConfig.type = inputConfig.options ? 'select' : 'text';
            }
            var tmpConfig;
            switch (inputConfig.type) {
                case 'select':
                    if (!inputConfig.options) {
                        console.error(dialogHeader + ': options are required for input "' + inputName + '"');
                        argsAreValid = false;
                    }
                    tmpConfig = {
                        type: 'select',
                        label: inputConfig.label,
                        id: inputName,
                        items: [],
                        'default': typeof inputConfig.value !== 'undefined' ? inputConfig.value : null
                    };
                    if ($.isPlainObject(inputConfig.options)) {
                        optionsOfAllSelects[inputName] = inputConfig.options;
                        for (var optionValue in inputConfig.options) {
                            tmpConfig.items.push([inputConfig.options[optionValue], optionValue]);
                        }
                    } else {
                        // url
                        (function (inputConfig, inputName) {
                            // needed to avoid mutable variable problems die to iteration
                            tmpConfig.onLoad = function () {
                                var select = this;
                                var form = select._.dialog._.editor.element.$.form;
                                var idInputName = $(form).attr('data-id-field');
                                var data = {};
                                if (idInputName && form[idInputName] && form[idInputName].value) {
                                    data['pk'] = form[idInputName].value;
                                }
                                $.ajax({
                                        url: inputConfig.options,
                                        method: 'GET',
                                        data: data,
                                        dataType: 'json',
                                        cache: false
                                    })
                                    .done(function (json) {
                                        var $select = $(select._.select.getElement().$);
                                        for (var value in json) {
                                            if ($.isPlainObject(json[value])) {
                                                // optgroup
                                                var $group = $('<optgroup>').attr('label', value);
                                                $select.append($group);
                                                for (var valueInGroup in json[value]) {
                                                    if (!select.default) {
                                                        select.default = valueInGroup;
                                                    }
                                                    $group.append(
                                                        $('<option>')
                                                            .attr('value', valueInGroup)
                                                            .html(json[value][valueInGroup])
                                                    );
                                                }
                                            } else {
                                                if (!select.default) {
                                                    select.default = value;
                                                }
                                                select.add(json[value], value);
                                            }
                                        }
                                        optionsOfAllSelects[inputName] = json;
                                    })
                                    .fail(Utils.handleAjaxError)
                            };
                            tmpConfig.onShow = function () {
                                var $select = $(this.getInputElement().$);
                                $select.val(
                                    typeof inputConfig.value === 'undefined'
                                        ? $select.find('option').first().attr('value')
                                        : inputConfig.value
                                )
                            }
                        })(inputConfig, inputName);
                    }
                    dialogElements.push(tmpConfig);
                    break;
                case 'checkbox':
                    dialogElements.push({
                        type: 'checkbox',
                        label: inputConfig.label,
                        id: inputName,
                        'default': !!inputConfig.checked
                    });
                    break;
                default:
                    dialogElements.push({
                        type: 'text',
                        label: inputConfig.label,
                        id: inputName,
                        'default': typeof inputConfig.value !== 'undefined' ? inputConfig.value : ''
                    });
            }
            argsCount++;
        }
        if (argsAreValid) {
            if (!CKEDITOR.dialog.exists(dialogName)) {
                CKEDITOR.dialog.add(dialogName, function () {
                    return {
                        title: dialogHeader,
                        minWidth: 400,
                        minHeight: 40 * argsCount,
                        contents: [
                            {
                                id: 'tab1',
                                label: '-',
                                title: '-',
                                expand: true,
                                padding: 0,
                                elements: dialogElements
                            }
                        ],
                        buttons: [CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton],
                        onOk: function () {
                            var data = {};
                            for (var inputName in inputsConfigs) {
                                var element = this.getContentElement('tab1', inputName);
                                if (element) {
                                    data[inputName] = this.getContentElement('tab1', inputName).getValue();
                                } else {
                                    data[inputName] = '';
                                }
                            }
                            if (typeof onSubmit === 'function') {
                                onSubmit.call(this, data, optionsOfAllSelects);
                            }
                        }
                    }
                });
            }
            return true;
        }
        return false;
    }
};