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
    return typeof resourceName === 'string' && String(resourceName).match(/^[a-zA-Z_][a-zA-Z_0-9]+$/);
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
    },
    selectOptions: {},
    selectOptionsTs: {},
    itemDefaults: {}
});

ScaffoldsManager.importTemplatesFromCmfSettings = function (templates) {
    if (templates.hasOwnProperty('pages') && $.isPlainObject(templates.pages)) {
        $.extend(CmfCache.views, templates.pages);
    }

    if (templates.hasOwnProperty('resources') && $.isPlainObject(templates.resources)) {
        for (var key in templates.resources) {
            if ($.isPlainObject(templates.resources[key])) {
                CmfCache.rawTemplates[key] = templates.resources[key];
                if (
                    CmfCache.rawTemplates[key].hasOwnProperty('itemFormDefaults')
                    && $.isPlainObject(CmfCache.rawTemplates[key].itemFormDefaults)
                ) {
                    ScaffoldsManager.cacheDefaultItemData(key, CmfCache.rawTemplates[key].itemFormDefaults);
                    delete CmfCache.rawTemplates[key].itemFormDefaults;
                }
            }
        }
    }
};

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
        }).fail(function (xhr) {
            Utils.handleAjaxError.call(this, xhr, deferred);
        });
    } else {
        deferred.resolve(resourceName, additionalParameter);
    }
    return deferred.promise();
};

ScaffoldsManager.setResourceTemplates = function (resourceName, additionalParameter, html) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var templates = $('<div id="templates">' + html + '</div>');
    var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
    CmfCache.rawTemplates[resourceId] = {
        datagrid: false,
        itemForm: false,
        bulkEditForm: false,
        itemDetails: false
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
    ScaffoldsManager
        .loadTemplates(resourceName, additionalParameter)
        .done(function () {
            if (!ScaffoldsManager.hasDataGridTemplate(resourceName, additionalParameter)) {
                throw 'There is no data grid template for resource [' + resourceName + ']'
                    + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
            }
            deferred.resolve(
                CmfCache.rawTemplates[ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter)].datagrid
            );
        })
        .fail(function (error) {
            deferred.reject(error);
        });
    return deferred.promise();
};

ScaffoldsManager.getItemFormTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager
        .loadTemplates(resourceName, additionalParameter)
        .done(function () {
            ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
            if (!ScaffoldsManager.hasItemFormTemplate(resourceName, additionalParameter)) {
                throw 'There is no item form template for resource [' + resourceName + ']'
                    + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
            }
            var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
            if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.itemForm[resourceId]) {
                Utils.makeTemplateFromText(
                        CmfCache.rawTemplates[resourceId].itemForm,
                        'Item form template for ' + resourceId
                    )
                    .done(function (template) {
                        CmfCache.compiledTemplates.itemForm[resourceId] = template;
                        deferred.resolve(template);
                    })
                    .fail(function (error) {
                        deferred.reject(error);
                    });
            } else {
                deferred.resolve(CmfCache.compiledTemplates.itemForm[resourceId]);
            }
        })
        .fail(function (error) {
            deferred.reject(error);
        });
    return deferred.promise();
};

ScaffoldsManager.getBulkEditFormTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName ,additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager
        .loadTemplates(resourceName, additionalParameter)
        .done(function () {
            ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
            if (!ScaffoldsManager.hasBulkEditFormTemplate(resourceName, additionalParameter)) {
                throw 'There is no bulk edit form template for resource [' + resourceName + ']'
                    + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
            }
            var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
            if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.bulkEditForm[resourceId]) {
                Utils.makeTemplateFromText(
                        CmfCache.rawTemplates[resourceId].bulkEditForm,
                        'Bulk edit form template for ' + resourceId
                    )
                    .done(function (template) {
                        CmfCache.compiledTemplates.bulkEditForm[resourceId] = template;
                        deferred.resolve(template);
                    })
                    .fail(function (error) {
                        deferred.reject(error);
                    });
            } else {
                deferred.resolve(CmfCache.compiledTemplates.bulkEditForm[resourceId]);
            }
        })
        .fail(function (error) {
            deferred.reject(error);
        });
    return deferred.promise();
};

ScaffoldsManager.getItemDetailsTpl = function (resourceName, additionalParameter) {
    ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
    var deferred = $.Deferred();
    ScaffoldsManager
        .loadTemplates(resourceName, additionalParameter).done(function () {
            ScaffoldsManager.validateResourceName(resourceName, additionalParameter);
            if (!ScaffoldsManager.hasItemDetailsTemplate(resourceName, additionalParameter)) {
                throw 'There is no item details template for resource [' + resourceName + ']'
                    + (typeof additionalParameter === 'undefined' ? '' : ' with additional parameter [' + additionalParameter + ']');
            }
            var resourceId = ScaffoldsManager.buildResourceUrlSuffix(resourceName, additionalParameter);
            if (!ScaffoldsManager.cacheTemplates || !CmfCache.compiledTemplates.itemDetails[resourceId]) {
                Utils.makeTemplateFromText(
                        CmfCache.rawTemplates[resourceId].itemDetails,
                        'Item details template for ' + resourceId
                    )
                    .done(function (template) {
                        CmfCache.compiledTemplates.itemDetails[resourceId] = template;
                        deferred.resolve(template);
                    })
                    .fail(function (error) {
                        deferred.reject(error);
                    });
            } else {
                deferred.resolve(CmfCache.compiledTemplates.itemDetails[resourceId]);
            }
        })
        .fail(function (error) {
            deferred.reject(error);
        });
    return deferred.promise();
};

ScaffoldsManager.getResourceItemData = function (resourceName, itemId, forDetailsViewer) {
    var deferred = $.Deferred();
    if (!itemId) {
        if (forDetailsViewer) {
            var error = 'ScaffoldsManager.getDataForItem(): itemId argument is requred when argument forDetailsViewer == true';
            console.error(error);
            deferred.reject(new Error(error));
            return deferred.promise();
        } else if (!CmfCache.itemDefaults[resourceName]) {
            itemId = 'service/defaults';
        } else {
            deferred.resolve(CmfCache.itemDefaults[resourceName]);
            return deferred.promise();
        }
    }
    $.ajax({
        url: ScaffoldsManager.getResourceBaseUrl(resourceName) + '/' + itemId + '?details=' + (forDetailsViewer ? '1' : '0'),
        method: 'GET',
        cache: false
    }).done(function (data) {
        data.formUUID = Base64.encode(this.url + (new Date()).getTime());
        if (itemId === 'service/defaults') {
            ScaffoldsManager.cacheDefaultItemData(resourceName, data);
        }
        deferred.resolve(data);
    }).fail(function (xhr) {
        Utils.handleAjaxError.call(this, xhr, deferred);
    });
    return deferred.promise();
};

ScaffoldsManager.cacheDefaultItemData = function (resourceName, data) {
    data.isCreation = true;
    CmfCache.itemDefaults[resourceName] = data;
};

var ScaffoldActionsHelper = {
    makeResourceBodyClass: function (resourceName) {
        return Utils.normalizeBodyClass('resource-' + resourceName);
    },
    initActions: function (container, useLiveEvents) {
        var $container = $(container);
        var clickHandler = function () {
            ScaffoldActionsHelper.handleDataAction(this, $container);
            return false;
        };
        if (useLiveEvents) {
            $container.on('click tap', '[data-action]', clickHandler);
        } else {
            $container.find('[data-action]').on('click tap', clickHandler);
        }
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
        return callbackRet !== false;
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
                ScaffoldActionsHelper
                    .handleRequestAction($el, ScaffoldActionsHelper.onSuccess, function () {
                        ScaffoldActionsHelper.handleDataAction(el, container);
                    })
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
    handleRequestAction: function ($el, onSuccess, onRepeat) {
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
        if (typeof(onSuccess) !== 'function') {
            onSuccess = function () {};
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
                if (onRepeat) {
                    if (typeof onRepeat === 'function') {
                        json.repeat_action = onRepeat;
                    }
                } else {
                    json.repeat_action = function () {
                        ScaffoldActionsHelper.handleRequestAction($el, onSuccess);
                    };
                }
                if (callback && callback.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
                    eval('callback = ' + callback);
                    if (typeof callback === 'function') {
                        ret = callback(json, $el, function () {
                            return onSuccess(json);
                        });
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
            var cleanedState = $.extend(true, {}, state);
            delete cleanedState.time;
            var api = this.api();
            // compress sorting - using column index is not really readable idea and actually makes it less reliable on server side
            if (typeof cleanedState.order !== 'undefined' && $.isArray(cleanedState.order)) {
                var sorting = cleanedState.order;
                delete cleanedState.order;
                cleanedState.sort = {};
                for (var k = 0; k < sorting.length; k++) {
                    cleanedState.sort[api.column(sorting[k][0]).dataSrc()] = sorting[k][1];
                }
            }
            // compress cleanedState.search - we only use cleanedState.search.search, other keys are not used
            if (
                typeof cleanedState.search !== 'undefined'
                && $.isPlainObject(cleanedState.search)
                && typeof cleanedState.search.search !== 'undefined'
            ) {
                if (cleanedState.search.search.length >= 2) {
                    try {
                        cleanedState.filter = JSON.parse(cleanedState.search.search);
                    } catch (e) {
                        console.warn('Failed to parse encoded filtering rules', cleanedState.search.search, e);
                    }
                }
                delete cleanedState.search;
            }
            // compress columns (we only need visibility value and only for possible future usage to add columns show/hide plugin)
            if (typeof cleanedState.columns !== 'undefined' && $.isArray(cleanedState.columns)) {
                var columns = cleanedState.columns;
                delete cleanedState.columns;
                cleanedState.cv = [];
                for (var i = 0; i < columns.length; i++) {
                    cleanedState.cv.push(columns[i].visible ? 1 : 0);
                }
            }
            var encodedState = JSON.stringify(cleanedState);
            if (settings.iDraw > 1) {
                if (encodedState !== settings.initialState) {
                    if (page.currentRequest().env().is_history) {
                        page.currentRequest().customData.is_datagrid = true;
                        page.currentRequest().customData.api = api;
                    } else {
                        var newUrl = document.location.pathname + '?' + settings.sTableId + '=' + encodedState + document.location.hash;
                        page.show(newUrl, null, true, true, {
                            is_state_save: true,
                            is_datagrid: true,
                            api: api
                        });
                        settings.initialState = encodedState;
                    }
                }
            } else {
                settings.initialState = encodedState;
                page.currentRequest().customData.is_datagrid = true;
                page.currentRequest().customData.api = api;
            }
        },
        stateLoadCallback: function (settings) {
            var api = this.api();
            var request = new page.Request(document.location.pathname + document.location.search + document.location.hash);
            if (request.query[settings.sTableId]) {
                try {
                    var state = JSON.parse(request.query[settings.sTableId]);
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.warn('Invalid JSON', request.query[settings.sTableId], e);
                    }
                }
                try {
                    state.time = (new Date()).getTime();
                    // restore compressed state.columns
                    if (typeof state.cv !== 'undefined' && $.isArray(state.cv)) {
                        var columnsEncoded = state.cv;
                        state.columns = [];
                        for (var i = 0; i < columnsEncoded.length; i++) {
                            state.columns.push({
                                visible: !!columnsEncoded[i],
                                search: {
                                    caseInsensitive: true,
                                    regex: false,
                                    search: '',
                                    smart: true
                                }
                            })
                        }
                        delete state.cv;
                    }
                    // restore compressed state.search
                    if (typeof state.filter !== 'undefined' && state.filter) {
                        state.search = {
                            caseInsensitive: true,
                            regex: false,
                            search: state.filter ? JSON.stringify(state.filter) : '',
                            smart: true
                        };
                        delete state.filter;
                    } else if (typeof state.search === 'undefined' || !state.search) {
                        state.search = {
                            caseInsensitive: true,
                            regex: false,
                            search: '',
                            smart: true
                        };
                    }
                    // restore compressed state.order
                    if (typeof state.sort !== 'undefined' && $.isPlainObject(state.sort)) {
                        state.order = [];
                        var columns = api.columns().dataSrc();
                        for (var columnName in state.sort) {
                            var index = $.inArray(columnName, columns);
                            if (index >= 0) {
                                state.order.push([index, state.sort[columnName]]);
                            }
                        }
                        delete state.sort;
                    } else if (typeof state.order === 'undefined' || !state.order || !$.isArray(state.order)) {
                        state.order = [];
                    }
                    return state;
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.warn('Failed to parse DataTable state: ', state, e);
                    }
                }
            } else if (request.query.filter) {
                var filters = null;
                try {
                    filters = JSON.parse(request.query.filter);
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.warn('Invalid json for "filter" query arg');
                    }
                    return {};
                }
                try {
                    if (filters && $.isPlainObject(filters)) {
                        var tableConfigs = $(settings.nTable).data('configs');
                        var fieldNameToFilterIdMap = null;
                        if (
                            $.isPlainObject(tableConfigs)
                            && tableConfigs.hasOwnProperty('queryBuilderConfig')
                            && $.isPlainObject(tableConfigs.queryBuilderConfig)
                            && tableConfigs.queryBuilderConfig.hasOwnProperty('fieldNameToFilterIdMap')
                        ) {
                            fieldNameToFilterIdMap = tableConfigs.queryBuilderConfig.fieldNameToFilterIdMap;
                        }
                        var search = DataGridSearchHelper.convertKeyValueFiltersToRules(
                            filters,
                            fieldNameToFilterIdMap
                        );
                        if (search) {
                            return {
                                time: (new Date()).getTime(),
                                search: {
                                    caseInsensitive: true,
                                    regex: false,
                                    search: search,
                                    smart: true
                                }
                            };
                        }
                    }
                    return {};
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.error('Failed to apply filters from "filter" query arg', e);
                    }
                    return {};
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
            ScaffoldDataGridHelper.getCurrentDataGridApi().ajax.reload(null, false);
        }
    },
    reloadStateOfCurrentDataGrid: function (callback) {
        if (ScaffoldDataGridHelper.getCurrentDataGrid()) {
            var api = ScaffoldDataGridHelper.getCurrentDataGridApi();
            ScaffoldDataGridHelper.getCurrentDataGrid()._fnLoadState(api.settings()[0].oInit, function () {
                api.ajax.reload(null, false);
                if (typeof callback === 'function') {
                    callback();
                }
            });
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
            if (configs.queryBuilderConfig) {
                mergedConfigs.queryBuilderConfig = DataGridSearchHelper.prepareConfigs(
                    configs.queryBuilderConfig,
                    configs.defaultSearchRules
                );
            }
            $dataGrid
                .data('configs', mergedConfigs)
                .data('resourceName', mergedConfigs.resourceName)
                .DataTable(mergedConfigs)
                .on('init', function (event, settings) {
                    var $table = $(settings.nTable);
                    var $tableWrapper = $(settings.nTableWrapper);
                    $table.dataTable().api().settings()[0].resourceName = mergedConfigs.resourceName;
                    ScaffoldDataGridHelper.initToolbar($tableWrapper, configs);
                    if (mergedConfigs.queryBuilderConfig) {
                        DataGridSearchHelper.init($table, mergedConfigs.queryBuilderConfig);
                    }
                    ScaffoldDataGridHelper.initContextMenu($tableWrapper, $table, configs);
                    ScaffoldDataGridHelper.initClickEvents($tableWrapper, $table, configs);
                    ScaffoldDataGridHelper.initRowsRepositioning($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initMultiselect($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkLinks($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkEditing($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initNestedView($table, $tableWrapper, configsBackup, tableOuterHtml);
                })
                .on('preXhr', function (event, settings) {
                    CmfRoutingHelpers.cleanupHangedElementsInBody();
                    CmfRoutingHelpers.cleanupHangedElementsInContentWrapper();
                    $(this).dataTable().api().columns.adjust();
                })
                .on('draw', function (event, settings) {
                    $(settings.nTableWrapper)
                        .find('[data-toggle="tooltip"]')
                        .tooltip({container: Utils.getContentContainer()});
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
                    $toolbarEl.append(doT.template(configs.toolbarItems[i])({}));
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
    initContextMenu: function ($tableWrapper, $table, configs) {
        if (configs.contextMenuTpl) {
            if (typeof configs.contextMenuTpl !== 'function') {
                var $deferred = Utils.makeTemplateFromText(
                        configs.contextMenuTpl,
                        'Data grid context menu template'
                    )
                    .done(function (template) {
                        configs.contextMenuTpl = template;
                    })
                    .fail(function (error) {
                        throw error;
                    });
            }
            var api = $table.dataTable().api();

            var getMenuPosition = function ($menu, mouse, direction, scrollDir) {
                 var win = $(window)[direction]();
                 var scroll = $(window)[scrollDir]();
                 var menu = $menu[direction]();
                 var position = mouse + scroll;

                // opening menu would pass the side of the page
                if (mouse + menu > win && menu < mouse) {
                    position -= menu;
                }
                return position;
            };

            var $menu = null;
            $table
                .on('contextmenu', 'td', function (event) {
                    if (event.ctrlKey) {
                        return; //< default context menu
                    }
                    if ($menu) {
                        $menu.trigger('hide.contextmenu');
                        $menu = null;
                    }
                    var $tr = $(this).closest('tr');
                    var row = api.row($tr);
                    $menu = $(configs.contextMenuTpl(row.data()));
                    $(document.body).append($menu);
                    $tr.addClass('selected context-menu');
                    $menu
                        .slideDown(80)
                        .css({
                            position: "absolute",
                            left: getMenuPosition($menu, event.clientX, 'width', 'scrollLeft'),
                            top: getMenuPosition($menu, event.clientY, 'height', 'scrollTop')
                        })
                        .on('click', 'a', function () {
                            if ($(this).attr('data-action') === 'request') {
                                var $el = $(this);
                                var handleRequest = function ($el) {
                                    var blockDataGrid = !!$el.attr('data-block-datagrid');
                                    if (blockDataGrid) {
                                        Utils.showPreloader($tableWrapper);
                                    }
                                    ScaffoldActionsHelper
                                        .handleRequestAction(
                                            $el,
                                            function (json) {
                                                if (
                                                    json.redirect
                                                    && (
                                                        json.redirect === 'back'
                                                        || json.redirect === 'reload'
                                                        || json.redirect === document.location.path
                                                        || json.redirect === document.location.pathname
                                                        || json.redirect === document.location.href
                                                    )
                                                ) {
                                                    api.ajax.reload(null, false);
                                                    delete json.redirect;
                                                }
                                                ScaffoldActionsHelper.onSuccess(json);
                                            },
                                            function () {
                                                handleRequest($el, blockDataGrid);
                                            }
                                        )
                                        .always(function () {
                                            if (blockDataGrid) {
                                                Utils.hidePreloader($tableWrapper);
                                            }
                                        });
                                };
                                handleRequest($el);
                            }
                            $menu.trigger('hide.contextmenu');
                            $menu = null;
                        })
                        .on('mousedown', 'a', function (event) {
                            event.preventDefault();
                            return false;
                        })
                        .on('hide.contextmenu', function () {
                            var $item = $(this);
                            $table.find('tr.selected.context-menu').removeClass('selected context-menu');
                            $item.slideUp(80, function () {
                                $item.remove();
                            });
                        });
                    $('body').one('mousedown contextmenu keydown', function () {
                        if ($menu && !$.contains($menu[0], this)) {
                            $menu.trigger('hide.contextmenu');
                            $menu = null;
                        }
                    });
                    event.preventDefault();
                    return false;
                });
        }
    },
    initClickEvents: function ($tableWrapper, $table, configs) {
        var api = $table.dataTable().api();
        $tableWrapper.on('click tap', '[data-action]', function (event) {
            var $el = $(this);
            var action = String($el.attr('data-action')).toLowerCase();
            if (action.match(/redirect/i) === null) {
                event.preventDefault();
            }
            if ($el.hasClass('disabled')) {
                return false;
            }
            if (ScaffoldActionsHelper.beforeDataActionHandling($el, $table) === false) {
                return false;
            }

            switch (action) {
                case 'reload':
                    api.ajax.reload(null, false);
                    break;
                case 'bulk-filtered-redirect':
                case 'bulk-selected-redirect':
                    if (!$el.attr('href')) {
                        return false;
                    }
                    var url;
                    if (!$el.data('original-url')) {
                        url = $el.attr('href');
                        $el.data('original-url', url);
                    } else {
                        url = $el.data('original-url');
                    }
                    if (action === 'bulk-filtered-redirect') {
                        $el.attr('href', url + '?conditions=' + encodeURIComponent(JSON.stringify(api.search())));
                    } else {
                        var data = $(this).data('data');
                        if (!data.ids || !$.isArray(data.ids) || data.ids.length === 0) {
                            return false;
                        }
                        $el.attr('href', url + '?ids=' + encodeURIComponent(JSON.stringify(data.ids)));
                    }
                    return true;
                case 'bulk-filtered':
                    $el.data('data', {conditions: api.search()});
                    $el.attr('data-block-datagrid', '1');
                    // no break here!!!
                case 'bulk-selected':
                    $el.attr('data-block-datagrid', '1');
                    // no break here!!!
                case 'request':
                    var handleRequest = function ($el) {
                        var blockDataGrid = !!$el.attr('data-block-datagrid');
                        if (blockDataGrid) {
                            Utils.showPreloader($tableWrapper);
                        }
                        ScaffoldActionsHelper.handleRequestAction(
                                $el,
                                function (json) {
                                    if (
                                        json.redirect
                                        && (
                                            json.redirect === 'back'
                                            || json.redirect === 'reload'
                                            || json.redirect === document.location.path
                                            || json.redirect === document.location.pathname
                                            || json.redirect === document.location.href
                                        )
                                    ) {
                                        api.ajax.reload(null, false);
                                        delete json.redirect;
                                    }
                                    ScaffoldActionsHelper.onSuccess(json);
                                },
                                function () {
                                    handleRequest($el, blockDataGrid);
                                }
                            )
                            .always(function () {
                                if (blockDataGrid) {
                                    Utils.hidePreloader($tableWrapper);
                                }
                            });
                    };
                    handleRequest($el);
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
                        page.show(
                            configs.doubleClickUrl(api.row($(this)).data()),
                            null,
                            true,
                            true,
                            {env: {is_click: true, target: event.target}}
                        );
                    }
                }
                return false;
            });
        }
    },
    initRowsRepositioning: function ($table, $tableWrapper, config) {
        if (
            config.hasOwnProperty('rowsReordering')
            && $.isPlainObject(config.rowsReordering)
            && config.rowsReordering.hasOwnProperty('columns')
            && config.rowsReordering.hasOwnProperty('url')
        ) {
            var api = $table.dataTable().api();
            var onDraw = function () {
                var ordering = api.order();
                for (var i = 0; i < ordering.length; i++) {
                    var column = api.column(ordering[i][0]);
                    if ($.inArray(column.dataSrc(), config.rowsReordering.columns) >= 0) {
                        column.nodes().to$().addClass('reorderable');
                        break;
                    }
                }
            };
            onDraw();
            $table.on('draw.dt', onDraw);
            var urlTpl = doT.template(config.rowsReordering.url);

            new Sortable($table.find('tbody')[0], {
                group: $table[0].id,
                sorting: true,
                scroll: true,
                handle: '.reorderable',
                chosenClass: 'reordering-this-element',
                onUpdate: function (event) {
                    var direction = 'asc';
                    var columnName = null;
                    var ordering = api.order();
                    for (var i = 0; i < ordering.length; i++) {
                        var column = api.column(ordering[i][0]);
                        if ($.inArray(column.dataSrc(), config.rowsReordering.columns) >= 0) {
                            columnName = column.dataSrc();
                            direction = ordering[i][1];
                            break;
                        }
                    }
                    if (columnName !== null) {
                        var tplData = {
                            moved_row: api.row(event.oldIndex).data(),
                            before_or_after: event.oldIndex > event.newIndex ? 'before' : 'after',
                            other_row: api.row(event.newIndex).data(),
                            sort_column: columnName,
                            sort_direction: direction
                        };
                        Utils.showPreloader($tableWrapper);
                        $.ajax({
                                url: urlTpl(tplData),
                                method: 'POST',
                                data: {
                                    _method: 'PUT'
                                },
                                type: 'json'
                            })
                            .done(function (json) {
                                Utils.handleAjaxSuccess(json);
                            })
                            .fail(function (xhr) {
                                Utils.handleAjaxError.call(this, xhr);
                            })
                            .always(function () {
                                Utils.hidePreloader($tableWrapper);
                                ScaffoldDataGridHelper.reloadCurrentDataGrid();
                            });
                    }
                }
            });
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
            '[data-action="bulk-selected"], [data-action="bulk-edit-selected"], [data-action="bulk-selected-redirect"], [data-type="bulk-selected"], [data-type="bulk-edit-selected"], [data-type="bulk-selected-redirect"]'
        );
        var $fitleringLinks = $tableWrapper.find(
            '[data-action="bulk-filtered"], [data-action="bulk-filtered-redirect"], [data-action="bulk-edit-filtered"], [data-type="bulk-filtered"], [data-type="bulk-edit-filtered"], [data-type="bulk-filtered-redirect"]'
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
                var count = 0;
                if (api.search()) {
                    var rules = DataGridSearchHelper.decodeRulesForDataTable(
                        JSON.parse(api.search()),
                        DataGridSearchHelper.getFieldNameToFilterIdMapForQueryBuilder()
                    );
                    if (DataGridSearchHelper.countRules(rules) > 0) {
                        count = api.page.info().recordsTotal || 0;
                    }
                }
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
            ScaffoldFormHelper.handleBulkEditForm($link, configs.resourceName, api);
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
                    var $tr = $(this).closest('tr');
                    var row = api.row($tr);
                    $(this).addClass('hidden');
                    if (!$tr.hasClass('has-children-table')) {
                        $tr.addClass('has-children-table');
                        var parentId = row.data()[subTableConfigs.nested_data_grid.value_column];
                        var $subTable = $(tableOuterHtml);
                        $subTable
                            .attr('id', $subTable.attr('id') + '-children-for-' + parentId)
                            .attr('data-depth', (parseInt($tr.closest('.children-data-grid').attr('data-depth')) || 0) + 1)
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
                                ScaffoldDataGridHelper.initNestedView($subTable, $subTableWrapper, subTableConfigs, tableOuterHtml);
                                return false;
                            })
                            .on('xhr', function (event, settings, json) {
                                var depth = $subTable.attr('data-depth');
                                for (var i = 0, len = json.data.length; i < len; i++) {
                                    json.data[i].___nesting_depth = depth;
                                }
                            })
                            .on('draw', function (event, settings) {
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
                .on('click', 'a.hide-children', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var $tr = $(this).closest('tr');
                    var row = api.row($tr);
                    $(this).tooltip('hide').addClass('hidden');
                    if (row.child.isShown()) {
                        row.child.hide();
                    }
                    $tr
                        .removeClass('children-table-opened')
                        .find('a.show-children')
                            .removeClass('hidden');
                    return false;
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
    getQueryBuilderNode: function () {
        return $('#' + DataGridSearchHelper.id);
    },
    getDataGridNodeForQueryBuilder: function ($builder) {
        return ($builder ? $builder : DataGridSearchHelper.getQueryBuilderNode()).data('dataGrid');
    },
    getDataGridApiForQueryBuilder: function ($builder) {
        return ($builder ? $builder : DataGridSearchHelper.getQueryBuilderNode()).data('dataGridApi');
    },
    getFieldNameToFilterIdMapForQueryBuilder: function ($builder) {
        return ($builder ? $builder : DataGridSearchHelper.getQueryBuilderNode()).data('config').fieldNameToFilterIdMap;
    },
    prepareConfigs: function (config, defaultRules) {
        if (!config || !config.filters || config.filters.length <= 0) {
            return;
        }
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
        $.extend(
            builderConfig,
            DataGridSearchHelper.defaultConfig,
            config
        );
        builderConfig.fieldNameToFilterIdMap = DataGridSearchHelper.makeFieldNameToFilterIdMapFromFiltersConfig(builderConfig.filters);
        return builderConfig;
    },
    init: function ($dataGrid, builderConfig) {
        if (!builderConfig) {
            return;
        }
        var $builder = DataGridSearchHelper.getQueryBuilderNode();
        $builder.prepend('<h4>' + DataGridSearchHelper.locale.header + '</h4>');
        var $builderContent = $('<div></div>').attr('id' , DataGridSearchHelper.id + '-content');
        $builder.append($builderContent);
        var tableApi = $dataGrid.dataTable().api();
        $dataGrid.data('queryBuilder', $builder);
        $builder.data('dataGrid', $dataGrid);
        $builder.data('dataGridApi', tableApi);
        $builderContent.queryBuilder(builderConfig);
        $builder.data('config', builderConfig);
        if (tableApi.search().length) {
            try {
                var currentSearch = JSON.parse(tableApi.search());
                var decoded = DataGridSearchHelper.decodeRulesForDataTable(
                    currentSearch,
                    builderConfig.fieldNameToFilterIdMap
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
                    encoded = DataGridSearchHelper.encodeRulesForDataTable(
                        DataGridSearchHelper.emptyRules,
                        builderConfig.fieldNameToFilterIdMap
                    );
                } else if ($builderContent.queryBuilder('validate')) {
                    encoded = DataGridSearchHelper.encodeRulesForDataTable(
                        rules,
                        builderConfig.fieldNameToFilterIdMap
                    );
                }
                tableApi.search(encoded).draw();
            }
        });
        var $resetFilteringBtnInToolbar = $('<button class="btn btn-danger reset-filter" type="button"></button>')
            .text(DataGridSearchHelper.locale.reset);
        $resetFilteringBtnInToolbar
            .hide()
            .on('click', function () {
                $builderContent.queryBuilder('reset');
                // $builderContent.queryBuilder('setRules', builderConfig.rules);
                $runFilteringBtn.click();
                // if (DataGridSearchHelper.countRules(builderConfig.rules) === 0) {
                    $resetFilteringBtnInToolbar.hide();
                // }
            });
        var $resetFilteringBtnInFilter = $resetFilteringBtnInToolbar.clone(true).show();
        var $toolbar = $builder
            .closest('.dataTables_wrapper')
            .find('.filter-toolbar');
        if (builderConfig.is_opened) {
            console.warn('Filter: is_opened option is not working currently and may never work in future');
            // $resetFilteringBtnInToolbar.show();
            // $toolbar
            //     .append($runFilteringBtn)
            //     .append($resetFilteringBtnInToolbar);
        } /*else {*/
            var $counterBadge = $('<span class="counter label label-success ml10"></span>');
            var $filterToggleButton = $('<button class="btn btn-default" type="button"></button>')
                .text(DataGridSearchHelper.locale.toggle)
                .append($counterBadge);
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
            var toggleFilterPanel = function (hideFilterPanel, filterToggleButtonClicked) {
                if (hideFilterPanel && !$builderContent.queryBuilder('validate', {skip_empty: true})) {
                    // do not hide filter until there are invalid rules
                    return false;
                }
                var $filterPanel = $('#' + DataGridSearchHelper.id);
                var rulesCount = DataGridSearchHelper.countRules($builderContent.queryBuilder('getRules'));
                if (hideFilterPanel) {
                    $filterPanel.collapse('hide');
                    $filterToggleButton.removeClass('active');
                    $runFilteringBtn.hide();
                    if (rulesCount === 0) {
                        $resetFilteringBtnInToolbar.hide();
                    }
                } else {
                    $filterPanel.collapse('show');
                    $filterToggleButton.addClass('active');
                    $runFilteringBtn.show();
                    if (rulesCount > 0) {
                        $resetFilteringBtnInToolbar.show();
                    }
                }
                return true;
            };
            $filterToggleButton
                .on('click', function () {
                    toggleFilterPanel($filterToggleButton.hasClass('active'));
                });
            $toolbar.append($filterToggleButton);
            $runFilteringBtn
                .on('click', function () {
                    if (toggleFilterPanel(true)) {
                        changeCountInBadge();
                    }
                })
                .hide();
            var $filterCloseButton = $('<button type="button" class="btn btn-default btn-sm">')
                .text(DataGridSearchHelper.locale.close)
                .on('click', function () {
                    toggleFilterPanel(true);
                });
            $builder.append(
                $('<div id="query-builder-controls">')
                    .append($filterCloseButton.addClass('pull-left'))
                    .append($runFilteringBtn.addClass('btn-sm'))
                    .append($resetFilteringBtnInFilter.addClass('btn-sm'))
            );
            $toolbar.append($resetFilteringBtnInToolbar);
        //}
    },
    encodeRulesForDataTable: function (rules, fieldNameToFilterIdMap, asObject) {
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
        if (!$.isPlainObject(fieldNameToFilterIdMap)) {
            fieldNameToFilterIdMap = {___unprefixedToPrefixedMap: {}};
        } else if (!$.isPlainObject(fieldNameToFilterIdMap.___unprefixedToPrefixedMap)) {
            fieldNameToFilterIdMap.___unprefixedToPrefixedMap = {};
        }
        if (rules.rules) {
            for (var i = 0; i < rules.rules.length; i++) {
                var rule = rules.rules[i];
                if (rule.condition) {
                    ret.r.push(DataGridSearchHelper.encodeRulesForDataTable(rule, fieldNameToFilterIdMap, true));
                } else {
                    if (fieldNameToFilterIdMap.___unprefixedToPrefixedMap.hasOwnProperty(rule.field)) {
                        // unprefixed field name detected: use prefixed name
                        rule.field = fieldNameToFilterIdMap.___unprefixedToPrefixedMap[rule.field];
                    }
                    ret.r.push({
                        f: rule.field,
                        o: rule.operator,
                        v: DataGridSearchHelper.normalizeRuleValue(rule.value)
                    });
                }
            }
        }
        return asObject ? ret : JSON.stringify(ret);
    },
    makeFieldNameToFilterIdMapFromFiltersConfig: function (filters) {
        var map = {___unprefixedToPrefixedMap: {}};
        for (var i = 0; i < filters.length; i++) {
            map[filters[i].field] = filters[i].id;
            // add unprefixed field names (for 'Orders.is_accepted' field unprefixed name will be 'is_accepted')
            // in case when map already contains unprefixed name - this one will be dropped
            var matches = filters[i].field.match(/\.([a-zA-Z_0-9-]+)$/);
            if (matches !== null && !map.hasOwnProperty(matches[1])) {
                map[matches[1]] = filters[i].id;
                map.___unprefixedToPrefixedMap[matches[1]] = filters[i].field;
            }
        }
        return map;
    },
    decodeRulesForDataTable: function (rules, fieldNameToFilterIdMap) {
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
                var subrules = DataGridSearchHelper.decodeRulesForDataTable(rule, fieldNameToFilterIdMap);
                if (subrules !== null) {
                    ret.rules.push(subrules);
                }
            } else {
                if (!fieldNameToFilterIdMap[rule.f]) {
                    continue;
                }
                ret.rules.push({
                    id: fieldNameToFilterIdMap[rule.f],
                    operator: rule.o,
                    value: DataGridSearchHelper.normalizeRuleValue(rule.v)
                });
            }
        }
        return ret;
    },
    countRules: function (rules) {
        if (!rules) {
            return 0;
        }
        rules = DataGridSearchHelper.encodeRulesForDataTable(rules, null, true);
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
    convertKeyValueFiltersToRules: function (filters, fieldNameToFilterIdMap) {
        var rules = [];
        if (!filters) {
            return false;
        }
        for (var field in filters) {
            if (typeof field === 'string') {
                rules.push({
                    field: field,
                    operator: 'equal',
                    value: DataGridSearchHelper.normalizeRuleValue(filters[field])
                });
            }
        }
        if (rules.length) {
            return DataGridSearchHelper.encodeRulesForDataTable(
                {
                    condition: 'AND',
                    rules: rules
                },
                fieldNameToFilterIdMap
            );
        } else {
            return false;
        }
    },
    normalizeRuleValue: function (value) {
        if (value === true) {
            return 't';
        } else if (value === false) {
            return 'f';
        }
        return value;
    }
};

var ScaffoldFormHelper = {
    loadOptions: function (resourceName, itemId, ignoreCache) {
        ignoreCache = true;
        var deferred = $.Deferred();
        var query = itemId ? '?id=' + itemId : '';
        var cacheKey = resourceName + (itemId ? '' : String(itemId));
        if (
            ignoreCache
            || !ScaffoldsManager.cacheTemplates
            || !CmfCache.selectOptions[cacheKey]
            || CmfCache.selectOptionsTs[cacheKey] + 30000 < Date.now()
        ) {
            $.ajax({
                url: ScaffoldsManager.getResourceBaseUrl(resourceName) + '/service/options' + query,
                method: 'GET',
                cache: false
            }).done(function (data) {
                CmfCache.selectOptionsTs[cacheKey] = Date.now();
                CmfCache.selectOptions[cacheKey] = data;
                deferred.resolve(CmfCache.selectOptions[cacheKey]);
            }).fail(function (xhr) {
                Utils.handleAjaxError.call(this, xhr, deferred);
            });
        } else {
            deferred.resolve(CmfCache.selectOptions[cacheKey]);
        }
        return deferred.promise();
    },
    cleanOptions: function (resourceName, itemId) {
        var cacheKey = resourceName + (itemId ? '' : String(itemId));
        delete CmfCache.selectOptions[cacheKey];
        delete CmfCache.selectOptionsTs[cacheKey];
    },
    initForm: function ($form, successCallback) {
        FormHelper.initForm($form, $form, successCallback);
    },
    handleBulkEditForm: function ($link, resourceName, api) {
        try {
            var deferred = ScaffoldsManager.getBulkEditFormTpl(resourceName);
        } catch (exc) {
            toastr.error(exc);
        }
        $('.modal.in').modal('hide'); //< hide any opened modals
        Utils.showPreloader(CmfRoutingHelpers.$currentContentContainer);
        var timeout = setTimeout(function () {
            CmfRoutingHelpers.hideContentContainerPreloader();
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
                    .not('.bulk-edit-form-input-enabler-switch, .bulk-edit-form-input-enabler-input')
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
                                .find('.bulk-edit-form-input-enabler-switch, .bulk-edit-form-input-enabler-input')
                                    .prop('checked', true)
                                    .change();
                    });
                // switch editing on/off by changing enabler input
                var isFormSubmitAllowed = function () {
                    return $bulkEditForm.find(
                        '.bulk-edit-form-input-enabler-switch:checked, .bulk-edit-form-input-enabler-input:checked'
                    ).length > 0;
                };
                var enablerValueChangeHandler = function () {
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
                    $bulkEditForm.find('[type="submit"]').prop('disabled', !isFormSubmitAllowed());
                };
                $bulkEditForm
                    .find('.bulk-edit-form-input-enabler-switch')
                    .on('change switchChange.bootstrapSwitch', enablerValueChangeHandler)
                    .change();
                $bulkEditForm
                    .find('.bulk-edit-form-input-enabler-input')
                    .on('change click', enablerValueChangeHandler)
                    .change();

                // add resulting html to page and open modal
                $(document.body).append($bulkEditModal);
                $bulkEditModal
                    .on('hidden.bs.modal', function () {
                        $bulkEditModal.remove();
                    })
                    .on('show.bs.modal', function () {
                        $bulkEditForm
                            .on('submit', function () {
                                return isFormSubmitAllowed();
                            });
                        ScaffoldFormHelper.initForm($bulkEditForm, function (json, $form, $container) {
                            if (json._message) {
                                toastr.success(json._message);
                            }
                            $bulkEditModal.modal('hide');
                            var resetDataGridPagination = true;
                            var pkColumn = $(api.table().node()).data('configs').pkColumnName;
                            if (pkColumn) {
                                var orders = api.order();
                                if (orders.length && api.column(orders[0]).dataSrc() === pkColumn) {
                                    resetDataGridPagination = false;
                                }
                            }
                            api.rows().deselect();
                            api.ajax.reload(null, resetDataGridPagination);
                        });
                    })
                    .on('shown.bs.modal', function () {
                        $('body').addClass('modal-open');
                    })
                    .modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
            })
            .always(function () {
                clearTimeout(timeout);
                CmfRoutingHelpers.hideContentContainerPreloader();
            });
    },
    initWysiwyg: function (textareaSelector, config) {
        if (!$.isPlainObject(config)) {
            config = {}
        }
        if (typeof config.data_inserts !== 'undefined' && $.isArray(config.data_inserts)) {
            config = ScaffoldFormHelper.addDataInsertsPluginToWysiwyg(config);
        }
        if (typeof config.html_inserts !== 'undefined' && $.isArray(config.html_inserts)) {
            config = ScaffoldFormHelper.addHtmlInsertsPluginToWysiwyg(config);
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
            var renderInsert = function (tplData) {
                var tag = tplData.__tag || 'div';
                return $('<insert></insert>')
                    .append(
                        $('<' + tag + '></' + tag + '>')
                        .addClass('wysiwyg-data-insert')
                        .attr('title', (tplData.title || '').replace(/"/g, '\''))
                        .text(tplData.code || '')
                    )
                    .html();
            };
            CKEDITOR.plugins.add(pluginName, {
                requires: 'widget',
                allowedContent: allowedContent,
                init: function (editor) {
                    editor.ui.addRichCombo('cmfScaffoldDataInserter', {
                        label: locale.cmf_scaffold_data_inserts_plugin_title,
                        title: locale.cmf_scaffold_data_inserts_plugin_title,
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
                                var insertInfo = editor.config.data_inserts[i];
                                var optionValue;
                                insertInfo.title = $.trim(insertInfo.title);
                                if (insertInfo.args_options && $.isPlainObject(insertInfo.args_options)) {
                                    insertInfo.args_options.__tag = {
                                        type: 'select',
                                        label: locale.cmf_scaffold_data_inserts_dialog_insert_tag_name,
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
                                                    var escaped;
                                                    if (insertInfo.args_options[argName].base64) {
                                                        escaped = Base64.encode(data[argName]);
                                                    } else {
                                                        escaped = data[argName].replace(/(['"])/g, '\\$1');
                                                    }
                                                    tplData.code = tplData.code.replace(':' + argName, escaped);
                                                }
                                                if (insertInfo.widget_title_tpl) {
                                                    tplData.title = tplData.widget_title_tpl;
                                                    for (argName in data) {
                                                        if (insertInfo.args_options[argName] && insertInfo.args_options[argName].type === 'select') {
                                                            // select
                                                            tplData.title = tplData.title.replace(
                                                                ':' + argName + '.value',
                                                                $.trim(data[argName])
                                                            );
                                                            if (optionsOfAllSelects[argName]) {
                                                                tplData.title = tplData.title.replace(
                                                                    ':' + argName + '.label',
                                                                    $.trim(optionsOfAllSelects[argName][data[argName]]) || ''
                                                                );
                                                            }
                                                        } else {
                                                            // text or checkbox
                                                            tplData.title = tplData.title.replace(':' + argName, $.trim(data[argName]));
                                                        }
                                                    }
                                                }
                                                tplData.title = $.trim(tplData.title);
                                                editor.focus();
                                                editor.fire('saveSnapshot');
                                                if (tplData.__tag === 'div') {
                                                    var currentSelectedElement;
                                                    if (editor.getSelection().getRanges().length > 0) {
                                                        currentSelectedElement = editor.getSelection().getRanges()[0].getCommonAncestor(true, true);
                                                    } else {
                                                        currentSelectedElement = editor.createRange().root;
                                                    }
                                                    var range = editor.createRange();
                                                    range.moveToPosition(currentSelectedElement, CKEDITOR.POSITION_AFTER_END);
                                                    editor.getSelection().selectRanges([range]);
                                                    editor.insertHtml(renderInsert(tplData), 'unfiltered_html');
                                                    var $selectedElement = $(currentSelectedElement.$);
                                                    if ($selectedElement.filter('p').length && $.trim($selectedElement.text().replace(/&nbsp;/ig, '')) === '') {
                                                        // replace empty <p> element
                                                        $selectedElement.remove();
                                                    }
                                                } else {
                                                    editor.insertHtml(renderInsert(tplData), 'unfiltered_html');
                                                }
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
                                    optionValue = 'insert:' + i;
                                }
                                combobox.add(optionValue, insertInfo.title, insertInfo.title);
                                // combobox._.committed = 0;
                                // combobox.commit(); //< ty good people of web!!!
                            }
                        },
                        onClick: function (value) {
                            var matches = value.match(/^(dialog|insert):(.*)$/);
                            if (matches !== null) {
                                switch (matches[1]) {
                                    case 'dialog':
                                        editor.openDialog(matches[2]);
                                        break;
                                    case 'insert':
                                        var insertInfo = editor.config.data_inserts[parseInt(matches[2])];
                                        if (insertInfo) {
                                            editor.focus();
                                            editor.fire('saveSnapshot');
                                            if (insertInfo.is_block) {
                                                var currentSelectedElement;
                                                if (editor.getSelection().getRanges().length > 0) {
                                                    currentSelectedElement = editor.getSelection().getRanges()[0].getCommonAncestor(true, true);
                                                } else {
                                                    currentSelectedElement = editor.createRange().root;
                                                }
                                                var range = editor.createRange();
                                                range.moveToPosition(currentSelectedElement, CKEDITOR.POSITION_AFTER_END);
                                                editor.getSelection().selectRanges([range]);
                                                editor.insertHtml(renderInsert(insertInfo), 'unfiltered_html');
                                                var $selectedElement = $(currentSelectedElement.$);
                                                if ($selectedElement.filter('p').length && $.trim($selectedElement.text().replace(/&nbsp;/ig, '')) === '') {
                                                    // replace empty <p> element
                                                    $selectedElement.remove();
                                                }
                                            } else {
                                                editor.insertHtml(renderInsert(insertInfo), 'unfiltered_html');
                                            }
                                            editor.fire('saveSnapshot');
                                        } else {
                                            console.error('Insert with index ' + matches[2] + ' not found');
                                        }
                                        break;
                                }
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
                        + 'div.wysiwyg-data-insert{margin: 10px 0 10px 0}'
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
            switch (inputConfig.type) {
                case 'select':
                    if (!inputConfig.options) {
                        console.error(dialogHeader + ': options are required for input "' + inputName + '"');
                        argsAreValid = false;
                    }
                    var tmpConfig = {
                        type: 'select',
                        label: inputConfig.label,
                        id: inputName, //< note: also used to collect optionsOfAllSelects
                        items: [],
                        'default': typeof inputConfig.value !== 'undefined' ? inputConfig.value : null
                    };
                    if ($.isPlainObject(inputConfig.options)) {
                        for (var optionValue in inputConfig.options) {
                            tmpConfig.items.push([inputConfig.options[optionValue], optionValue]);
                        }
                        tmpConfig.onLoad = function () {
                            var select = this;
                            var $select = $(select._.select.getElement().$);
                            optionsOfAllSelects[select.id] = {};
                            $select.find('option').each(function () {
                                optionsOfAllSelects[select.id][this.value] = $(this).text();
                            });
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
                                        optionsOfAllSelects[select.id] = {};
                                        $select.find('option').each(function () {
                                            optionsOfAllSelects[select.id][this.value] = $(this).text();
                                        });
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
                case 'textarea':
                    dialogElements.push({
                        type: 'textarea',
                        label: inputConfig.label,
                        id: inputName,
                        'default': typeof inputConfig.value !== 'undefined' ? inputConfig.value : ''
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
    },
    addHtmlInsertsPluginToWysiwyg: function (curentWysiwygConfig) {
        var pluginName = 'cmf_scaffold_html_inserter';
        if (curentWysiwygConfig.extraPlugins) {
            curentWysiwygConfig.extraPlugins += ',' + pluginName;
        } else {
            curentWysiwygConfig.extraPlugins = pluginName;
        }
        if (!CKEDITOR.plugins.get(pluginName)) {
            var comboboxPanelCss = 'body{font-family:Arial,sans-serif;font-size:14px;}';
            var locale = CmfConfig.getLocalizationStringsForComponent('ckeditor');
            var renderInsert = function (tplData) {
                return $('<insert></insert>')
                    .append(tplData.html)
                    .html();
            };
            CKEDITOR.plugins.add(pluginName, {
                allowedContent: '*[!class]; *[!id]; a[!href]',
                disallowedContent: 'script style',
                init: function (editor) {
                    editor.ui.addRichCombo('cmfScaffoldHtmlInserter', {
                        label: locale.cmf_scaffold_html_inserts_plugin_title,
                        title: locale.cmf_scaffold_html_inserts_plugin_title,
                        toolbar: 'insert',
                        className: 'cke_combo_full_width',
                        multiSelect: false,
                        panel: {
                            css: [CKEDITOR.skin.getPath("editor")].concat(comboboxPanelCss + (editor.contentsCss || '')),
                            multiSelect: false
                        },
                        init: function () {
                            var combobox = this;
                            for (var i = 0; i < editor.config.html_inserts.length; i++) {
                                var insertInfo = editor.config.html_inserts[i];
                                var optionValue;
                                insertInfo.title = $.trim(insertInfo.title);
                                optionValue = 'html:' + i;
                                combobox.add(optionValue, insertInfo.title, insertInfo.title);
                            }
                        },
                        onClick: function (value) {
                            var matches = value.match(/^(html):(.*)$/);
                            if (matches !== null) {
                                switch (matches[1]) {
                                    case 'html':
                                        var insertInfo = editor.config.html_inserts[parseInt(matches[2])];
                                        if (insertInfo) {
                                            editor.focus();
                                            editor.fire('saveSnapshot');
                                            if (insertInfo.is_block) {
                                                var currentSelectedElement;
                                                if (editor.getSelection().getRanges().length > 0) {
                                                    currentSelectedElement = editor.getSelection().getRanges()[0].getCommonAncestor(true, true);
                                                } else {
                                                    currentSelectedElement = editor.createRange().root;
                                                }
                                                var range = editor.createRange();
                                                range.moveToPosition(currentSelectedElement, CKEDITOR.POSITION_AFTER_END);
                                                editor.getSelection().selectRanges([range]);
                                                editor.insertHtml(renderInsert(insertInfo), 'unfiltered_html');
                                                var $selectedElement = $(currentSelectedElement.$);
                                                if ($selectedElement.filter('p').length && $.trim($selectedElement.text().replace(/&nbsp;/ig, '')) === '') {
                                                    // replace empty <p> element
                                                    $selectedElement.remove();
                                                }
                                            } else {
                                                editor.insertHtml(renderInsert(insertInfo), 'unfiltered_html');
                                            }
                                            editor.fire('saveSnapshot');
                                        } else {
                                            console.error('HTML Insert with index ' + matches[2] + ' not found');
                                        }
                                        break;
                                }
                            } else {
                                editor.focus();
                                editor.fire('saveSnapshot');
                                editor.insertHtml(value);
                                editor.fire('saveSnapshot');
                            }
                        }
                    });
                }
            });
        }
        return curentWysiwygConfig;
    }
};