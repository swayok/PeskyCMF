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
            this.$el.find('select[data-value!=""]').each(function () {
                $(this).val(this.getAttribute('data-value'));
            });
            var form = this.$el.find('form');
            FormHelper.initForm(form, form, function (json, form, container) {
                if (json._message) {
                    toastr.success(json._message);
                }
                window.adminApp.back(form.attr('data-back-url'));
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
                ScaffoldActionsHelper.handleRequestAction($el)
                    .done(ScaffoldActionsHelper.onSuccess)
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
                var defaultUrl = $el.attr('data-url') || GlobalVars.rootUrl;
                ScaffoldsManager.app.back(defaultUrl);
                break;
        }
    },
    handleRequestAction: function ($el) {
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
        var data = $el.attr('data-data') || '';
        var method = $el.attr('data-method') || 'get';
        var baseMethod;
        if (!$.inArray(method.toLowerCase(), ['post', 'put', 'delete'])) {
            baseMethod = 'GET';
        } else {
            baseMethod = 'POST';
            if (method.toLowerCase() !== 'post') {
                data += (data.length ? '&' : '') + '_method=' + method.toUpperCase();
            }
        }
        return $.ajax({
                url: url,
                data: data,
                method: baseMethod,
                cache: false,
                dataType: 'json'
            })
            .done(function (json) {
                if ($el.attr('data-on-success')) {
                    eval($el.attr('data-on-success'));
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
                    if (GlobalVars.isDebug) {
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
                    if (GlobalVars.isDebug) {
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
                {language: GlobalVars.getLocalizationStringsForComponent('data_tables')},
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
                    ScaffoldDataGridHelper.initClickEvents($tableWrapper, $table);
                    ScaffoldDataGridHelper.initRowActions($table);
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
            .html(GlobalVars.getLocalizationStringsForComponent('data_tables').toolbar.reloadData);
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
    initClickEvents: function ($tableWrapper, $table) {
        $tableWrapper.on('click tap', '[data-action]', function (event) {
            var $el = $(this);
            var action = String($el.attr('data-action')).toLowerCase();
            switch (action) {
                case 'reload':
                    $table.dataTable().api().ajax.reload();
                    break;
                case 'request':
                    if ($el.attr('data-block-datagrid')) {
                        Utils.showPreloader($tableWrapper);
                    }
                    ScaffoldActionsHelper.handleRequestAction($el)
                        .done(function (json) {
                            if (
                                json.redirect
                                && (
                                    json.redirect === 'back'
                                    || json.redirect === 'reload'
                                    || json.redirect === ScaffoldsManager.app.activeRequest.href
                                    || json.redirect === ScaffoldsManager.app.activeRequest.path
                                )
                            ) {
                                $table.dataTable().api().ajax.reload();
                                delete json.redirect;
                            }
                            ScaffoldActionsHelper.onSuccess(json);
                        })
                        .always(function () {
                            if ($el.attr('data-block-datagrid')) {
                                Utils.hidePreloader($tableWrapper);
                            }
                        });
                    break;
            }
            return false;
        });
        var configs = $table.data('configs');
        if (configs && configs.doubleClickUrl) {
            $table.on('dblclick dbltap', 'tbody tr', function (event) {
                ScaffoldsManager.app.nav(configs.doubleClickUrl($table.dataTable().api().row($(this)).data()));
                return false;
            });
        }
    },
    initRowActions: function ($table) {
        var configs = $table.data('configs');
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
    }
};

var DataGridSearchHelper = {
    container: '#query-builder',
    defaultConfig: {
        plugins: ['bt-tooltip-errors', 'bt-checkbox'],
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
    }
};