var ScaffoldsManager = {
    app: null,
    cacheTemplates: true
};

ScaffoldsManager.init = function (app) {
    ScaffoldsManager.app = app;

    ScaffoldsManager.app
        .route('resource_datagrid', '/resource/:resource', ScaffoldControllers.dataGrid)
        .route('resource_details', '/resource/:resource/details/:id', ScaffoldControllers.itemDetails)
        .route('/resource/:resource/list', function (event, request) {
            setTimeout(function () {
                // timeout required to make previous route change end before this one started
                ScaffoldsManager.app.nav(CmfConfig.rootUrl + '/resource/' + request.params.resource);
            }, 50);
        })
        .route('resource_create', '/resource/:resource/create', ScaffoldControllers.itemForm)
        .route('resource_edit', '/resource/:resource/edit/:id', ScaffoldControllers.itemForm)
        .route('resource_page', '/resource/:resource/:id/page/:page', ScaffoldControllers.itemCustomPage)
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