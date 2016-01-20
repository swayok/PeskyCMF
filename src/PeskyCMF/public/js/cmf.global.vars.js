var GlobalVars = {
    isDebug: true,
    rootUrl: '/',
    scaffoldApiUrlSection: 'api',
    uiUrl: null,
    userDataUrl: null,
    userDataCacheTimeoutMs: 30000,
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
        Cache.localization = data;
    },
    getLocalizationStringsForComponent: function (componentName) {
        return (componentName && !!Cache.localization[componentName]) ? Cache.localization[componentName] : {};
    }
};

var Cache = {
    localization: {},
    views: {},
    user: null,
    userLastUpdateMs: 0
};