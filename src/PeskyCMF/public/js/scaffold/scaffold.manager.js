var ScaffoldsManager = {
    app: null,
    cacheTemplates: true
};

ScaffoldsManager.init = function (app) {
    ScaffoldsManager.app = app;

    ScaffoldsManager.app
        .route('/resource/:resource', ScaffoldControllers.dataGrid)
        .route('/resource/:resource/details/:id', ScaffoldControllers.itemDetails)
        .route('/resource/:resource/list', function (event, request) {
            setTimeout(function () {
                // timeout required to make previous route change end before this one started
                ScaffoldsManager.app.nav(GlobalVars.rootUrl + '/resource/' + request.params.resource);
            }, 50);
        })
        .route('/resource/:resource/create', ScaffoldControllers.itemForm)
        .route('/resource/:resource/edit/:id', ScaffoldControllers.itemForm)
};

ScaffoldsManager.getResourceBaseUrl = function (resourceName) {
    return GlobalVars.rootUrl + '/' + GlobalVars.scaffoldApiUrlSection + '/' + resourceName
};

ScaffoldsManager.isValidResourceName = function (resourceName) {
    return typeof resourceName == 'string' && String(resourceName).match(/^[a-zA-Z_][a-zA-Z_0-9]+$/);
};

ScaffoldsManager.validateResourceName = function (resourceName) {
    if (!ScaffoldsManager.isValidResourceName(resourceName)) {
        console.trace();
        throw 'Invalid REST resource name: ' + resourceName;
    }
};

ScaffoldsManager.findResourceNameInUrl = function (url) {
    var matches = url.match(/\/resource\/([^\/]+)/i);
    return !matches ? false : matches[0];
};

/* ============ Templates ============ */

$.extend(Cache, {
    rawTemplates: {},
    compiledTemplates: {
        itemForm: {},
        bulkEditForm: {},
        itemDetails: {}
    }
});

ScaffoldsManager.loadTemplates = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    var deferred = $.Deferred();
    if (!ScaffoldsManager.cacheTemplates || !ScaffoldsManager.isTemplatesLoaded(resourceName)) {
        var resourceUrl = ScaffoldsManager.getResourceBaseUrl(resourceName);
        $.ajax({
            url: resourceUrl + '/service/templates',
            method: 'GET',
            cache: false,
            type: 'html'
        }).done(function (html) {
            ScaffoldsManager.setResourceTemplates(resourceName, html);
            deferred.resolve(resourceName);
        }).fail(Utils.handleAjaxError);
    } else {
        deferred.resolve(resourceName);
    }
    return deferred;
};

ScaffoldsManager.setResourceTemplates = function (resourceName, html) {
    ScaffoldsManager.validateResourceName(resourceName);
    var templates = $('<div id="templates">' + html + '</div>');
    Cache.rawTemplates[resourceName] = {
        datagrid: false,
        itemForm: false,
        bulkEditForm: false,
        itemdetails: false
    };
    var dataGridTpl = templates.find('#data-grid-tpl');
    if (dataGridTpl.length) {
        Cache.rawTemplates[resourceName].datagrid = dataGridTpl.html();
    }
    var itemFormTpl = templates.find('#item-form-tpl');
    if (itemFormTpl.length) {
        Cache.rawTemplates[resourceName].itemForm = itemFormTpl.html();
    }
    var bulkEditFormTpl = templates.find('#bulk-edit-form-tpl');
    if (bulkEditFormTpl.length) {
        Cache.rawTemplates[resourceName].bulkEditForm = bulkEditFormTpl.html();
    }
    var itemDetailsTpl = templates.find('#item-details-tpl');
    if (itemDetailsTpl.length) {
        Cache.rawTemplates[resourceName].itemDetails = itemDetailsTpl.html();
    }
};

ScaffoldsManager.isTemplatesLoaded = function (resourceName) {
    return !!Cache.rawTemplates[resourceName];
};

ScaffoldsManager.hasDataGridTemplate = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    return ScaffoldsManager.isTemplatesLoaded(resourceName) && Cache.rawTemplates[resourceName].datagrid;
};

ScaffoldsManager.hasItemFormTemplate = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    return ScaffoldsManager.isTemplatesLoaded(resourceName) && Cache.rawTemplates[resourceName].itemForm;
};

ScaffoldsManager.hasBulkEditFormTemplate = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    return ScaffoldsManager.isTemplatesLoaded(resourceName) && Cache.rawTemplates[resourceName].bulkEditForm;
};

ScaffoldsManager.hasItemDetailsTemplate = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    return ScaffoldsManager.isTemplatesLoaded(resourceName) && Cache.rawTemplates[resourceName].itemDetails;
};

ScaffoldsManager.getDataGridTpl = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName).done(function () {
        if (!ScaffoldsManager.hasDataGridTemplate(resourceName)) {
            throw 'There is no data grid template for resource [' + resourceName + ']';
        }
        deferred.resolve(Cache.rawTemplates[resourceName].datagrid);
    });
    return deferred;
};

ScaffoldsManager.getItemFormTpl = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName).done(function () {
        ScaffoldsManager.validateResourceName(resourceName);
        if (!ScaffoldsManager.hasItemFormTemplate(resourceName)) {
            throw 'There is no item form template for resource [' + resourceName + ']';
        }
        if (!ScaffoldsManager.cacheTemplates || !Cache.compiledTemplates.itemForm[resourceName]) {
            Cache.compiledTemplates.itemForm[resourceName] = Utils.makeTemplateFromText(
                Cache.rawTemplates[resourceName].itemForm,
                'Item form template for ' + resourceName
            );
        }
        deferred.resolve(Cache.compiledTemplates.itemForm[resourceName]);
    });
    return deferred;
};

ScaffoldsManager.getBulkEditFormTpl = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName).done(function () {
        ScaffoldsManager.validateResourceName(resourceName);
        if (!ScaffoldsManager.hasBulkEditFormTemplate(resourceName)) {
            throw 'There is no bulk edit form template for resource [' + resourceName + ']';
        }
        if (!Cache.compiledTemplates.bulkEditForm[resourceName]) {
            Cache.compiledTemplates.bulkEditForm[resourceName] = Utils.makeTemplateFromText(
                Cache.rawTemplates[resourceName].bulkEditForm,
                'Bulk edit form template for ' + resourceName
            );
        }
        deferred.resolve(Cache.compiledTemplates.bulkEditForm[resourceName]);
    });
    return deferred;
};

ScaffoldsManager.getItemDetailsTpl = function (resourceName) {
    ScaffoldsManager.validateResourceName(resourceName);
    var deferred = $.Deferred();
    ScaffoldsManager.loadTemplates(resourceName).done(function () {
        ScaffoldsManager.validateResourceName(resourceName);
        if (!ScaffoldsManager.hasItemDetailsTemplate(resourceName)) {
            throw 'There is no item details template for resource [' + resourceName + ']';
        }
        if (!Cache.compiledTemplates.itemDetails[resourceName]) {
            Cache.compiledTemplates.itemDetails[resourceName] = Utils.makeTemplateFromText(
                Cache.rawTemplates[resourceName].itemDetails,
                'Item details template for ' + resourceName
            );
        }
        deferred.resolve(Cache.compiledTemplates.itemDetails[resourceName]);
    });
    return deferred;
};
