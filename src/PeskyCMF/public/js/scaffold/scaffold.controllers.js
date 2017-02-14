var ScaffoldControllers = {
    dataGrid: CmfView.extend({
        getContainer: Utils.getContentContainer,
        sigleton: true,
        switchBodyClass: function (request) {
            Utils.switchBodyClass('resource-' + request.params.resource);
        },
        loadTemplate: function (request) {
            return ScaffoldsManager.getDataGridTpl(request.params.resource);
        },
        afterRender : function (event, request) {

        }
    }),
    itemForm: CmfView.extend({
        getContainer: Utils.getContentContainer,
        sigleton: true,
        switchBodyClass: function (request) {
            Utils.switchBodyClass('resource-' + request.params.resource + '-' + request.params.id);
        },
        loadTemplate: function (request) {
            return ScaffoldsManager.getItemFormTpl(request.params.resource);
        },
        loadData: function (request) {
            var model = ScaffoldFormHelper.getModel(request.params.resource);
            if (request.params.id && request.params.id !== 'create') {
                model({id: request.params.id});
            }
            var deferred = $.Deferred();
            $.when(model.fetch(), ScaffoldFormHelper.loadOptions(request.params.resource, model.get('id')))
                .done(function (modelResponse, optionsResponse) {
                    model.set('_options', optionsResponse);
                    deferred.resolve(model);
                });
            return deferred;
        },
        getData: function () {
            return $.isObservable(this.data) ? this.data.toJSON() : this.data;
        },
        setData: function (data) {
            this.data = $.isObservable(data) ? data : $.observable(data);
            return this;
        },
        afterRender : function (event, request) {
            ScaffoldActionsHelper.initActions(this.$el);
            ScaffoldFormHelper.initForm(this.$el.find('form'), function (json, form, container) {
                if (json._message) {
                    toastr.success(json._message);
                }
                if (json.redirect) {
                    if (json.redirect === 'reload') {
                        window.adminApp.reload();
                    } else {
                        window.adminApp.nav(json.redirect);
                    }
                } else {
                    window.adminApp.back(form.attr('data-back-url'));
                }
            });
        }
    }),
    itemDetails: CmfView.extend({
        getContainer: Utils.getContentContainer,
        sigleton: true,
        switchBodyClass: function (request) {
            Utils.switchBodyClass('resource-' + request.params.resource + '-view-' + request.params.id);
        },
        loadTemplate: function (request) {
            return ScaffoldsManager.getItemDetailsTpl(request.params.resource);
        },
        loadData: function (request) {
            var model = ScaffoldFormHelper.getModel(request.params.resource);
            model({id: request.params.id, _is_details: true});
            return model.fetch()
        },
        getData: function () {
            return $.isObservable(this.data) ? this.data.toJSON() : this.data;
        },
        setData: function (data) {
            this.data = $.isObservable(data) ? data : $.observable(data);
            return this;
        },
        afterRender : function (event, request) {
            ScaffoldActionsHelper.initActions(this.$el);
            var customInitiator = this.$el.find('table.item-details-table').attr('data-initiator');
            if (customInitiator && customInitiator.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
                eval('customInitiator = ' + customInitiator);
                if (typeof customInitiator === 'function') {
                    customInitiator.call(this.$el);
                }
            }
        }
    }),
    itemCustomPage: CmfControllers.pageController.extend({
        switchBodyClass: function (request) {
            Utils.switchBodyClass(this.bodyClass || 'resource-' + request.params.resource + '-' + request.params.page + '-' + request.params.id);
        }
    })
};

var ScaffoldActionsHelper = {
    initActions: function (container, useLiveEvents) {
        if (useLiveEvents) {
            $(container).on('click tap', '[data-action]', function (event) {
                ScaffoldActionsHelper.handleDataAction(this, container);
                return false;
            });
        } else {
            $(container).find('[data-action]').on('click tap', function (event) {
                ScaffoldActionsHelper.handleDataAction(this, container);
                return false;
            });
        }
    },
    handleDataAction: function (el, container) {
        var $el = $(el);
        var action = String($el.attr('data-action')).toLowerCase();
        switch (action) {
            case 'request':
                Utils.showPreloader(container);
                ScaffoldActionsHelper.handleRequestAction($el, ScaffoldActionsHelper.onSuccess)
                    .always(function () {
                        Utils.hidePreloader(container);
                    });
                break;
            case 'redirect':
                ScaffoldsManager.app.nav($el.attr('data-url'));
                break;
            case 'reload':
                ScaffoldsManager.app.reload();
                break;
            case 'back':
                var defaultUrl = $el.attr('data-url') || CmfConfig.rootUrl;
                ScaffoldsManager.app.back(defaultUrl);
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
                var newUrl = window.adminApp.request.path + '?' + settings.sTableId + '=' + rison.encode_object(state);
                window.history.pushState(null, document.title, newUrl);
                window.adminApp.addToHistory(newUrl);
                ScaffoldDataGridHelper.hideRowActions($(settings.nTable));
            }
        },
        stateLoadCallback: function (settings) {
            if (window.adminApp.request.query[settings.sTableId]) {
                try {
                    return rison.decode_object(window.adminApp.request.query[settings.sTableId]);
                } catch (e) {
                    if (CmfConfig.isDebug) {
                        console.log('Invalid Rison object');
                    }
                }
            } else if (window.adminApp.request.query.filter) {
                try {
                    var filters = JSON.parse(window.adminApp.request.query.filter);
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
    init: function (dataGrid, configs) {
        dataGrid = $(dataGrid);
        if (dataGrid.length) {
            if (!$.isPlainObject(configs)) {
                configs = {};
            }
            var mergedConfigs = $.extend(
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
            dataGrid.DataTable(mergedConfigs)
                .on('init', function (event, settings) {
                    var $table = $(settings.nTable);
                    var $tableWrapper = $(settings.nTableWrapper);
                    $table.data('configs', mergedConfigs);
                    ScaffoldDataGridHelper.initToolbar($tableWrapper, configs.toolbarItems, configs.filterToolbarItems);
                    ScaffoldDataGridHelper.initClickEvents($tableWrapper, $table, configs);
                    ScaffoldDataGridHelper.initRowActions($table, configs);
                    ScaffoldDataGridHelper.initMultiselect($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkLinks($table, $tableWrapper, configs);
                    ScaffoldDataGridHelper.initBulkEditing($table, $tableWrapper, configs);
                }).on('preXhr', function (event, settings) {
                    ScaffoldDataGridHelper.hideRowActions($(settings.nTable));
                });
            return dataGrid;
        } else {
            throw 'Invalid data grid id: ' + dataGrid
        }
    },
    initToolbar: function ($tableWrapper, customToolbarItems, customFilterToolbarItems) {
        var $toolbarEl = $tableWrapper.find('.toolbar');
        var $filterToolbar = $tableWrapper.find('.filter-toolbar');
        var $reloadBtn = $('<button class="btn btn-default" data-action="reload"></button>')
            .html(CmfConfig.getLocalizationStringsForComponent('data_tables').toolbar.reloadData);
        if ($filterToolbar.length) {
            $filterToolbar.prepend($reloadBtn);
        }
        if ($.isArray(customFilterToolbarItems)) {
            for (var i = 0; i < customFilterToolbarItems.length; i++) {
                $filterToolbar.append(customFilterToolbarItems[i]);
            }
        }
        if ($toolbarEl.length) {
            if (!$filterToolbar.length) {
                $toolbarEl.prepend($reloadBtn);
            }
            if ($.isArray(customToolbarItems)) {
                for (i = 0; i < customToolbarItems.length; i++) {
                    $toolbarEl.append(customToolbarItems[i]);
                }
            }
        }
    },
    initClickEvents: function ($tableWrapper, $table, configs) {
        var api = $table.dataTable().api();
        $tableWrapper.on('click tap', '[data-action]', function (event) {
            var $el = $(this);
            if ($el.hasClass('disabled')) {
                return;
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
                                    || json.redirect === ScaffoldsManager.app.activeRequest.href
                                    || json.redirect === ScaffoldsManager.app.activeRequest.path
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
                if (!$(event.target).hasClass('select-checkbox')) {
                    ScaffoldsManager.app.nav(configs.doubleClickUrl(api.row($(this)).data()));
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
    }
};

var DataGridSearchHelper = {
    container: '#query-builder',
    defaultConfig: {
        plugins: ['bt-tooltip-errors', 'bt-checkbox', 'bt-selectpicker'],
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
            var $builder = $(DataGridSearchHelper.container);
            $builder.prepend('<h4>' + DataGridSearchHelper.locale.header + '</h4>');
            var tableApi = $dataGrid.dataTable().api();
            for (var i in config.filters) {
                if (
                    (config.filters[i].input === 'radio' || config.filters[i].input === 'checkbox')
                    && !config.filters[i].color
                ) {
                    config.filters[i].color = 'primary';
                }
            }
            var builderConfig = {};
            if ($.isArray(defaultRules)) {
                builderConfig = {rules: defaultRules};
            } else if (defaultRules.rules) {
                builderConfig = $.extend({}, defaultRules);
            }
            builderConfig = $.extend(builderConfig, DataGridSearchHelper.defaultConfig, config);
            $builder.queryBuilder(builderConfig);
            try {
                var currentSearch = JSON.parse(tableApi.search());
                var decoded = DataGridSearchHelper.decodeRulesForDataTable(
                    currentSearch,
                    DataGridSearchHelper.getFieldNameToFilterIdMap(config.filters)
                );
                if (decoded.rules && decoded.rules.length) {
                    $builder.queryBuilder('setRules', decoded);
                } else {
                    $builder.queryBuilder('reset');
                }
            } catch (ignore) {}
            var $runFilteringBtn = $('<button class="btn btn-success" type="button"></button>')
                .text(DataGridSearchHelper.locale.submit);
            $runFilteringBtn.on('click', function () {
                // clean empty filters
                $builder.find('.rule-container').each(function () {
                    var model = $builder.queryBuilder('getModel', $(this));
                    if (model && !model.filter) {
                        model.drop();
                    }
                });
                // clean empty filter groups
                $builder.find('.rules-group-container').each(function () {
                    var group = $builder.queryBuilder('getModel', $(this));
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
                if ($builder.queryBuilder('validate')) {
                    var rules = $builder.queryBuilder('getRules');
                    var encoded;
                    if (!rules.rules) {
                        // empty rules set
                        $builder.queryBuilder('reset');
                        encoded = DataGridSearchHelper.encodeRulesForDataTable(DataGridSearchHelper.emptyRules)
                    } else if ($builder.queryBuilder('validate')) {
                        encoded = DataGridSearchHelper.encodeRulesForDataTable(rules);
                    }
                    tableApi.search(encoded).draw();
                }
            });
            var $resetFilteringBtn = $('<button class="btn btn-danger" type="button"></button>')
                .text(DataGridSearchHelper.locale.reset);
            $resetFilteringBtn.on('click', function () {
                $builder.queryBuilder('reset');
                if (defaultRules) {
                    $builder.queryBuilder('setRules', defaultRules);
                }
            });
            var $toolbar = $builder
                .closest('.dataTables_wrapper')
                .find('.filter-toolbar');
            if (!config.is_opened) {
                var $counterBadge = $('<span class="counter label label-success ml10"></span>');
                var $filterToggleButton = $('<button class="btn btn-default" type="button"></button>')
                    .text(DataGridSearchHelper.locale.toggle)
                    .append($counterBadge)
                    .attr('data-toggle','collapse')
                    .attr('data-target', '#' + $builder[0].id);
                var changeCountInBadge = function () {
                    var rules = $builder.queryBuilder('getRules');
                    var count = DataGridSearchHelper.countRules(rules);
                    if (count) {
                        $counterBadge.text(count).show();
                    } else {
                        $counterBadge.hide();
                    }
                };
                changeCountInBadge();
                $runFilteringBtn.on('click', function () {
                    changeCountInBadge();
                });
                $runFilteringBtn.hide();
                $resetFilteringBtn.hide();
                $filterToggleButton.on('click', function () {
                    $filterToggleButton.toggleClass('active');
                    if ($filterToggleButton.hasClass('active')) {
                        $runFilteringBtn.show();
                        $resetFilteringBtn.show();
                    } else {
                        $runFilteringBtn.hide();
                        $resetFilteringBtn.hide();
                    }
                });
                $builder.addClass('collapse');
                $toolbar.append($filterToggleButton);
            }
            $toolbar
                .append($runFilteringBtn)
                .append($resetFilteringBtn);
        }
    },
    encodeRulesForDataTable: function (rules, asObject) {
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
        var ret = {
            condition: rules.c,
            rules: []
        };
        for (var i = 0; i < rules.r.length; i++) {
            var rule = rules.r[i];
            if (rule.c) {
                ret.rules.push(DataGridSearchHelper.decodeRulesForDataTable(rule, fieldNameTofilterIdMap));
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
        Utils.showPreloader(CmfControllerHelpers.currentContentContainer);
        var timeout = setTimeout(function () {
            Utils.hidePreloader(CmfControllerHelpers.currentContentContainer);
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
                Utils.hidePreloader(CmfControllerHelpers.currentContentContainer);
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
        if (!CKEDITOR.plugins.get(pluginName)) {
            var comboboxPanelCss = 'body{font-family:Arial,sans-serif;font-size:14px;}';
            var locale = CmfConfig.getLocalizationStringsForComponent('ckeditor');
            var insertTpl = doT.template(
                '<{{= it.tag }} title="{{! it.title }}" class="wysiwyg-data-insert">{{= it.code }}</{{= it.tag }}>'
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
                                var insertInfo = editor.config.data_inserts[i];
                                insertInfo.tag = insertInfo.is_block ? 'div' : 'span';
                                combobox.add(insertTpl(insertInfo), insertInfo.title, insertInfo.title);
                                combobox._.committed = 0;
                                combobox.commit(); //< ty good people of web!!!
                            }
                        },
                        onClick: function (value) {
                            editor.focus();
                            editor.fire('saveSnapshot');
                            editor.insertHtml(value);
                            editor.fire('saveSnapshot');
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
    }
};