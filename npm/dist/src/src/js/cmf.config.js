var CmfConfig = {
    isDebug: false,
    enablePing: false,      //< enables ajax requests to server in order to track session or csrf token timeouts
    pingInterval: 20000,
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
    },
    getLocalizationString: function (path, defaultValue) {
        if (!path || typeof path !== 'string') {
            return 'Invalid localization path. String expected.';
        }
        var parts = path.split('.');
        var pointer = CmfCache.localization;
        for (var i = 0; i < parts.length; i++) {
            if ($.isPlainObject(pointer) && pointer.hasOwnProperty(parts[i])) {
                pointer = pointer[parts[i]];
            } else {
                return defaultValue || path;
            }
        }
        return pointer;
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