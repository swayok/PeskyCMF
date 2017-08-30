var CmfConfig = {
    isDebug: false,
    debugDialog: null,
    defaultPageTitle: '',
    pageTitleAddition: '',
    rootUrl: '/',
    scaffoldApiUrlSection: 'api',
    uiUrl: null,                //< absolute URL or relative URL that contains CmfConfig.rootUrl
    userDataUrl: null,          //< absolute URL or relative URL that contains CmfConfig.rootUrl
    userDataCacheTimeoutMs: 30000,
    enableMenuCounters: true,
    menuCountersDataUrl: null,  //< absolute URL or relative URL that contains CmfConfig.rootUrl
    menuCountersUpdateIntervalMs: 30000,
    disableMenuCountersIfEmptyOrInvalidDataReceived: true,
    contentChangeAnimationDurationMs: 300,
    contentWrapperCssClass: 'section-content-wrapper',
    toastrOptions: {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "500",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "slideDown",
        "hideMethod": "slideUp"
    },
    setLocalizationStrings: function (data) {
        CmfCache.localization = data;
    },
    getLocalizationStringsForComponent: function (componentName) {
        return (componentName && !!CmfCache.localization[componentName]) ? CmfCache.localization[componentName] : {};
    }
};

/**
 * @return DebugDialog
 */
CmfConfig.getDebugDialog = function () {
    if (CmfConfig.debugDialog === null) {
        if (CmfConfig.isDebug && typeof DebugDialog === 'function') {
            CmfConfig.debugDialog = new DebugDialog();
        } else {
            CmfConfig.debugDialog = {
                showDebug: function (title, content) {
                    toastr.error(title);
                },
                toggleVisibility: function () {}
            }
        }
    }
    return CmfConfig.debugDialog;
};

var CmfCache = {
    localization: {},
    views: {},
    user: null,
    userLastUpdateMs: 0
};