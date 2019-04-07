var CmfFileUploads = {
    baseUploaderOptions: {
        language: $(document.body).attr('data-locale'),
        validateInitialCount: true,
        showUpload: false,
        allowedFileTypes: ['image', 'text', 'video', 'audio', 'object'],
        allowedPreviewTypes: ['image', 'video', 'audio'],
        previewFileType: 'any',
        previewFileIcon: '<i class="fa fa-file"></i>',
        initialPreviewAsData: true,
        overwriteInitial: true,
        fileActionSettings: {
            showDrag: false,
            showDownload: true
        },
        browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
        layoutTemplates: {
            main1: "{preview}\n" +
                "<div class=\'input-group {class}\'>\n" +
                "   <div class=\'input-group-btn\'>\n" +
                "       {browse}\n" +
                "       {upload}\n" +
                "       {remove}\n" +
                "   </div>\n" +
                "   {caption}\n" +
                "</div>"
        }
    },
    imageUploaderOptions: {
        allowedFileTypes: ['image'],
        allowedPreviewTypes: ['image'],
        previewFileType: 'image',
        initialPreviewFileType: 'image'
    },
    fileUploaderOptions: {
        fileActionSettings: {
            showDrag: false,
            showDownload: true,
            showBrowse: false
        },
        initialPreviewFileType: 'object',
        previewFileIconSettings: {
            'doc': '<i class="fa fa-file-word-o text-primary"></i>',
            'docx': '<i class="fa fa-file-word-o text-primary"></i>',
            'txt': '<i class="fa fa-file-text-o"></i>',
            'json': '<i class="fa fa-file-text-o"></i>',
            'js': '<i class="fa fa-file-text-o"></i>',
            'rtf': '<i class="fa fa-file-text-o"></i>',
            'xls': '<i class="fa fa-file-excel-o text-success"></i>',
            'xlsx': '<i class="fa fa-file-excel-o text-success"></i>',
            'csv': '<i class="fa fa-file-excel-o text-success"></i>',
            'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pptx': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
            'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'gzip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'rar': '<i class="fa fa-file-archive-o text-muted"></i>'
        }
    }
};

CmfFileUploads.initFileUploaders = function (data, isImages, additionalPluginOptions) {
    for (var filesGroupName in data.configs) {
        CmfFileUploads.initFileUploader(data, filesGroupName, isImages, additionalPluginOptions);
    }
};

CmfFileUploads.initFileUploader = function (data, filesGroupName, isImages, additionalPluginOptions) {
    var fileConfig = data.configs[filesGroupName];
    var isSingleFile = fileConfig.max_files_count === 1;
    fileConfig.defaultPluginOptions = $.extend(
        {layoutTemplates: {}},
        CmfFileUploads.baseUploaderOptions,
        isImages ? CmfFileUploads.imageUploaderOptions : CmfFileUploads.fileUploaderOptions,
        ($.isPlainObject(additionalPluginOptions) ? additionalPluginOptions : {}),
        {
            allowedFileExtensions: fileConfig.allowed_extensions,
            minFileCount: 0,
            maxFileCount: 1,
            maxFileSize: fileConfig.max_file_size,
            showCaption: isSingleFile,
            previewClass: (isSingleFile ? 'single-file-upload' : 'multi-file-upload')
        }
    );
    if (!isSingleFile) {
        fileConfig.defaultPluginOptions.layoutTemplates.main2 =
            '<button class="fileinput-dragger" type="button"><span class="fa fa-arrows"></span></button>' +
            '{preview}\n<div class="kv-upload-progress kv-hidden"></div>\n' +
            '<div class="clearfix"></div>\n' +
            '<div class="kv-upload-toolbar text-center">{remove}\n{cancel}\n{upload}\n{browse}\n</div>';

        fileConfig.defaultPluginOptions.layoutTemplates.preview =
            '<div class="file-preview {class}">\n' +
            '    {close}' +
            '    <div class="no-file">' + CmfConfig.getLocalizationStringsForComponent('file_uploader').no_file + '</div>' +
            '    <div class="{dropClass}">\n' +
            '    <div class="file-preview-thumbnails">\n' +
            '    </div>\n' +
            '    <div class="clearfix"></div>' +
            '    <div class="file-preview-status text-center text-success"></div>\n' +
            '    <div class="kv-fileinput-error"></div>\n' +
            '    </div>\n' +
            '</div>';
    }
    fileConfig.inputsAdded = 0;
    fileConfig.isCloning = !!data.is_cloning;
    fileConfig.isInModal = !!data.is_in_modal;

    Utils.makeTemplateFromText(
            $('#' + fileConfig.id + '-tpl').html(),
            'CmfFileUploads.initFileUploader for files group ' + filesGroupName
        )
        .done(function (inputTemplate) {
            fileConfig.inputTpl = inputTemplate;
            fileConfig.addInput = function (pluginOptions, existingFileData) {
                return CmfFileUploads.initFileUploaderInput(fileConfig, pluginOptions, existingFileData);
            };
            $('#' + fileConfig.id + '-add')
                .on('click', function () {
                    fileConfig.addInput();
                    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
                        $(this).remove();
                    }
                    return false;
                });
            // show existing files
            if (
                data.files
                && $.isPlainObject(data.files)
                && data.files.urls
                && data.files.urls[filesGroupName]
                && data.files.preview_info
                && data.files.preview_info[filesGroupName]
                && data.files.files
                && data.files.files[filesGroupName]
            ) {
                var existingFilesUrls = data.files.urls[filesGroupName];
                var existingFilesPreviewsInfo = data.files.preview_info[filesGroupName];
                var existingFiles = data.files.files[filesGroupName];
                if ($.isArray(existingFilesUrls) && $.isArray(existingFilesPreviewsInfo) && $.isArray(existingFiles)) {
                    for (var i = 0; i < existingFilesUrls.length; i++) {
                        fileConfig.addInput(
                            {
                                initialPreview: [existingFilesUrls[i]],
                                initialPreviewConfig: [existingFilesPreviewsInfo[i]],
                                initialCaption: [existingFilesPreviewsInfo[i].caption]
                            },
                            $.extend(
                                {is_cloning: fileConfig.isCloning},
                                existingFiles[i]
                            )
                        )
                    }
                }
            }
            // add empty inputs
            if (fileConfig.inputsAdded === 0 && (isSingleFile || fileConfig.min_files_count > 0)) {
                // add at least 1 input
                fileConfig.addInput();
            }
            // add required amount of inputs
            for (var k = fileConfig.inputsAdded; k < fileConfig.min_files_count; k++) {
                fileConfig.addInput();
            }
            if (!isSingleFile) {
                var container = $('#' + fileConfig.id + '-container');
                container.data('sortable', Sortable.create(
                    container[0],
                    {
                        handle: '.fileinput-dragger',
                        draggable: '.file-upload-input-container',
                        animation: 200,
                        forceFallback: true,
                        onUpdate: function (event) {
                            $(event.to).find('.file-upload-input-container').each(function (index, item) {
                                $(item).find('input[name$="][position]"]').val(String(index + 1));
                                $(item).find('input[name]').each(function (_, input) {
                                    input.name = String(input.name).replace(/\]\[[0-9]\]\[/, '][' + String(index) + '][');
                                })
                            });
                        }
                    }
                ));
            }
        });
};

CmfFileUploads.initFileUploaderInput = function (fileConfig, pluginOptions, existingFileData) {
    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
        return false;
    }
    if (!$.isPlainObject(existingFileData)) {
        existingFileData = {};
    }
    existingFileData = $.extend({index: fileConfig.inputsAdded}, existingFileData);
    var $renderedTemplate = $(fileConfig.inputTpl(existingFileData));
    $('#' + fileConfig.id + '-container').append($renderedTemplate);
    fileConfig.inputsAdded++;
    var $fileInput = $renderedTemplate.find('input[type="file"]');
    var options = $.extend(
        {},
        fileConfig.defaultPluginOptions,
        $.isPlainObject(pluginOptions) ? pluginOptions : {}
    );

    if (existingFileData.is_cloning && existingFileData.url) {
        var xhr = new XMLHttpRequest();
        xhr.onload = function(){
            var reader = new FileReader();
            reader.onloadend = function() {
                $('#' + $fileInput[0].id + '-file-data').val(JSON.stringify($.extend({data: reader.result}, existingFileData)));
            };
            reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', existingFileData.url);
        xhr.responseType = 'blob';
        xhr.send();
    }
    $fileInput
        .fileinput(options)
        .on('fileclear', function() {
            $('#' + this.id + '-deleted').val('1');
            $('#' + this.id + '-file-data').remove();
            var sortable = $('#' + fileConfig.id + '-container').data('sortable');
            if (sortable) {
                var order = sortable.toArray();
                var index = $.inArray(this.id, order);
                if (index >= 0) {
                    order.splice(index, 1);
                    order.push(this.id);
                    sortable.sort(order);
                }
            }
        })
        .on('change', function () {
            $('#' + this.id + '-file-data').remove();
        })
        .on('filezoomhidden', function (event, params) {
            params.modal.remove();
            if (fileConfig.isInModal) {
                $('body').addClass('modal-open');
                $('.modal.in').css('padding-left', '17px');
            }
        });
};
(function(global) {

'use strict';

global.page = page;

page.isSameOrigin = isSameOrigin;
page.Request = Request;
page.Route = Route;
page.pathToRegexp = pathToRegexp;
page.queryString = queryString;
page.Deferred = Deferred;

/**
 * Detect click event
 */
var clickEvent = ('undefined' !== typeof document) && document.ontouchstart ? 'touchstart' : 'click';

/**
 * To work properly with the URL
 * history.location generated polyfill in https://github.com/devote/HTML5-History-API
 */
var location = ('undefined' !== typeof window) && (window.history.location || window.location);

var isArray = isArray || function (arr) {
    return Object.prototype.toString.call(arr) === '[object Array]';
};

var normalizeArguments = function(args) {
    if (!args) {
        return [];
    } else if (typeof args === 'object' && args.length) {
        return isArray(args) ? args : (args.length === 1 ? [args[0]] : Array.apply(null, args));
    } else {
        return [args];
    }
};

/**
 * Decode URL components (query string, pathname, hash).
 * Accommodates both regular percent encoding and x-www-form-urlencoded format.
 */
var decodeURLComponents = true;

/**
 * Decode URL query string into object for every request
 * @type {boolean}
 */
var decodeURLQuery = false;

/**
 * Base path.
 */
var base = '';

/**
 * Running flag.
 */
var running;

/**
 * Previous request, for capturing
 * page exit events.
 * @type {Request}
 */
var previousRequest;

/**
 * Current request
 * @type {Request}
 */
var currentRequest = {
    promise: Deferred().resolve().promise()
};

/**
 * Amount of Request objects created. Used to identify requests via Request.id() or Request.isSameAs(otherRequest)
 * @type {number}
 */
var requestsCreated = 0;

/**
 * Amount of push states
 * @type {number}
 */
var navs = 0;

/**
 * Shortcut for `page.start(options)`.
 * @param {!Object} options
 * @api public
 */
function page(options) {
    page.start(options);
}

/**
 * Callback functions.
 */

var callbacks = [];
var exits = [];
var errorHandlers = [];
var routeNotFoundHandlers = [];

/**
 * Returns current request
 * @return {Request}
 */
page.currentRequest = function () {
    return currentRequest;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.currentUrlWithoutBase = function () {
    return page.currentRequest().path;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.currentUrl = function () {
    return page.currentRequest().fullUrl(true);
};

/**
 * Returns previous request
 * @return {Request}
 */
page.previousRequest = function () {
    return previousRequest;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.previousUrlWithoutBase = function () {
    return page.previousRequest().path;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.previousUrl = function () {
    return page.previousRequest().fullUrl(true);
};

/**
 * Get or set basepath to `path`.
 *
 * @param {string} path
 * @api public
 */
page.base = function (path) {
    if (0 === arguments.length) {
        return base;
    }
    base = path;
};

/**
 * Bind with the given `options`.
 *
 * Options:
 *
 *    - `click` - bool, bind to click events [true]
 *    - `popstate` - bool, bind to popstate [true]
 *    - `dispatch` - bool, perform initial dispatch [true]
 *    - `decodeURLComponents` - bool, remove URL encoding from URL components [true]
 *    - `decodeURLQuery` - bool, convert query string to object for each request request [false]
 *
 * @param {Object} options
 * @api public
 */
page.start = function (options) {
    options = options || {};
    if (running) {
        return;
    }
    running = true;
    navs = 0;
    if (options.decodeURLComponents === false) {
        decodeURLComponents = false;
    }
    if (options.decodeURLQuery) {
        decodeURLQuery = true;
    }
    if (options.popstate !== false) {
        window.addEventListener('popstate', onpopstate, false);
    }
    if (options.click !== false) {
        document.addEventListener(clickEvent, onclick, false);
    }
    page.show(
        getDocumentUrl(true),
        undefined,
        options.dispatch !== false,
        true,
        {env: {is_first: true}}
    );
};

/**
 * Unbind click and popstate event handlers.
 *
 * @api public
 */
page.stop = function () {
    if (!running) {
        return;
    }
    currentRequest = null;
    previousRequest = null;
    running = false;
    document.removeEventListener(clickEvent, onclick, false);
    window.removeEventListener('popstate', onpopstate, false);
};

/**
 * Declare route.
 * Note: You can pass many fn
 *
 * @param {string} path
 * @param {Function=} fn1
 * @param {Function=} fn2
 * @param {Function=} fnx
 * @api public
 */
page.route = function (path, fn1, fn2, fnx) {
    if (typeof path !== 'string') {
        throw new TypeError('1st argument passed to page.route() must be a string. Use \'*\' if you want to apply callbacks to all routes');
    }
    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        if (typeof arguments[i] !== 'function') {
            throw new TypeError('argument ' + (i + 1) + ' passed to page.route() for route ' + path + ' is not a funciton');
        }
        callbacks.push(route.middleware(arguments[i], 'route_handler'));
    }
};

/**
 * Register an error handler on `path` with callback `fn()`,
 * which will be called on error during matching request.path
 * @param {string} path
 * @param {Function=} fn
 * @api public
 */
page.error = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        errorHandlers.push(route.middleware(arguments[i], 'error_handler'));
    }
};

/**
 * Register a page not found handler on `path` with callback `fn()`,
 * which will be called if there is no route that matches request.path
 * but matches provided `path`
 * @param {string} path
 * @param {Function=} fn
 * @api public
 */
page.notFound = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        routeNotFoundHandlers.push(route.middleware(arguments[i], 'page_not_found_handler'));
    }
};

/**
 * Register an exit route on `path` with callback `fn()`,
 * which will be called on the previous request when a new
 * page is visited.
 */
page.exit = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; ++i) {
        exits.push(route.middleware(arguments[i], 'exit'));
    }
};

function checkIfStarted() {
    if (!running) {
        throw new Error('Attempt to navigate before router is started');
    }
}

function getDocumentUrl(withHashbang) {
    return window.location.pathname + window.location.search + (withHashbang ? window.location.hash : '');
}

/**
 * Show `path` with optional `state` object.
 *
 * @param {string} path
 * @param {Object=} state
 * @param {boolean=} dispatch
 * @param {boolean=} push - not used anymore
 * @param {Object=} customData
 * @return {!Request}
 * @api public
 */
page.show = function (path, state, dispatch, push, customData) {
    checkIfStarted();
    var request = new Request(path, state, customData);
    processRequest(request, dispatch/*, push*/);
    return request;
};

/**
 * Goes back in the history
 * Back should always let the current route push state and then go back.
 *
 * @param {string} fallbackPath - fallback path to go back if no more history exists, if undefined defaults to page.base
 * @param {Object=} state
 * @api public
 */
page.back = function (fallbackPath, state) {
    checkIfStarted();
    if (navs > 1 && window.history.length > 0) {
        // navs > 1 conditions is required to prevent unexpected backs in case when router was just launched and there
        // is no information if previous URL has same domain and base path.
        currentRequest.promise.always(function () {
            navs--;
            window.history.back();
        });
    } else if (fallbackPath) {
        page.show(fallbackPath, state, true, true);
    } else {
        page.show(base, state, true, true);
    }
};

/**
 * Reload current page
 *
 * @api public
 */
page.reload = function () {
    checkIfStarted();
    if (!currentRequest || !currentRequest.path) {
        throw new Error('Attempt to reload page before router has dispatched at least one page')
    }
    page.show(document.location.href, undefined, true, true, {env: {is_reload: true}});
};

/**
 * Replace current request by new one using `path` and optional `state` object.
 *
 * @param {string} path
 * @param {Object=} state
 * @param {boolean=} dispatch
 * @param {Object=} customData
 * @return {Request}
 * @api public
 */
page.replace = function (path, state, dispatch, customData) {
    var request = new Request(path, state, customData);
    //request.push = false; //< it does not change url
    currentRequest.promise.always(function () {
        request.saveState();
    });
    if (dispatch !== false) {
        request.dispatch();
    }
    return request;
};

/**
 * Restore request.
 *
 * @param {Request=} request
 * @param {boolean=} dispatch
 * //@param {boolean=} push
 * @api public
 */
page.restoreRequest = function (request, dispatch/*, push*/) {
    if (request.customData) {
        request.customData.env = {};
    }
    request.customData.env.is_restore = true;
    processRequest(request, dispatch/*, push*/);
};

/**
 * Execute request.
 *
 * @param {Request=} request
 * @param {boolean=} dispatch
 * //@param {boolean=} push
 * @api private
 */
function processRequest(request, dispatch/*, push*/) {
    // if (push === false) {
    //     request.push = false;
    // }
    if (dispatch !== false) {
        request.dispatch();
    } else /*if (request.push)*/ {
        request.pushState();
    }
}

/**
 * Remove URL encoding from the given `str`.
 * Accommodates whitespace in both x-www-form-urlencoded
 * and regular percent-encoded form.
 *
 * @param {string} val - URL component to decode
 */
function decodeURLEncodedURIComponent(val) {
    if (typeof val !== 'string') {
        return val;
    }
    return decodeURLComponents ? decodeURIComponent(val.replace(/\+/g, ' ')) : val;
}

/**
 * Default handler for requests that do not have matching routes
 * Called only when there is no matching route not found handlers provided via page.notFound(path, fn)
 *
 * @param {Request} request
 */
function routeNotFound(request) {
    var current = getDocumentUrl(true);
    if (current !== request.originalPath && current !== request.fullUrl()) {
        document.location = request.originalPath;
    }
}

/**
 * Initialize a new "request" `Request`
 * with the given `path` and optional initial `state`.
 *
 * @constructor
 * @param {string} path
 * @param {Object=} state
 * @param {Object=} customData
 * @api public
 */
function Request(path, state, customData) {
    requestsCreated++;
    var id = requestsCreated;

    this.id = function () {
        return id;
    };

    this.originalPath = path;

    if (path.indexOf('http') === 0 && isSameOrigin(path)) {
        // absolute url provided: remove "https?://doma.in" from path
        path = path.replace(/https?:\/\/.+?\//, '/');
    }

    if (path[0] === '/' && path.indexOf(base) !== 0) {
        path = base + path;
    }
    var i = path.indexOf('?');

    this.title = document.title;
    this.state = state || {};
    this.querystring = ~i ? decodeURLEncodedURIComponent(path.slice(i + 1)) : '';
    this.pathname = decodeURLEncodedURIComponent(~i ? path.slice(0, i) : path);
    this.path = this.pathname.replace(base, '') || '/';
    this.params = {};
    this.customData = (customData && (typeof customData === 'object')) ? customData : {};
    if (!this.customData.env || typeof this.customData.env !== 'object') {
        this.customData.env = {};
    }

    this.promise = null;

    //this.push = true;
    this.routeFound = false;
    this.routeNotFoundHandled = false;
    this.error = false;
    this.errorHandled = false;

    this.parentRequest = null;  //< current request is subrequest of this request
    this.subRequest = null;     //< current request has a subrequest

    // fragment
    this.hash = '';
    if (~this.originalPath.indexOf('#')) {
        var parts = this.originalPath.split('#');
        this.hash = decodeURLEncodedURIComponent(parts[1]) || '';
        this.querystring = this.querystring.split('#', 2)[0];
        this.path = this.path.split('#', 2)[0];
        this.pathname = this.pathname.split('#', 2)[0];
        if (this.hash[0] === '!') {
            this.subRequest = new Request(this.hash.slice(1));
            this.subRequest.parentRequest = this;
            this.hash = '';
        }
    }

    if (decodeURLQuery) {
        this.query = queryString(this.querystring);
    }

}

/**
 * Reload page using url and state of this request
 */
Request.prototype.reload = function () {
    page.show(currentRequest.fullUrl(), currentRequest.state, true, true, {env: {is_reload: true}});
};

/**
 * Compare paths and query strings of 2 requests
 * @param {Request} otherRequest
 * @return {boolean}
 */
Request.prototype.hasSamePathAs = function (otherRequest) {
    return typeof otherRequest === 'object' && this.path === otherRequest.path && this.querystring === otherRequest.querystring;
};

/**
 * Make full URL for this request
 *
 * @param {boolean=} withHashbang - false: URL will not include hasbang part [default: true]
 * @return {string}
 */
Request.prototype.fullUrl = function (withHashbang) {
    if (this.isSubRequest()) {
        return this.parentRequest.fullUrl();
    } else {
        var url = base + this.path;
        if (this.querystring.length > 0) {
            url += '?' + this.querystring;
        }
        if (withHashbang !== false) {
            if (this.hasSubRequest()) {
                url += '#!' + this.subRequest.makeUrlToUseItInParentRequest();
            } else if (this.hash.length > 0) {
                url += '#' + this.hash;
            }
        }
        return url;
    }
};

/**
 * Make URL for this request. This URL may be used in hasbang of parent request
 *
 * @return {string}
 */
Request.prototype.makeUrlToUseItInParentRequest = function () {
    var url = base + this.path;
    if (this.querystring.length > 0) {
        url += '?' + this.querystring;
    }
    return url;
};

/**
 * Get requests unique id
 *
 * @return {integer}
 */
Request.prototype.id = function () {
    throw new Error('This method should never be called. Something wrong happened.');
};

/**
 * Compare unique ids of 2 requests
 *
 * @param {Request} otherRequest
 * @return {boolean}
 */
Request.prototype.isSameAs = function (otherRequest) {
    return this.id() === otherRequest.id();
};

/**
 * Get environment-related data for this request
 * @return {object}
 */
Request.prototype.env = function () {
    return this.customData.env || {};
};

/**
 * Push state if current url differs from new one (otherwise - history.replaceState() will be used instead)
 * @param {boolean=} force - true: ignore url equality testing and force history.pushState()
 * @api private
 */
Request.prototype.pushState = function (force) {
    var url = this.fullUrl(true);
    if (force || getDocumentUrl(true) !== url) {
        navs++;
        history.pushState(this.state, this.title, url);
    } else {
        this.saveState();
    }
};

/**
 * Save the request state.
 *
 * @api public
 */
Request.prototype.saveState = function () {
    history.replaceState(this.state, this.title, this.fullUrl(true));
};

/**
 * Convert this request to subrequest.
 * This will modify only hash part of current page's address when calling pushState()
 */
Request.prototype.convertToSubrequest = function () {
    if (!currentRequest.isSameAs(this)) {
        this.parentRequest = currentRequest;
    } else {
        this.parentRequest = previousRequest;
    }
    this.parentRequest.setSubRequest(this);
};

/**
 * Check if request is handled by modal dialog
 */
Request.prototype.isSubRequest = function () {
    return !!this.parentRequest;
};

/**
 * Check if request has subrequest
 */
Request.prototype.hasSubRequest = function () {
    return !!this.subRequest;
};

/**
 * Restore parent request
 * @param dispatch
 */
Request.prototype.restoreParentRequest = function (dispatch) {
    if (this.isSubRequest() && currentRequest.hasSubRequest() && currentRequest.isSameAs(this.parentRequest)) {
        this.parentRequest.removeSubRequest();
        if (dispatch === false) {
            //if (this.parentRequest.push) {
                this.parentRequest.pushState();
            //}
        } else {
            this.parentRequest.customData.env.is_restore = true;
            this.parentRequest.dispatch();
        }
    }
};

/**
 * Set sub request for current request
 * @param request
 */
Request.prototype.setSubRequest = function (request) {
    this.subRequest = request;
    this.parentRequest = null;
    request.removeSubRequest();
};

/**
 * Remove sub request from current request
 */
Request.prototype.removeSubRequest = function () {
    this.subRequest = null;
    //this.push = true;
};
/**
 * Dispatch the given `request`.
 * @return {Deferred.promise}
 * @api public
 */
Request.prototype.dispatch = function () {
    var request = this;
    var deferred = Deferred();
    var currentRequestBackup = currentRequest && currentRequest.backup ? currentRequest.backup() : currentRequest;
    var prevRequestBackup = previousRequest && previousRequest.backup ? previousRequest.backup() : previousRequest;

    // note: currentRequest may potentially be same as request wuth same 'promise' property
    // so to avoid deadlocking we need to store currentRequest.promise into variable before setting new request.promise
    var currentPromise = currentRequest.promise;
    request.promise = deferred.promise();

    currentPromise.always(function () {
        previousRequest = currentRequest;
        currentRequest = request;

        Deferred
            .queue(
                previousRequest && previousRequest.path ? exits : [],
                previousRequest,
                [previousRequest, request]
            )
            .done(function () {
                Deferred
                    .queue(callbacks, request, [request])
                    .done(function () {
                        deferred.resolve();
                    })
                    .fail(function () {
                        deferred.reject.apply(deferred, arguments);
                    });
            })
            .fail(function () {
                deferred.reject.apply(deferred, arguments);
            });

        request.promise
            .done(function () {
                if (request.routeFound) {
                    // if (request.push) {
                        request.pushState();
                    // }
                    delete request.customData.env.is_restore; //< not needed anymore
                    if (request.hasSubRequest()) {
                        var prevRequest = previousRequest;
                        setTimeout(function () {
                            request.subRequest.dispatch().always(function () {
                                previousRequest = prevRequest;
                                currentRequest = request;
                            });
                        }, 300);
                    } else if (request.isSubRequest()) {
                        previousRequest = prevRequestBackup;
                        currentRequest = request.parentRequest;
                    }
                } else {
                    Deferred
                        .queue(routeNotFoundHandlers, request, [request])
                        .done(function () {
                            if (!request.routeNotFoundHandled) {
                                routeNotFound(request);
                                request.routeNotFoundHandled = true;
                            }
                        });
                }
            })
            .fail(function (error) {
                Deferred
                    .queue(errorHandlers, request, [request])
                    .done(function () {
                        if (!request.errorHandled) {
                            request.errorHandled = true;
                            console.error('Error occured while handling a request to ' + request.originalPath);
                            console.groupCollapsed('Error details');
                            console.warn('request', request);
                            console.warn('request error', error);
                            console.groupEnd();
                        }
                    })
                    .fail(function () {
                        console.error('Error occured while handling a request and error' + request.originalPath);
                        console.groupCollapsed('Error details');
                        console.warn('request', request);
                        console.warn('request error', error);
                        console.warn('error handler fail info:', arguments);
                        console.groupEnd();
                        request.errorHandled = true;
                    })
                    .always(function () {
                        // restore previous state of requests
                        currentRequest = currentRequestBackup;
                        previousRequest = prevRequestBackup;
                    });
            });
        });

    return request.promise;
};

/**
 * Clone this Request
 * @return {Request}
 */
Request.prototype.clone = function () {
    return new Request(this.fullUrl(), $.extend(true, {}, this.state), $.extend(true, {}, this.customData));
};

/**
 * Clone this Request preserving promise
 * @return {Request}
 */
Request.prototype.backup = function () {
    var request = this.clone();
    request.promise = this.promise;
    return request;
};

/**
 * Initialize `Route` with the given HTTP `path`,
 * and an array of `callbacks` and `options`.
 *
 * Options:
 *
 *   - `sensitive`    enable case-sensitive routes
 *   - `strict`       enable strict matching for trailing slashes
 *
 * @constructor
 * @param {string} path
 * @param {Object=} options
 * @api private
 */
function Route(path, options) {
    options = options || {};
    this.isWildcard = (path === '*');
    this.path = this.isWildcard ? '(.*)' : path;
    this.method = 'GET';
    this.regexp = pathToRegexp(this.path, this.keys = [], options);
}

/**
 * Return route middleware with
 * the given callback `fn()`.
 *
 * @param {Function} fn
 * @param {string} type - type of function: route_handler, route_not_found_handler, exit, error_handler
 * @return {Function}
 * @api public
 */
Route.prototype.middleware = function (fn, type) {
    var self = this;
    return function (request) {
        var args = arguments;
        return Deferred(function (deferred) {
            if (self.match(request.path, request.params)) {
                // placed here to be able to rollback these values in callbacks
                if (type === 'route_handler' && !self.isWildcard) {
                    request.routeFound = true;
                } else if (type === 'route_not_found_handler') {
                    request.routeNotFoundHandled = true;
                } else if (type === 'error_handler') {
                    request.errorHandled = true;
                }
                try {
                    var ret = fn.apply(request, args);
                    if (typeof ret === 'object' && typeof ret.then === 'function') {
                        ret.then(
                            function () {
                                deferred.resolve();
                            },
                            function () {
                                deferred.reject.apply(deferred, arguments);
                            }
                        );
                    } else {
                        deferred.resolve();
                    }
                } catch (exc) {
                    deferred.reject(exc);
                }
            } else {
                deferred.resolve();
            }
        });
    };
};

/**
 * Check if this route matches `path`, if so
 * populate `params`.
 *
 * @param {string} path
 * @param {Object} params
 * @return {boolean}
 * @api private
 */
Route.prototype.match = function (path, params) {
    var keys = this.keys,
        qsIndex = path.indexOf('?'),
        pathname = ~qsIndex ? path.slice(0, qsIndex) : path,
        m = this.regexp.exec(decodeURIComponent(pathname));

    if (!m) {
        return false;
    }

    for (var i = 1, len = m.length; i < len; ++i) {
        var key = keys[i - 1];
        var val = decodeURLEncodedURIComponent(m[i]);
        if (val !== undefined || !(Object.prototype.hasOwnProperty.call(params, key.name))) {
            params[key.name] = val;
        }
    }

    return true;
};


/**
 * Handle "populate" events.
 */
var onpopstate = (function () {
    var loaded = false;
    if ('undefined' === typeof window) {
        return;
    }
    if (document.readyState === 'complete') {
        loaded = true;
    } else {
        window.addEventListener('load', function () {
            setTimeout(function () {
                loaded = true;
            }, 0);
        });
    }
    return function onpopstate(e) {
        if (!loaded) {
            return;
        }
        page.show(getDocumentUrl(true), e.state || null, true, false, {env: {is_history: true}});
    };
})();

/**
 * Handle "click" events.
 */
function onclick(e) {

    if (which(e) !== 1) {
        return;
    }

    if (e.metaKey || e.ctrlKey || e.shiftKey) {
        return;
    }
    if (e.defaultPrevented) {
        return;
    }


    // ensure link
    // use shadow dom when available
    var el = e.path ? e.path[0] : e.target;
    while (el && el.nodeName !== 'A') {
        el = el.parentNode;
    }
    if (!el || el.nodeName !== 'A') {
        return;
    }


    // Ignore if tag has
    // 1. "download" attribute
    // 2. rel="external" attribute
    if (el.hasAttribute('download') || el.getAttribute('rel') === 'external') {
        return;
    }

    // ensure non-hash for the same path
    var link = el.getAttribute('href');
    if (el.pathname === window.location.pathname && (el.hash || '#' === link)) {
        return;
    }


    // Check for mailto: in the href
    if (link && link.indexOf('mailto:') > -1) {
        return;
    }

    // check target
    if (el.target) {
        return;
    }

    // x-origin
    if (!isSameOrigin(el.href)) {
        return;
    }

    // rebuild path
    var path = el.pathname + el.search + (el.hash || '');

    // same page
    var orig = path;

    if (path.indexOf(base) === 0) {
        path = path.substr(base.length);
    }

    if (base && orig === path) {
        return;
    }

    e.preventDefault();
    page.show(orig, undefined, true, true, {env: {is_click: true, target: this.activeElement || e.target}});
}

/**
 * Event button.
 */
function which(e) {
    e = e || window.event;
    return null === e.which ? e.button : e.which;
}

/**
 * Check if `href` is the same origin.
 */

function isSameOrigin(href) {
    var origin = window.location.protocol + '//' + window.location.hostname;
    if (window.location.port) {
        origin += ':' + window.location.port;
    }
    return (href && (0 === href.indexOf(origin)));
}

/**
 * Normalize the given path string, returning a regular expression.
 *
 * An empty array can be passed in for the keys, which will hold the
 * placeholder key descriptions. For example, using `/user/:id`, `keys` will
 * contain `[{ name: 'id', delimiter: '/', optional: false, repeat: false }]`.
 *
 * @param  {(String|RegExp|Array)} path
 * @param  {Array}                 [keys]
 * @param  {Object}                [options]
 * @return {RegExp}
 */
function pathToRegexp(path, keys, options) {

    /**
     * Compile a string to a template function for the path.
     *
     * @param  {String}   str
     * @return {Function}
     */
    pathToRegexp.compile = compile;

    /**
     * The main path matching regexp utility.
     *
     * @type {RegExp}
     */
    var PATH_REGEXP = new RegExp([
        // Match escaped characters that would otherwise appear in future matches.
        // This allows the user to escape special characters that won't transform.
        '(\\\\.)',
        // Match Express-style parameters and un-named parameters with a prefix
        // and optional suffixes. Matches appear as:
        //
        // "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?", undefined]
        // "/route(\\d+)"  => [undefined, undefined, undefined, "\d+", undefined, undefined]
        // "/*"            => ["/", undefined, undefined, undefined, undefined, "*"]
        '([\\/.])?(?:(?:\\:(\\w+)(?:\\(((?:\\\\.|[^()])+)\\))?|\\(((?:\\\\.|[^()])+)\\))([+*?])?|(\\*))'
    ].join('|'), 'g');


    keys = keys || [];

    if (!isArray(keys)) {
        options = keys;
        keys = [];
    } else if (!options) {
        options = {};
    }

    if (path instanceof RegExp) {
        return regexpToRegexp(path, keys);
    }

    if (isArray(path)) {
        return arrayToRegexp(path, keys, options);
    }

    return stringToRegexp(path, keys, options);

    /**
     * Parse a string for the raw tokens.
     *
     * @param  {String} str
     * @return {Array}
     */
    function parseTokens(str) {
        var tokens = [];
        var key = 0;
        var index = 0;
        var path = '';
        var res;

        while ((res = PATH_REGEXP.exec(str)) !== null) {
            var m = res[0];
            var escaped = res[1];
            var offset = res.index;
            path += str.slice(index, offset);
            index = offset + m.length;

            // Ignore already escaped sequences.
            if (escaped) {
                path += escaped[1];
                continue;
            }

            // Push the current path onto the tokens.
            if (path) {
                tokens.push(path);
                path = '';
            }

            var prefix = res[2];
            var name = res[3];
            var capture = res[4];
            var group = res[5];
            var suffix = res[6];
            var asterisk = res[7];

            var repeat = suffix === '+' || suffix === '*';
            var optional = suffix === '?' || suffix === '*';
            var delimiter = prefix || '/';
            var pattern = capture || group || (asterisk ? '.*' : '[^' + delimiter + ']+?');

            tokens.push({
                name: name || key++,
                prefix: prefix || '',
                delimiter: delimiter,
                optional: optional,
                repeat: repeat,
                pattern: escapeGroup(pattern)
            });
        }

        // Match any characters still remaining.
        if (index < str.length) {
            path += str.substr(index);
        }

        // If the path exists, push it onto the end.
        if (path) {
            tokens.push(path);
        }

        return tokens;
    }

    /**
     * Compile a string to a template function for the path.
     *
     * @param  {String}   str
     * @return {Function}
     */
    function compile(str) {
        return tokensToFunction(parseTokens(str));
    }

    /**
     * Expose a method for transforming tokens into the path function.
     */
    function tokensToFunction(tokens) {
        // Compile all the tokens into regexps.
        var matches = new Array(tokens.length);

        // Compile all the patterns before compilation.
        for (var i = 0; i < tokens.length; i++) {
            if (typeof tokens[i] === 'object') {
                matches[i] = new RegExp('^' + tokens[i].pattern + '$');
            }
        }

        return function (obj) {
            var path = '';
            var data = obj || {};

            for (var i = 0; i < tokens.length; i++) {
                var token = tokens[i];

                if (typeof token === 'string') {
                    path += token;

                    continue;
                }

                var value = data[token.name];
                var segment;

                if (value === null) {
                    if (token.optional) {
                        continue;
                    } else {
                        throw new TypeError('Expected "' + token.name + '" to be defined');
                    }
                }

                if (isArray(value)) {
                    if (!token.repeat) {
                        throw new TypeError('Expected "' + token.name + '" to not repeat, but received "' + value + '"');
                    }

                    if (value.length === 0) {
                        if (token.optional) {
                            continue;
                        } else {
                            throw new TypeError('Expected "' + token.name + '" to not be empty');
                        }
                    }

                    for (var j = 0; j < value.length; j++) {
                        segment = encodeURIComponent(value[j]);

                        if (!matches[i].test(segment)) {
                            throw new TypeError('Expected all "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"');
                        }

                        path += (j === 0 ? token.prefix : token.delimiter) + segment;
                    }

                    continue;
                }

                segment = encodeURIComponent(value);

                if (!matches[i].test(segment)) {
                    throw new TypeError('Expected "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"');
                }

                path += token.prefix + segment;
            }

            return path;
        }
    }

    /**
     * Escape a regular expression string.
     *
     * @param  {String} str
     * @return {String}
     */
    function escapeString(str) {
        return str.replace(/([.+*?=^!:${}()[\]|\/])/g, '\\$1');
    }

    /**
     * Escape the capturing group by escaping special characters and meaning.
     *
     * @param  {String} group
     * @return {String}
     */
    function escapeGroup(group) {
        return group.replace(/([=!:$\/()])/g, '\\$1');
    }

    /**
     * Attach the keys as a property of the regexp.
     *
     * @param  {RegExp} re
     * @param  {Array}  keys
     * @return {RegExp}
     */
    function attachKeys(re, keys) {
        re.keys = keys;
        return re;
    }

    /**
     * Get the flags for a regexp from the options.
     *
     * @param  {Object} options
     * @return {String}
     */
    function flags(options) {
        return options.sensitive ? '' : 'i';
    }

    /**
     * Pull out keys from a regexp.
     *
     * @param  {RegExp} path
     * @param  {Array}  keys
     * @return {RegExp}
     */
    function regexpToRegexp(path, keys) {
        // Use a negative lookahead to match only capturing groups.
        var groups = path.source.match(/\((?!\?)/g);

        if (groups) {
            for (var i = 0; i < groups.length; i++) {
                keys.push({
                    name: i,
                    prefix: null,
                    delimiter: null,
                    optional: false,
                    repeat: false,
                    pattern: null
                });
            }
        }

        return attachKeys(path, keys);
    }

    /**
     * Transform an array into a regexp.
     *
     * @param  {Array}  path
     * @param  {Array}  keys
     * @param  {Object} options
     * @return {RegExp}
     */
    function arrayToRegexp(path, keys, options) {
        var parts = [];

        for (var i = 0; i < path.length; i++) {
            parts.push(convertPathToRegexp(path[i], keys, options).source);
        }

        var regexp = new RegExp('(?:' + parts.join('|') + ')', flags(options));

        return attachKeys(regexp, keys);
    }

    /**
     * Create a path regexp from string input.
     *
     * @param  {String} path
     * @param  {Array}  keys
     * @param  {Object} options
     * @return {RegExp}
     */
    function stringToRegexp(path, keys, options) {
        var tokens = parseTokens(path);
        var re = tokensToRegExp(tokens, options);

        // Attach keys back to the regexp.
        for (var i = 0; i < tokens.length; i++) {
            if (typeof tokens[i] !== 'string') {
                keys.push(tokens[i]);
            }
        }

        return attachKeys(re, keys);
    }

    /**
     * Expose a function for taking tokens and returning a RegExp.
     *
     * @param  {Array}  tokens
     * @param  {Object} options
     * @return {RegExp}
     */
    function tokensToRegExp(tokens, options) {
        options = options || {};

        var strict = options.strict;
        var end = options.end !== false;
        var route = '';
        var lastToken = tokens[tokens.length - 1];
        var endsWithSlash = typeof lastToken === 'string' && /\/$/.test(lastToken);

        // Iterate over the tokens and create our regexp string.
        for (var i = 0; i < tokens.length; i++) {
            var token = tokens[i];

            if (typeof token === 'string') {
                route += escapeString(token);
            } else {
                var prefix = escapeString(token.prefix);
                var capture = token.pattern;

                if (token.repeat) {
                    capture += '(?:' + prefix + capture + ')*';
                }

                if (token.optional) {
                    if (prefix) {
                        capture = '(?:' + prefix + '(' + capture + '))?';
                    } else {
                        capture = '(' + capture + ')?';
                    }
                } else {
                    capture = prefix + '(' + capture + ')';
                }

                route += capture;
            }
        }

        // In non-strict mode we allow a slash at the end of match. If the path to
        // match already ends with a slash, we remove it for consistency. The slash
        // is valid at the end of a path match, not in the middle. This is important
        // in non-ending mode, where "/test/" shouldn't match "/test//route".
        if (!strict) {
            route = (endsWithSlash ? route.slice(0, -2) : route) + '(?:\\/(?=$))?';
        }

        if (end) {
            route += '$';
        } else {
            // In non-ending mode, we need the capturing groups to match as much as
            // possible by using a positive lookahead to the end or next path segment.
            route += strict && endsWithSlash ? '' : '(?=\\/|$)';
        }

        return new RegExp('^' + route, flags(options));
    }

}

/**
 * Parse the given query `str` or `obj`, returning an object.
 *
 * @param {String} str | {Object} obj
 * @return {Object}
 * @api public
 */
function queryString (str) {

    /**
     * Turn the given `obj` into a query string
     *
     * @param {Object|Array} obj
     * @param {string} prefix
     * @return {String}
     */
    queryString.stringify = stringify;

    /**
     * Object#toString() ref for stringify().
     */
    var toString = Object.prototype.toString;

    /**
     * Cache non-integer test regexp.
     */
    var isInt = /^[0-9]+$/;

    if (str === null || str === '') {
        return {};
    }
    return typeof str === 'object' ? parseObject(str) : parseString(str);

    function promote(parent, key) {
        if (parent[key].length === 0) {
            return parent[key] = {};
        }
        var t = {};
        for (var i in parent[key]) {
            t[i] = parent[key][i];
        }
        parent[key] = t;
        return t;
    }

    function parse(parts, parent, key, val) {
        var part = parts.shift();
        // end
        if (!part) {
            if (isArray(parent[key])) {
                parent[key].push(val);
            } else if (typeof parent[key] === 'object') {
                parent[key] = val;
            } else if (typeof parent[key] === 'undefined') {
                parent[key] = val;
            } else {
                parent[key] = [parent[key], val];
            }
            // array
        } else {
            var obj = parent[key] = parent[key] || [];
            if (part === ']') {
                if (isArray(obj)) {
                    if (val !== '') {
                        obj.push(val);
                    }
                } else if (typeof obj === 'object') {
                    obj[Object.keys(obj).length] = val;
                } else {
                    parent[key] = [parent[key], val];
                }
                // prop
            } else if (~part.indexOf(']')) {
                part = part.substr(0, part.length - 1);
                if (!isInt.test(part) && isArray(obj)) {
                    obj = promote(parent, key);
                }
                parse(parts, obj, part, val);
                // key
            } else {
                if (!isInt.test(part) && isArray(obj)) {
                    obj = promote(parent, key);
                }
                parse(parts, obj, part, val);
            }
        }
    }

    /**
     * Merge parent key/val pair.
     */
    function merge(parent, key, val) {
        if (~key.indexOf(']')) {
            parse(key.split('['), parent, 'base', val);
        } else {
            if (!isInt.test(key) && isArray(parent.base)) {
                var t = {};
                for (var k in parent.base) {
                    t[k] = parent.base[k];
                }
                parent.base = t;
            }
            set(parent.base, key, val);
        }

        return parent;
    }

    /**
     * Parse the given obj.
     * @return {Object}
     */
    function parseObject(obj) {
        var ret = {base: {}};
        Object.keys(obj).forEach(function (name) {
            merge(ret, name, obj[name]);
        });
        return ret.base;
    }

    /**
     * Parse the given str.
     * @return {Object}
     */
    function parseString(str) {
        var ret = {base: {}};
        String(str)
            .split('&')
            .reduce(
                function (ret, pair) {
                    try {
                        pair = decodeURIComponent(pair.replace(/\+/g, ' '));
                    } catch (e) {
                        // ignore
                    }

                    var eql = pair.indexOf('=');
                    var brace = lastBraceInKey(pair);
                    var key = pair.substr(0, brace || eql);
                    var val = pair.substr(brace || eql, pair.length);
                    val = val.substr(val.indexOf('=') + 1, val.length);

                    // ?foo
                    if (key === '') {
                        key = pair;
                        val = '';
                    }

                    return merge(ret, key, val);
                },
                ret
            );
        return ret.base;
    }

    /**
     * Turn the given `obj` into a query string
     *
     * @param {Object|Array} obj
     * @param {string} prefix
     * @return {String}
     */
    function stringify(obj, prefix) {
        if (isArray(obj)) {
            return stringifyArray(obj, prefix);
        } else if (toString.call(obj) === '[object Object]') {
            return stringifyObject(obj, prefix);
        } else if (typeof obj === 'string') {
            return stringifyString(obj, prefix);
        } else {
            return prefix + '=' + obj;
        }
    }

    /**
     * Stringify the given `str`.
     *
     * @param {String} str
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyString(str, prefix) {
        if (!prefix) {
            throw new TypeError('stringify expects an object');
        }
        return prefix + '=' + encodeURIComponent(str);
    }

    /**
     * Stringify the given `arr`.
     *
     * @param {Array} arr
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyArray(arr, prefix) {
        var ret = [];
        if (!prefix) {
            throw new TypeError('stringify expects an object');
        }
        for (var i = 0; i < arr.length; i++) {
            ret.push(stringify(arr[i], prefix + '[' + i + ']'));
        }
        return ret.join('&');
    }

    /**
     * Stringify the given `obj`.
     *
     * @param {Object} obj
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyObject(obj, prefix) {
        var ret = [];
        var keys = Object.keys(obj);
        var key;

        for (var i = 0, len = keys.length; i < len; ++i) {
            key = keys[i];
            ret.push(stringify(obj[key], prefix
                ? prefix + '[' + encodeURIComponent(key) + ']'
                : encodeURIComponent(key)));
        }

        return ret.join('&');
    }

    /**
     * Set `obj`'s `key` to `val` respecting
     * the weird and wonderful syntax of a qs,
     * where "foo=bar&foo=baz" becomes an array.
     *
     * @param {Object} obj
     * @param {String} key
     * @param {String} val
     * @api private
     */
    function set (obj, key, val) {
        var v = obj[key];
        if (v === undefined) {
            obj[key] = val;
        } else if (isArray(v)) {
            v.push(val);
        } else {
            obj[key] = [v, val];
        }
    }

    /**
     * Locate last brace in `str` within the key.
     *
     * @param {String} str
     * @return {Number}
     * @api private
     */
    function lastBraceInKey(str) {
        var len = str.length;
        var brace, c;
        for (var i = 0; i < len; ++i) {
            c = str[i];
            if (c === ']') {
                brace = false;
            }
            if (c === '[') {
                brace = true;
            }
            if (c === '=' && !brace) {
                return i;
            }
        }
    }
}

/**
 * Promise/Deferred implementation
 * Source: https://github.com/warpdesign/deferred-js
 * @param {function=} fn
 * @return {promise|deferred}
 * @constructor
 */
function Deferred(fn) {
    var status = 'pending';
    var doneFuncs = [];
    var failFuncs = [];
    var progressFuncs = [];
    var resultArgs = null;

    var promise = {
        done: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'resolved') {
                            arr[j].apply(this, resultArgs);
                        }

                        doneFuncs.push(arr[j]);
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'resolved') {
                        arguments[i].apply(this, resultArgs);
                    }

                    doneFuncs.push(arguments[i]);
                }
            }

            return this;
        },

        fail: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'rejected') {
                            arr[j].apply(this, resultArgs);
                        }

                        failFuncs.push(arr[j]);
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'rejected') {
                        arguments[i].apply(this, resultArgs);
                    }

                    failFuncs.push(arguments[i]);
                }
            }

            return this;
        },

        always: function () {
            return this.done.apply(this, arguments).fail.apply(this, arguments);
        },

        progress: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'pending') {
                            progressFuncs.push(arr[j]);
                        }
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'pending') {
                        progressFuncs.push(arguments[i]);
                    }
                }
            }

            return this;
        },

        then: function () {
            // fail callbacks
            if (arguments.length > 1 && arguments[1]) {
                this.fail(arguments[1]);
            }

            // done callbacks
            if (arguments.length > 0 && arguments[0]) {
                this.done(arguments[0]);
            }

            // notify callbacks
            if (arguments.length > 2 && arguments[2]) {
                this.progress(arguments[2]);
            }
        },

        promise: function (obj) {
            if (!obj) {
                return promise;
            } else {
                for (var i in promise) {
                    obj[i] = promise[i];
                }
                return obj;
            }
        },

        state: function () {
            return status;
        },

        debug: function () {
            console.log('[debug]', doneFuncs, failFuncs, status);
        },

        isRejected: function () {
            return status === 'rejected';
        },

        isResolved: function () {
            return status === 'resolved';
        }
    };

    var deferred = {
        resolveWith: function (context, args) {
            if (status === 'pending') {
                status = 'resolved';
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < doneFuncs.length; i++) {
                    doneFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        rejectWith: function (context, args) {
            if (status === 'pending') {
                status = 'rejected';
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < failFuncs.length; i++) {
                    failFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        notifyWith: function (context, args) {
            if (status === 'pending') {
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < progressFuncs.length; i++) {
                    progressFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        resolve: function () {
            return this.resolveWith(this, arguments);
        },

        reject: function () {
            return this.rejectWith(this, arguments);
        },

        notify: function () {
            return this.notifyWith(this, arguments);
        }
    };

    var obj = promise.promise(deferred);

    if (typeof fn === 'function') {
        fn.apply(obj, [obj]);
    }

    return obj;
}

/**
 * Run functions one by one. Each function MUST return Deferred object
 * @param {Array=} functions
 * @param {Object=} context - context of each funciton
 * @param {Array=} args - arguments to pass to each funciton
 * @return {*}
 */
Deferred.queue = function (functions, context, args) {
    var deferred = Deferred();
    if (!functions) {
        return deferred.resolve();
    }
    if (!isArray(functions)) {
        throw new TypeError('Deferred.queue: argument 1 (functions) must be an array or empty');
    }
    if (!functions.length) {
        return deferred.resolve();
    }
    if (!context) {
        context = deferred;
    } else if (typeof context !== 'object') {
        throw new TypeError('Deferred.queue: argument 2 (context) must be an object or empty');
    }
    if (!args) {
        args = [];
    } else if (!isArray(args)) {
        throw new TypeError('Deferred.queue: argument 3 (args) must be an array or empty');
    }

    var i = 0;
    var results = [];
    var next = function () {
        if (i < functions.length) {
            functions[i++]
                .apply(context, args)
                .then(
                    function () {
                        results.push(normalizeArguments(arguments));
                        next();
                    },
                    function () {
                        deferred.reject.apply(deferred, arguments);
                    });
        } else {
            deferred.resolve.call(deferred, results);
        }
    };
    next();
    return deferred.promise();
};

})(typeof window !== "undefined" ? window : this);
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
var DebugDialog = function () {

    var model = {
        title: 'no title',
        content: 'no content',
        isVisible: false
    };

    var container = $('<div id="debug-dialog"></div>');
    var template = '<div class="dialog opened">' +
            '<div class="dialog-close">&#x2716;</div>' +
            '<div class="dialog-title"></div>' +
            '<div class="dialog-content">' +
                '<iframe frameborder="0"></iframe>' +
        '</div>';

    this.showDebug = showDebug;
    this.toggleVisibility = toggle;

    function showDebug (title, content) {
        if (!content) {
            content = '';
        }
        if (!$.isPlainObject(content) && content.length > 3 && content[0] === '{' && content[content.length - 1] === '}') {
            try {
                var json = JSON.parse(content);
                content = json;
            } catch (ignore) {}
        }
        if (typeof content !== 'string') {
            content = '<html><head></head><body><pre style="white-space: pre-wrap;">'
                + JSON.stringify(content, null, 3).replace(/\\\\/g, '\\').replace(/\\["']/g, '"').replace(/\\n/g, "\n")
                + '</pre></body></html>';
        } else if (!content.match(/^\s*<(html|!doctype)/i)) {
            content = content.replace(/^([\s\S]*?)((?:<!doctype[^>]*>)?\s*<html[\s\S]*?<body[^>]*>)/i, '$2$1', content);
        }
        content = content.replace(/<(\/?script[^>]*)>/i, '&lt;$1&gt;');
        $.extend(model, {
            isVisible: true,
            title: title,
            content: content
        });
        render();
    }

    function toggle (event){
        model.isVisible = !model.isVisible;
        render();
    }

    function render () {
        if (model.isVisible) {
            var tpl = $(template);
            container.empty().append(tpl.find('.dialog-title').html(model.title).end());

            if (!$.contains(document.body, container[0])) {
                $(document.body).append(container);
            }

            var iframe = container.find('iframe')[0];
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(model.content);
                iframe.contentWindow.document.close();
            }
        } else {
            container.empty();
        }
        return this;
    }

    container.on('click', '.dialog-close', toggle);
};

var FormHelper = {
    messageAnimDurationMs: 200
};

FormHelper.initInputPlugins = function (container) {
    var $container = (container);
    $container
        .find('.selectpicker')
        .each(function () {
            // somehow it is loosing value that was set by $('select').val('val');
            var $select = $(this);
            var val = $select.val();
            if (val === null || typeof val === 'undefined') {
                val = $select.find('option[selected]').first().val();
                if (
                    (val === null  || typeof val === 'undefined')
                    && ($select.prop('required') || $select.find('option[value=""]').length === 0)
                ) {
                    val = $select.find('option:first').val();
                }
            }
            var pluginOptions = {};
            if ($select.find('option').length > 10) {
                pluginOptions.liveSearch = true;
                pluginOptions.liveSearchNormalize = true;
            }
            if (!$select.attr('data-style')) {
                if ($select.hasClass('input-sm')) {
                    pluginOptions.style = 'input-sm';
                }
                if ($select.hasClass('input-lg')) {
                    pluginOptions.style = 'input-lg';
                }
            }

            if ($select.attr('data-abs-ajax-url') || $select.attr('data-abs-ajax')) {
                pluginOptions.liveSearch = true;
                $select
                    .selectpicker(pluginOptions)
                    .ajaxSelectPicker({ajax: {data: {keywords: '{{{q}}}'}}});
            } else {
                $select.selectpicker(pluginOptions);
            }
            $select.selectpicker('val', val);
        });
    $container
        .find('input.switch[type="checkbox"]')
        .bootstrapSwitch();
    // input masks
    $container
        .find('input[data-type], textarea[data-type]')
        .each(function () {
            $(this)
                .attr({
                    'data-inputmask-alias': $(this).attr('data-type'),
                    'data-inputmask-rightAlign': 'false'
                })
                .removeAttr('data-type')
                .inputmask();
            $(this).val($(this).val());
        });
    $container
        .find('input[data-mask], textarea[data-mask]')
        .each(function () {
            $(this)
                .attr('data-inputmask-mask', $(this).attr('data-mask'))
                .removeAttr('data-mask')
                .inputmask();
            $(this).val($(this).val());
        });
    $container
        .find('input[data-regexp], textarea[data-regexp]')
        .each(function () {
            $(this)
                .attr({
                    'data-inputmask-regex': $(this).attr('data-regexp')
                })
                .removeAttr('data-regexp')
                .inputmask();
            $(this).val($(this).val());
        });
    $container
        .find(
            'input[data-inputmask], textarea[data-inputmask], ' +
            'input[data-inputmask-alias], textarea[data-inputmask-alias], ' +
            'input[data-inputmask-mask], textarea[data-inputmask-mask], ' +
            'input[data-inputmask-regex], textarea[data-inputmask-regex]'
        )
        .each(function () {
            if (!$(this).attr('data-inputmask-rightAlign')) {
                $(this).attr('data-inputmask-rightAlign', 'false');
            }
            $(this).inputmask();
            $(this).val($(this).val());
        });
};

FormHelper.setValuesFromDataAttributes = function (container) {
    var $container = (container);
    $container
        .find('select')
        .each(function () {
            var value = this.getAttribute('data-value');
            if (value) {
                if (this.multiple) {
                    try {
                        var json = JSON.parse(value);
                        $(this).val(json);
                    } catch (exc) {
                        $(this).val(value);
                    }
                } else {
                    $(this).val(value);
                }
            }
            $(this).change();
        });
};

FormHelper.initForm = function (form, container, onSubmitSuccess, options) {
    var $form = $(form);
    if (!$form.length) {
        console.error('Passed $form argument is not a valid element or selector', $form);
        return;
    }
    var $container = $(container);
    if (!options) {
        options = {}
    }
    options = $.extend({}, {
        isJson: true,
        clearForm: true,
        onValidationErrors: null,
        onResponse: null,
        beforeSubmit: null
    }, options);
    var customInitiator = $form.attr('data-initiator');
    if (customInitiator) {
        var ret = null;
        if (customInitiator.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
            eval('customInitiator = ' + customInitiator);
            if (typeof customInitiator === 'function') {
                ret = customInitiator.call(form, $form, $container, onSubmitSuccess);
            }
        }
        if (ret === false) {
            // notify that form was initiated
            $form.trigger('ready.cmfform');
            return;
        }
    }
    // set values
    FormHelper.setValuesFromDataAttributes($form);
    // init plugins
    FormHelper.initInputPlugins($form);
    // init submit
    $form.ajaxForm({
        clearForm: !!options.clearForm,
        dataType: !options.isJson ? 'html' : 'json',
        beforeSubmit: function () {
            var defaultBeforeSubmit = function () {
                FormHelper.removeAllFormMessagesAndErrors($form);
                Utils.showPreloader($container);
            };
            if (typeof options.beforeSubmit === 'function') {
                options.beforeSubmit($form, $container, defaultBeforeSubmit);
            } else {
                defaultBeforeSubmit();
            }
        },
        error: function (xhr) {
            Utils.hidePreloader($container);
            if ((xhr.status === 400 || xhr.status === 422) && typeof options.onValidationErrors === 'function') {
                options.onValidationErrors(xhr, $form, $container);
            } else {
                FormHelper.handleAjaxErrors($form, xhr, this);
            }
            if (typeof options.onResponse === 'function') {
                options.onResponse($form, $container);
            }
        },
        success: function (data) {
            if ($.isFunction(onSubmitSuccess)) {
                onSubmitSuccess(data, $form, $container);
            } else {
                Utils.hidePreloader($container);
                if (options.isJson) {
                    Utils.handleAjaxSuccess(data);
                }
            }
            if (typeof options.onResponse === 'function') {
                options.onResponse($form, $container);
            }
        }
    });
    // notify that form was initiated
    $form.trigger('ready.cmfform');
    return $form;
};

FormHelper.removeAllFormMessagesAndErrors = function ($form) {
    return $.when(FormHelper.removeFormMessage($form), FormHelper.removeFormValidationMessages($form));
};

FormHelper.setFormMessage = function ($form, message, type) {
    if (!type) {
        type = 'error';
    }
    toastr[type](message);
    /*
    var errorDiv = $form.find('.form-error');
    if (!errorDiv.length) {
        errorDiv = $('<div class="form-error text-center"></div>').hide();
        $form.prepend(errorDiv);
    }
    return errorDiv.slideUp(100, function () {
        errorDiv.html('<div class="alert alert-' + type + '">' + message + '</div>').slideDown(100);
    });*/
};

FormHelper.addFormMessage = function ($form, message, type) {
    if (!type) {
        type = 'error';
    }
    toastr[type](message);
};

FormHelper.removeFormMessage = function ($form) {
    /*var errorDiv = $form.find('.form-error');
    return errorDiv.slideUp(100, function () {
        errorDiv.html('');
    })*/
};

FormHelper.removeFormValidationMessages = function ($form) {
    $form.find('.has-error').removeClass('has-error');
    return $form.find('.error-text').slideUp(FormHelper.messageAnimDurationMs, function () {
        $(this).html('');
    });
};

FormHelper.handleAjaxErrors = function ($form, xhr, request) {
    FormHelper.removeAllFormMessagesAndErrors($form)
        .done(function () {
            if (xhr.status === 400 || xhr.status === 422) {
                var response = Utils.convertXhrResponseToJsonIfPossible(xhr);
                if (!response) {
                    Utils.handleAjaxError.call(request, xhr);
                    return;
                }
                var inputName;
                if (xhr.status === 422 && !response.errors) {
                    var strings = CmfConfig.getLocalizationStringsForComponent('form');
                    if (strings && strings.invalid_data_received) {
                        FormHelper.setFormMessage($form, strings.invalid_data_received);
                    }
                    for (inputName in response) {
                        FormHelper.showErrorForInput($form, inputName, response[inputName]);
                    }
                } else {
                    if (response._message) {
                        FormHelper.setFormMessage($form, response._message);
                    }
                    if (response.errors && $.isPlainObject(response.errors)) {
                        for (inputName in response.errors) {
                            FormHelper.showErrorForInput($form, inputName, response.errors[inputName]);
                        }
                    }
                }
                return;
            }
            Utils.handleAjaxError.call(request, xhr);
        });
};

FormHelper.showErrorForInput = function ($form, inputName, message) {
    if ($form[0][inputName]) {
        var $container = null;
        var $input = $($form[0][inputName]);
        if ($input.attr('data-error-container')) {
            $container = $form.find($input.attr('data-error-container'))
        }
        if (!$container || !$container.length) {
            $container = $input.closest('.form-group, .checkbox');
        }
        $container.addClass('has-error');
        var $errorEl = $container.find('.error-text');
        if ($errorEl.length == 0) {
            $errorEl = $('<div class="error-text bg-danger"></div>').hide();
            var $helpEl = $container.find('.help-block');
            if ($helpEl.length) {
                $helpEl.before($errorEl);
            } else {
                $container.append($errorEl);
            }
        }
        if ($.isArray(message)) {
            message = message.join('; ');
        }
        $errorEl.html(message).slideDown(FormHelper.messageAnimDurationMs);
        // mark current tab that it has an error inside
        var $tabs = $form.find('.nav-tabs');
        if ($tabs.length) {
            var tabId = $container.closest('.tab-pane').attr('id');
            if (tabId) {
                $tabs.find('a[href="#' + tabId + '"]').parent('li').addClass('has-error');
            }
        }
    } else {
        FormHelper.addFormMessage($form, inputName + ': ' + message, 'error');
    }
};

FormHelper.inputsDisablers = {};

FormHelper.inputsDisablers.init = function (formSelector, disablers, runDisablers) {
    var $form = $(formSelector);
    if ($form.length === 0) {
        return;
    }
    if (!$.isArray(disablers)) {
        console.error('Disablers argument must be a plain array');
    }
    var findInput = function (name) {
        var $matchingInputs = $form
            .find('[name="' + name + '[]"], [name="' + name + '"]')
            .filter('input, select, textarea');
        if ($matchingInputs.length === 0) {
            return null;
        } else if ($matchingInputs.length === 1) {
            return $matchingInputs.first();
        } else {
            var $notHiddenInputs = $matchingInputs.not('[type="hidden"]');
            if ($notHiddenInputs.length > 0) {
                return $notHiddenInputs.first();
            } else {
                var $multiValueInputs = $matchingInputs.filter('[name="' + name + '[]"]');
                if ($multiValueInputs.length > 0) {
                    return $multiValueInputs; //< for select multiple and checkboxes list
                } else {
                    return $matchingInputs; //< for radios
                }
            }
        }
    };
    var validDisablers = [];
    for (var i = 0; i < disablers.length; i++) {
        var disablerConfig = disablers[i];
        if (
            !$.isPlainObject(disablerConfig)
            || !disablerConfig.input_name
            || !disablerConfig.conditions
            || !$.isArray(disablerConfig.conditions)
            || disablerConfig.conditions.length === 0
        ) {
            continue;
        }
        var $inputToDisable = findInput(disablerConfig.input_name);
        if (!$inputToDisable) {
            console.error(
                "Target input with name '" + disablerConfig.input_name + "' or '"
                + disablerConfig.input_name + "[]' was not found in form"
            );
            continue;
        }
        var allDisablersAreValid = true;
        var validConditions = [];
        for (var k = 0; k < disablerConfig.conditions.length; k++) {
            var condition = disablerConfig.conditions[k];
            if (condition.force_state === true) {
                // always disable/readonly
                $inputToDisable.prop(condition.attribute || 'disabled', true);
                validConditions = [];
                break;
            }
            var $disablerInput = findInput(condition.disabler_input_name);
            if (!$disablerInput) {
                if (condition.ignore_if_disabler_input_is_absent) {
                    continue;
                } else {
                    console.error(
                        "Enabler input with name '" + condition.disabler_input_name + "' or '"
                        + condition.disabler_input_name + "[]' were not found in form"
                    );
                    allDisablersAreValid = false;
                    break;
                }
            } else {
                if (typeof condition.on_value === 'undefined') {
                    console.error(
                        "No value provided in condition for disabler '" + condition.disabler_input_name
                        + "' on input '" + disablerConfig.input_name
                    );
                    allDisablersAreValid = false;
                    break;
                } else if (condition.on_value !== true && condition.on_value !== false) {
                    var regexpParts = condition.on_value.match(/^\/(.*)\/(i?g?m?|i?m?g?|g?m?i?|g?i?m?|m?i?g?|m?g?i?)$/);
                    if (regexpParts === null) {
                        console.error(
                            "Invalid regexp '" + condition.on_value + "' for disabler '" + condition.disabler_input_name
                            + "' on input '" + disablerConfig.input_name +
                            + "'. Expected string like: '/<regexp_body>/<flags>' where flags: mix of 'i', 'm', 'g'"
                        );
                        allDisablersAreValid = false;
                        break;
                    }
                    condition.regexp = new RegExp(regexpParts[1], regexpParts[2]);
                }
                condition.$disablerInput = $disablerInput;
                condition.isDisablerInputChecboxOrRadio = $disablerInput.filter('[type="checkbox"], [type="radio"]').length > 0;
                condition.value_is_equals = typeof condition.value_is_equals === 'undefined' ? true : !!condition.value_is_equals;
                validConditions.push(condition);
            }
        }
        if (!allDisablersAreValid || validConditions.length === 0) {
            continue;
        }
        disablerConfig.conditions = validConditions;
        disablerConfig.$targetInput = $inputToDisable;
        for (var h = 0; h < disablerConfig.conditions.length; h++) {
            if (disablerConfig.conditions[h] && disablerConfig.conditions[h].$disablerInput) {
                FormHelper.inputsDisablers.setDisablerInputValueChangeEventHandlers(disablerConfig, disablerConfig.conditions[h]);
            }
        }
        validDisablers.push(disablerConfig);
    }
    $form.data('disablers', validDisablers);
    if (runDisablers) {
        FormHelper.inputsDisablers.onFormDataChanged($form);
    }
};

FormHelper.inputsDisablers.setDisablerInputValueChangeEventHandlers = function (disablerConfig, condition) {
    var $disablerInput = condition.$disablerInput;
    if ($disablerInput.prop("tagName").toLowerCase() === 'select') {
        $disablerInput.on('run-disabler.cmfform change blur', function () {
            FormHelper.inputsDisablers.handleDisablerInputValueChange(disablerConfig);
        });
    } else {
        if ($disablerInput.not('[type="checkbox"], [type="radio"]').length > 0) {
            // input (excluding checkbox and radio) or textarea
            $disablerInput.on('run-disabler.cmfform change blur keyup', function () {
                FormHelper.inputsDisablers.handleDisablerInputValueChange(disablerConfig);
            });
        } else {
            // checkbox or radio
            $disablerInput.on('run-disabler.cmfform change switchChange.bootstrapSwitch', function () {
                FormHelper.inputsDisablers.handleDisablerInputValueChange(disablerConfig);
            });
        }
    }
};

FormHelper.inputsDisablers.handleDisablerInputValueChange = function (disablerConfig) {
    var $targetInput = disablerConfig.$targetInput;
    var disablerCondition = FormHelper.inputsDisablers.isInputMustBeDisabled(disablerConfig);
    if (disablerCondition && typeof disablerCondition.set_readonly_value !== 'undefined' && disablerCondition.set_readonly_value !== null) {
        if ($targetInput.not('[type="checkbox"], [type="radio"]').length > 0) {
            $targetInput.val(disablerCondition.set_readonly_value).change();
        } else {
            if (
                $targetInput.attr('type')
                && $targetInput.attr('type').toLowerCase() === 'checkbox'
                && $targetInput.length === 1
            ) {
                // single checkbox
                $targetInput.prop('checked', !!disablerCondition.set_readonly_value).change();
            } else {
                // multiple checkboxes or set of radios
                $targetInput
                    .prop('checked', false)
                    .filter('[value="' + disablerCondition.set_readonly_value + '"]')
                        .prop('checked', true)
                        .end()
                    .change();
            }
        }
    }
    if ($targetInput.hasClass('selectpicker')) {
        if (disablerCondition) {
            $targetInput.prop({disabled: true, readOnly: true});
        } else {
            $targetInput.prop({disabled: false, readOnly: false});
        }
        $targetInput.selectpicker('refresh');
    } else if ($targetInput.attr('data-editor-name')) {
        var editor = CKEDITOR.instances[$targetInput.attr('data-editor-name')];
        if (editor) {
            editor.setReadOnly(!!disablerCondition);
        }
    } else if ($targetInput.hasClass('switch')) {
        if (disablerCondition) {
            $targetInput.bootstrapSwitch(disablerCondition.attribute, true)
        } else {
            $targetInput.bootstrapSwitch('readonly', false);
            $targetInput.bootstrapSwitch('disabled', false);
        }
    }
    $targetInput.prop({disabled: false, readOnly: false});
    if (disablerCondition) {
        $targetInput.prop(disablerCondition.attribute, true);
    }
    $targetInput.change();
};

/**
 * @param {object} disablerConfig
 * @return {null|object} - disabler condition that disables the input first
 */
FormHelper.inputsDisablers.isInputMustBeDisabled = function (disablerConfig) {
    for (var i = 0; i < disablerConfig.conditions.length; i++) {
        var condition = disablerConfig.conditions[i];
        var isConditionDisablesInput = false;
        var valueIsAffectedByValueIsEquals = false;
        if (condition.isDisablerInputChecboxOrRadio) {
            if (condition.$disablerInput.attr('type').toLowerCase() === 'checkbox' && condition.$disablerInput.length === 1) {
                // single checkbox
                isConditionDisablesInput = condition.$disablerInput.prop('checked') === !!condition.on_value;
            } else {
                // multiple checkboxes or set of radios
                for (var k = 0; k < condition.$disablerInput.filter(':checked').length; k++) {
                    if (condition.regexp.test($(this).val())) {
                        if (condition.value_is_equals) {
                            isConditionDisablesInput = true;
                            break;
                        }
                    } else if (!condition.value_is_equals) {
                        isConditionDisablesInput = true;
                        break;
                    }
                }
                valueIsAffectedByValueIsEquals = true;
            }
        } else if (condition.regexp) {
            // text input, select, textarea
            isConditionDisablesInput = condition.regexp.test(condition.$disablerInput.val());
        } else {
            // hidden/text input with bool value in it
            isConditionDisablesInput = condition.$disablerInput.val() === (condition.on_value ? '1' : '0');
        }
        if (!valueIsAffectedByValueIsEquals && !condition.value_is_equals) {
            isConditionDisablesInput = !isConditionDisablesInput;
        }
        if (isConditionDisablesInput) {
            return condition;
        }
    }
    return null;
};

FormHelper.inputsDisablers.onFormDataChanged = function (form) {
    var disablers = $(form).data('disablers');
    if (disablers && $.isArray(disablers)) {
        for (var i = 0; i < disablers.length; i++) {
            if (disablers[i] && disablers[i].conditions && $.isArray(disablers[i].conditions)) {
                for (var k = 0; k < disablers[i].conditions.length; k++) {
                    if (disablers[i].conditions[k] && disablers[i].conditions[k].$disablerInput) {
                        disablers[i].conditions[k].$disablerInput.trigger('run-disabler.cmfform');
                    }
                }
            }

        }
    }
};

var AdminUI = {
    $el: null,
    visible: false,
    loaded: false,
    userInfoTplSelector: '#user-panel-tpl',
    userInfoTpl: null,
    userInfoContainer: '#user-panel .info'
};

AdminUI.destroyUI = function () {
    var deferred = $.Deferred();
    if (AdminUI.visible) {
        var wrapper = Utils.getPageWrapper();
        AdminUI.stopMenuCountersUpdates();
        wrapper.fadeOut(CmfConfig.contentChangeAnimationDurationMs, function () {
            if (AdminUI.$el) {
                AdminUI.$el.detach();
            }
            wrapper.removeClass('with-ui').empty();
            wrapper.show();
            AdminUI.visible = false;
            deferred.resolve();
            $(document).trigger('appui:hidden');
        });
    } else {
        deferred.resolve();
    }
    return deferred.promise();
};

AdminUI.showUI = function () {
    var deferred = $.Deferred();
    var wrapper = Utils.getPageWrapper();
    if (AdminUI.visible) {
        deferred.resolve();
    } else if (AdminUI.loaded) {
        AdminUI.visible = true;
        AdminUI.updateUserInfo();
        wrapper.fadeIn(CmfConfig.contentChangeAnimationDurationMs);
        deferred.resolve();
        AdminUI.startMenuCountersUpdates();
        $(document).trigger('appui:shown');
    } else {
        Utils.showPreloader(wrapper);
        $.when(
                AdminUI.loadUI(),
                wrapper.fadeOut(CmfConfig.contentChangeAnimationDurationMs)
            )
            .done(function ($ui) {
                wrapper.addClass('with-ui').empty().append($ui);
                AdminUI.visible = true;
                AdminUI.updateUserInfo();
                wrapper.fadeIn(CmfConfig.contentChangeAnimationDurationMs);
                deferred.resolve();
                AdminUI.startMenuCountersUpdates();
                $(document).trigger('appui:shown');
            })
            .fail(function (error) {
                deferred.reject(error);
            });
    }
    return deferred.promise();
};

AdminUI.loadUI = function () {
    var deferred = $.Deferred();
    if (!AdminUI.loaded) {
        Utils.downloadHtml(CmfConfig.uiUrl, true, false)
            .done(function (html) {
                AdminUI.loaded = true;
                AdminUI.$el = $('<div class="ui-container"></div>').html(html);
                deferred.resolve(AdminUI.$el);
                AdminUI.initMenuCountersUpdatesAfterAjaxRequests();
                AdminUI.initCustomScrollbars(AdminUI.$el);
                $(document).trigger('appui:loaded');
            })
            .fail(function (error) {
                deferred.reject(error);
            });
    } else {
        deferred.resolve(AdminUI.$el);
    }
    return deferred.promise();
};

AdminUI.initCustomScrollbars = function ($el) {
    var $scrollbarContainers = $el.find('[ss-container]');
    if ($scrollbarContainers.length) {
        var observer = null;
        var observerConfig = {subtree: true, childList: true, attributes: true, attributeFilter: ['style', 'class']};
        if (typeof MutationObserver !== 'undefined') {
            observer = new MutationObserver(function (mutations) {
                var $scrollContainer = $(mutations[0].target).closest('.ss-container');
                if ($scrollContainer.length && $scrollContainer[0].hasOwnProperty('data-simple-scrollbar')) {
                    $scrollContainer[0]['data-simple-scrollbar'].moveBar();
                }
            });
        }
        for (var i = 0; i < $scrollbarContainers.length; i++) {
            if (!$scrollbarContainers[i].hasOwnProperty('data-simple-scrollbar')) {
                Object.defineProperty(
                    $scrollbarContainers[i],
                    'data-simple-scrollbar',
                    {value: new SimpleScrollbar($scrollbarContainers[i])}
                );
            }
            if (observer) {
                observer.observe($($scrollbarContainers[i]).find('.ss-content')[0], observerConfig);
            }
        }
    }
};

AdminUI.updateUserInfo = function (userInfo) {
    if (!AdminUI.visible) {
        return;
    }
    if (!userInfo) {
        Utils.getUser().done(function (userInfo) {
            AdminUI.updateUserInfo(userInfo);
        });
        return;
    }
    var container = $(AdminUI.userInfoContainer);
    if (!AdminUI.userInfoTpl) {
        container.addClass('fading fade-out').width();
        var $tpl = $(AdminUI.userInfoTplSelector);
        if ($tpl.length) {
            Utils.makeTemplateFromText(
                    $tpl.html(),
                    'User Info block template'
                )
                .done(function (template) {
                    AdminUI.userInfoTpl = template;
                    container.html(AdminUI.userInfoTpl(userInfo)).removeClass('fade-out');
                    $(document).on('change:user', function (event, userInfo) {
                        AdminUI.updateUserInfo(userInfo);
                    });
                })
                .fail(function (error) {
                    throw error;
                });
        }
    } else {
        container.html(AdminUI.userInfoTpl(userInfo)).removeClass('fade-out');
    }
};

AdminUI.initMenuCountersUpdatesAfterAjaxRequests = function () {
    if (CmfConfig.enableMenuCounters) {
        $(document).ajaxSuccess(function (event, xhr, options) {
            if (
                $.inArray(options.type, ['POST', 'PUT', 'DELETE']) !== -1
                || options.url.match(/_method=(POST|PUT|DELETE)/) !== null
                || ($.isPlainObject(options.data) && options.data._method && $.inArray(options.data._method, ['POST', 'PUT', 'DELETE']) !== -1)
                || (typeof options.data === 'string' && options.data.match(/_method=(POST|PUT|DELETE)/) !== null)
            ) {
                AdminUI.updateMenuCounters();
            }
        });
    }
};

AdminUI.menuCountersInterval = null;
AdminUI.startMenuCountersUpdates = function () {
    if (AdminUI.menuCountersInterval || !CmfConfig.enableMenuCounters) {
        return;
    }
    AdminUI.updateMenuCounters();
    AdminUI.menuCountersInterval = setInterval(AdminUI.updateMenuCounters, CmfConfig.menuCountersUpdateIntervalMs);
};

AdminUI.stopMenuCountersUpdates = function () {
    if (AdminUI.menuCountersInterval) {
        clearInterval(AdminUI.menuCountersInterval);
        AdminUI.menuCountersInterval = null;
    }
};

AdminUI.disableMenuCountersUpdates = function () {
    CmfConfig.enableMenuCounters = false;
    AdminUI.stopMenuCountersUpdates();
};

AdminUI.updateMenuCounters = function () {
    var deferred = $.Deferred();
    if (!AdminUI.visible || !CmfConfig.enableMenuCounters) {
        return deferred.resolve({});
    }
    $.ajax({
        url: CmfConfig.menuCountersDataUrl,
        cache: false,
        dataType: 'json',
        method: 'GET'
    }).done(function (json) {
        if ($.isPlainObject(json) && !$.isEmptyObject(json)) {
            $(document).find('[data-counter-name]').each(function () {
                var counterName = $.trim($(this).data('counter-name'));
                if (counterName && json[counterName]) {
                    $(this).html(json[counterName]);
                }
            });
        } else if (CmfConfig.disableMenuCountersIfEmptyOrInvalidDataReceived) {
            AdminUI.disableMenuCountersUpdates();
            deferred.resolve({});
            return;
        }
        deferred.resolve(json);
    }).fail(function (xhr) {
        Utils.handleAjaxError.call(this, xhr, deferred);
        AdminUI.stopMenuCountersUpdates();
        if (CmfConfig.disableMenuCountersIfEmptyOrInvalidDataReceived) {
            AdminUI.disableMenuCountersUpdates();
        }
    });
    return deferred.promise();

};
var Utils = {
    bodyClass: false,
    loadedJsFiles: {},
    loadedCssFiles: {},
    cacheLoadedJsFiles: true,
    modalTpl: null,
    modalsCount: 0
};

Utils.configureAppLibs = function () {
    Utils.configureAjax();
    Utils.configureToastr();
    if (typeof $.inputmask !== 'undefined') {
        $.inputmask.defaults.rightAlign = false;
        $.inputmask.defaults.rightAlignNumerics = false;
    }
};

Utils.configureToastr = function () {
    toastr.options = CmfConfig.toastrOptions;
};

Utils.configureAjax = function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    if (CmfConfig.isDebug) {
        $(document).ajaxComplete(function (event, xhr, settings) {
            var path = settings.url.replace(/\?.*$/, '');
            var query = settings.url.replace(/^.*\?(.*)$/, '$1');
            console.group('Ajax: %s %s', settings.type || 'GET', path, xhr.status, xhr.statusText);
            if (query.length > 0) {
                var queryData = $.extend(page.queryString(query), (settings.type !== 'GET' ? {} : (settings.data || {})));
                delete queryData._; //< remove anticache argument
                if (!$.isEmptyObject(queryData)) {
                    console.groupCollapsed('GET');
                    console.log(queryData);
                    console.groupEnd();
                }
            }
            if (settings.type === 'POST') {
                console.groupCollapsed('POST');
                if ($.isPlainObject(settings.data) || $.isArray(settings.data)) {
                    console.log(settings.data);
                } else {
                    console.log(page.queryString(settings.data));
                }
                console.groupEnd();
            }
            console.groupCollapsed('Response');
            var json = Utils.convertXhrResponseToJsonIfPossible(xhr);
            console.log(json || xhr.responseText);
            console.groupEnd();
            console.groupEnd();
        });
    }
};

Utils.convertXhrResponseToJsonIfPossible = function (xhr) {
    if (xhr.responseText && xhr.responseText.length >= 2 && (xhr.responseText[0] === '{' || xhr.responseText[0] === '[')) {
        try {
            return JSON.parse(xhr.responseText);
        } catch (exc) {}
    }
    return false;
};

/**
 * @param jsFiles
 * @param cssFiles
 * @return $.Deferred()
 */
Utils.requireFiles = function (jsFiles, cssFiles) {
    var deferred = $.Deferred();
    // js
    var loadedJsFiles = 0;
    if (typeof jsFiles !== 'undefined') {
        if (!$.isArray(jsFiles)) {
            if (typeof jsFiles === 'string') {
                jsFiles = [jsFiles];
            } else {
                console.trace();
                alert('jsFiles argument in Utils.requireFiles() must be a string or array');
            }
        }
        for (var i = 0; i < jsFiles.length; i++) {
            if (typeof jsFiles[i] !== 'string') {
                alert('jsFiles argument in Utils.requireFiles() must contain only strings. Not a string detected in index ' + i);
            }
            var jsFileCleaned = jsFiles[i].replace(/(\?|&)_=[0-9]+/, '');
            if (Utils.cacheLoadedJsFiles) {
                if (Utils.loadedJsFiles[jsFileCleaned]) {
                    Utils.loadedJsFiles[jsFileCleaned].done(function () {
                        loadedJsFiles++;
                        if (jsFiles.length === loadedJsFiles) {
                            deferred.resolve();
                        }
                    });
                    continue;
                } else if ($('script[src^="' + jsFileCleaned + '"]').length) {
                    Utils.loadedJsFiles[jsFileCleaned] = $.Deferred().resolve().promise();
                    loadedJsFiles++;
                    if (jsFiles.length === loadedJsFiles) {
                        deferred.resolve();
                    }
                    continue;
                }
            }
            Utils.loadedJsFiles[jsFileCleaned] = $.getScript(jsFiles[i])
                .done(function (data, textStatus, jqxhr) {
                    loadedJsFiles++;
                    if (jsFiles.length === loadedJsFiles) {
                        deferred.resolve();
                    }
                })
                .fail(function (jqxhr, settings, exception) {
                    alert('Failed to load js file ' + this.url + '. Error: ' + exception);
                    deferred.reject(exception);
                });
        }
    } else {
        deferred.resolve();
    }
    // css
    if (typeof cssFiles !== 'undefined') {
        if (!$.isArray(cssFiles)) {
            if (typeof cssFiles === 'string') {
                cssFiles = [cssFiles];
            } else {
                console.trace();
                alert('cssFiles argument in Utils.requireFiles() must be a string or array');
            }
        }
        if (cssFiles && $.isArray(cssFiles)) {
            for (i = 0; i < cssFiles.length; i++) {
                if (typeof cssFiles[i] !== 'string') {
                    alert('cssFiles argument in Utils.requireFiles() must contain only strings. Not a string detected in index ' + i);
                }
                var cssFileCleaned = cssFiles[i].replace(/(\?|&)_=[0-9]+/, '');
                if (Utils.loadedCssFiles[cssFileCleaned] || $('link[href^="' + cssFileCleaned + '"]').length) {
                    Utils.loadedCssFiles[cssFileCleaned] = true;
                    continue;
                }
                if (document.createStyleSheet) {
                    document.createStyleSheet(cssFiles[i]);
                } else {
                    $('body').before(
                        $('<link rel="stylesheet" href="' + cssFiles[i] + '" type="text/css" />')
                    );
                    Utils.loadedCssFiles[cssFileCleaned] = true;
                }
            }
        }
    }
    return deferred.promise();
};

/**
 * Handle AJAX error
 * @param {object} xhr
 * @param {Deferred|null} deferredToRejectWithError - reject this deferred with Error object and good message
 */
Utils.handleAjaxError = function (xhr, deferredToRejectWithError) {
    var isRejectableDeferred = typeof deferredToRejectWithError === 'object' && typeof deferredToRejectWithError.reject === 'function';
    if (xhr.status >= 400 && isRejectableDeferred) {
        deferredToRejectWithError.reject(
            new Error(
                this.url + ': ' + xhr.statusText + '; Response: ' + xhr.responseText,
                xhr.status
            )
        );
    }
    if (xhr.status === 419) {
        // CSRF token missmatch or session expired
        toastr.error(CmfConfig.getLocalizationString(
            'error.csrf_token_missmatch',
            'Your session has timed out. Page will be reloaded in 5 seconds.'
        ));
        setTimeout(function () {
            document.location.reload(true);
        }, 5000);
        Utils.showPreloader($(document.body));
        return;
    }
    var json = Utils.convertXhrResponseToJsonIfPossible(xhr);
    if (json) {
        if (json.redirect_with_reload) {
            document.location = json.redirect_with_reload;
        } else if (json.redirect) {
            if (json.redirect === 'back') {
                if (isRejectableDeferred) {
                    deferredToRejectWithError.reject();
                }
                page.back(json.redirect_fallback);
            } else if (json.redirect[0] === '/') {
                if (isRejectableDeferred) {
                    deferredToRejectWithError.reject();
                }
                page.show(json.redirect);
            } else {
                document.location = json.redirect;
            }
        }
        if (json._message || json.error) {
            toastr.error(json._message || json.error);
        }
        if (json.redirect || json.redirect_with_reload || json._message || json.error) {
            return;
        }
    }
    var url = this.url || null;
    if (xhr.status === 0) {
        toastr.error('Request to ' + url + ' failed. Reason: no internet connection');
        if (isRejectableDeferred) {
            deferredToRejectWithError.reject();
        }
        return;
    } else if (xhr.status === 200 && xhr.responseText === '') {
        toastr.info('Empty response from ' + url + '. Possibly debug session was stopped.');
        if (isRejectableDeferred) {
            deferredToRejectWithError.reject();
        }
        return;
    }
    if (xhr.responseText.length === 0 || typeof CmfConfig === 'undefined') {
        toastr.error('HTTP Error ' + xhr.status + ' ' + xhr.statusText);
    } else {
        CmfConfig.getDebugDialog().showDebug(
            'HTTP Error ' + xhr.status + ' ' + xhr.statusText,
            json ? json : xhr.responseText
        );
    }
};

Utils.handleAjaxSuccess = function (json) {
    try {
        if (json.redirect_with_reload) {
            document.location = json.redirect_with_reload;
        } else if (json.redirect) {
            var hasPageJsRouter = typeof page !== 'undefined' && typeof page.show === 'function';
            switch (json.redirect) {
                case 'back':
                    if (hasPageJsRouter) {
                        page.back(json.redirect_fallback);
                    } else {
                        document.location = json.redirect_fallback || '/';
                    }
                    break;
                case 'reload':
                    if (hasPageJsRouter) {
                        page.reload();
                    } else {
                        document.location.reload();
                    }
                    break;
                default:
                    if (hasPageJsRouter) {
                        page.show(json.redirect, null, true, true, {env: {is_ajax_response: true}});
                    } else {
                        document.location = json.redirect;
                    }
            }
        }
    } catch (exc) {
        console.error(exc.name + ': ' + exc.message);
        console.error(exc.stack);
    }
    if (json._message) {
        // do not place before json.redirect!!! message may be suppressed as soon as redirection starts
        toastr.success(json._message);
    }
    if (json._reload_user) {
        Utils.getUser(true);
    }
    if (json.modal && $.isPlainObject(json.modal) && (typeof json.modal.content !== 'undefined')) {
        var $modal = Utils.makeModal(
            json.modal.title || '',
            json.modal.content,
            json.modal.footer || '',
            json.modal.size || 'medium'
        );
        if (json.repeat_action && typeof json.repeat_action === 'function') {
            $modal.find('.reload-url-button, .reload')
                .removeClass('hidden')
                .on('click', function (event) {
                    event.preventDefault();
                    $modal
                        .one('hide.bs.modal', function () {
                            json.repeat_action();
                        })
                        .modal('hide');
                    return false;
                });
        }
        $(document.body).append($modal);
        // todo: add json.modal.url usage
        $modal
            .one('hidden.bs.modal', function () {
                $modal.remove();
            })
            .modal({
                show: true,
                backdrop: 'static'
            })
            .on('shown.bs.modal', function () {
                $('body').addClass('modal-open');
            })
            .find('[data-toggle="tooltip"]')
            .tooltip();
    }
};

Utils.handleMissingContainerError = function () {
    document.location.reload();
};

Utils.showPreloader = function (el) {
    if (el) {
        $(el).addClass('has-preloader loading');
    }
};

Utils.hidePreloader = function (el) {
    if (el) {
        $(el).removeClass('loading');
    }
};

Utils.hasActivePreloader = function (el) {
    return el && $(el).hasClass('has-preloader loading');
};

Utils.fadeOut = function (el, callback) {
    $(document.body).queue(function (next) {
        el.fadeOut(CmfConfig.contentChangeAnimationDurationMs, function () {
            if ($.isFunction(callback)) {
                callback(el);
            }
            next();
        });
    });
};

Utils.fadeIn = function (el, callback) {
    $(document.body).queue(function (next) {
        if ($.isFunction(callback)) {
            callback(el);
        }
        el.fadeIn(CmfConfig.contentChangeAnimationDurationMs, next);
    });
};

Utils.switchBodyClass = function (className, section, itemPk) {
    Utils.removeBodyClass();
    if (!!className) {
        className = Utils.normalizeBodyClass(className);
        $(document.body).addClass(className);
        Utils.bodyClass = className;
    }
    if (section) {
        $(document.body).attr('data-section', section);
    }
    if (itemPk) {
        $(document.body).attr('data-item-pk', itemPk);
    }
};

Utils.normalizeBodyClass = function (className) {
    return className.replace(/[^a-zA-Z0-9 ]+/g, '-');
};

Utils.getBodyClass = function () {
    return Utils.bodyClass;
};

Utils.getCurrentSectionName = function () {
    return $(document.body).attr('data-section');
};

Utils.removeBodyClass = function () {
    if (Utils.bodyClass) {
        $(document.body).removeClass(Utils.bodyClass);
        Utils.bodyClass = false;
    }
    $(document.body)
        .removeAttr('data-section')
        .removeAttr('data-item-pk')
        .find('> .tooltip')
            .remove(); //< remove hanged tooltips directly inside <body>
};

Utils.getPageWrapper = function () {
    var el = $('#page-wrapper');
    if (el.length) {
        Utils.getPageWrapper = function () {
            return el;
        };
        return el;
    }
    return false;
};

Utils.getContentContainer = function () {
    var el = $('#section-content');
    if (el.length) {
        Utils.getContentContainer = function () {
            return el;
        };
        return el;
    }
    return false;
};

Utils.getAvailableContentContainer = function () {
    var container = Utils.getContentContainer();
    if (!container) {
        container = Utils.getPageWrapper();
    }
    return container;
};

Utils.makeTemplateFromText = function (text, clarification) {
    var deferred = $.Deferred();
    try {
        var template = doT.template(text);
        deferred.resolve(function (data) {
            try {
                return template(data);
            } catch (exc) {
                var title = 'Failed to render doT.js template' + (!clarification ? '' : ' (' + clarification + ')');
                var content = '<h1>' + exc.name + ': ' + exc.message + '</h1><pre>' + exc.stack + '</pre>'
                    + '<h2>Template:</h2><pre>' + $('<div/>').text(text).html() + '</pre>'
                    + '<h2>Data:</h2><pre>' + JSON.stringify(data, null, '   ') + '</pre>';
                CmfConfig.getDebugDialog().showDebug(title, content);
                return '';
            }
        });
    } catch (exc) {
        var title = 'Failed to convert text into doT.js template' + (!clarification ? '' : ' (' + clarification + ')');
        var content = '<h1>' + exc.name + ': ' + exc.message + '</h1><pre>' + exc.stack + '</pre><h2>Template:</h2>';
        CmfConfig.getDebugDialog().showDebug(title, content + '<pre>' + $('<div/>').text(text).html() + '</pre>');
        deferred.reject(new Error(title));
    }
    return deferred.promise();
};

/**
 * @param {string} url
 * @param {bool?} cache - save to cache or not (default: no)
 * @param {bool?} isTemplate - is loaded HTML is a dotJS template or not (default: no)
 * @param {string?} urlQuery - attach this to page's url
 * @return {string|function} - depends on isTemplate argument
 */
Utils.downloadHtml = function (url, cache, isTemplate, urlQuery) {
    var deferred = $.Deferred();
    if (!url || !url.length) {
        console.warn('Empty url received in Utils.downloadHtml()');
        console.trace();
        deferred.reject(new Error('Empty url received in Utils.downloadHtml()'));
        return deferred.promise();
    }
    url = (url[0] === '/' || url.match(/^https?:/) !== null ? url : CmfConfig.rootUrl + '/' + url).replace(/\.html$/i, '') + '.html';
    if (typeof urlQuery === 'string' && urlQuery.length > 1) {
        url += (urlQuery[0] === '?') ? urlQuery : '?' + urlQuery;
    }
    if (!cache || !CmfCache.views[url]) {
        $.ajax({
            url: url,
            cache: false,
            dataType: 'html',
            method: 'GET'
        }).done(function (html) {
            if (isTemplate) {
                Utils.makeTemplateFromText(html, url)
                    .done(function (template) {
                        if (cache) {
                            CmfCache.views[url] = template;
                        }
                        deferred.resolve(template);
                    })
                    .fail(function (error) {
                        deferred.reject(error);
                    });
            } else {
                if (cache) {
                    CmfCache.views[url] = html;
                }
                deferred.resolve(html);
            }
        }).fail(function (xhr) {
            Utils.handleAjaxError.call(this, xhr, deferred);
        });
    } else {
        deferred.resolve(CmfCache.views[url]);
    }
    return deferred.promise();
};

Utils.userDataIsLoading = false;

Utils.getUser = function (reload) {
    var deferred = $.Deferred();
    if (
        !!reload
        || !CmfCache.user
        || Math.abs((new Date()).getMilliseconds() - CmfCache.userLastUpdateMs) > CmfConfig.userDataCacheTimeoutMs
    ) {
        $.ajax({
            url: CmfConfig.userDataUrl,
            cache: false,
            dataType: 'json',
            method: 'GET'
        }).done(function (json) {
            var user = Utils.setUser(json);
            deferred.resolve(user);
        }).fail(function (xhr) {
            Utils.handleAjaxError.call(this, xhr, deferred);
        });
    } else {
        deferred.resolve(CmfCache.user);
    }
    return deferred.promise();
};

Utils.setUser = function (userData) {
    CmfCache.user = userData;
    CmfCache.userLastUpdateMs = (new Date()).getMilliseconds();
    $(document).trigger('change:user', CmfCache.user);
    return CmfCache.user;
};

Utils.highlightLinks = function (request) {
    if (!request || !request.pathname || request.is_dialog) {
        return;
    }
    var url = request.pathname;
    $('li.current-page, a.current-page, li.treeview').removeClass('current-page active');
    var $links = $('a[href="' + url + '"], a[href="' + document.location.origin + url + '"]');
    if ($links.length) {
        $links.parent().filter('li').addClass('current-page active')
            .parent().filter('ul.treeview-menu').addClass('menu-open')
            .parent().filter('li.treeview').addClass('active');
        $links.not('li').find('> a').addClass('current-page active');
    }
    // in case when there is no active link in sidemenus - try to shorten url to resource/page name
    if (!$('.sidebar-menu li.current-page.active').length) {
        var parentUrl = url.replace(/(\/page|resource\/[^\/]+)(\/.+)$/, '$1');
        if (parentUrl.match(/(page|resource)$/) === null) {
            $links = $('.sidebar-menu a[href="' + parentUrl + '"], a[href="' + document.location.origin + parentUrl + '"]');
            if ($links.length) {
                $links.parent().filter('li').addClass('current-page active')
                    .parent().filter('ul.treeview-menu').addClass('menu-open')
                    .parent().filter('li.treeview').addClass('active');
                $links.not('li').find('> a').addClass('current-page active');
            }
        }
    }
};

Utils.cleanCache = function () {

};

Utils.updatePageTitleFromH1 = function ($content) {
    var $h1 = $content && $content.length ? $content.find('h1, .modal-header').first() : $('#section-content h1, h1').first();
    if ($h1.length) {
        var $pageTitle = $h1.find('.page-title, .modal-title');
        var pageTitleAddition = $.trim(String(CmfConfig.pageTitleAddition));
        document.title = ($pageTitle.length ? $pageTitle.text() : $h1.text()) + (pageTitleAddition.length ? ' - ' + pageTitleAddition : '');
    } else {
        document.title = $.trim(String(CmfConfig.defaultPageTitle));
    }
};

Utils.getTitleFromContent = function ($content) {
    var $h1 = $content.find('h1, .modal-header').first();
    if ($h1.length) {
        var $pageTitle = $h1.find('.page-title, .modal-title');
        return $pageTitle.length ? $pageTitle.text() : $h1.text();
    }
    return '';
};

Utils.initDebuggingTools = function () {
    if (CmfConfig.isDebug) {
        $(document.body).addClass('debug');
        var $opener = $('<button type="button" class="btn btn-xs btn-default">&nbsp;</button>')
            .css({
                width: '14px',
                cursor: 'default',
                'background-color': 'transparent',
                border: '0 none',
                margin: '0',
                position: 'absolute',
                top: '0',
                right: '0',
                opacity: '0',
                'z-index': 50
            })
            .on('click', function () {
                $buttons.toggle();
                if (Modernizr.localstorage) {
                    localStorage.setItem('debug-tools-opened', $buttons.height() > 0);
                }
            });
        var $buttons = $('<div class="btn-group" role="group"></div>')
            .css({
                position: 'absolute',
                top: '0',
                right: '14px',
                width: '192px'
            })
            .hide();
        if (
            Modernizr.localstorage
            && (
                localStorage.getItem('debug-tools-opened')
                || localStorage.getItem('debug-tools-templates-cache-disabled')
                || localStorage.getItem('debug-tools-js-files-cache-disabled')
            )
        ) {
            $buttons.show();
        }
        var $container = $('<div id="debug-tools"></div>')
            .css({
                position: 'absolute',
                top: '-4px',
                right: '0',
                overflow: 'visible',
                'z-index': 10
            })
            .append($opener)
            .append($buttons);

        $(document.body).append($container);

        // templates cache
        var $disableTplCacheBtn = $('<button type="button" class="btn btn-xs btn-default">Tpl cache: on</button>');
        $buttons.append($disableTplCacheBtn);
        if (Modernizr.localstorage) {
            ScaffoldsManager.cacheTemplates = localStorage.getItem('debug-tools-templates-cache') !== 'off';
        }
        if (!ScaffoldsManager.cacheTemplates) {
            $disableTplCacheBtn.addClass('btn-danger').removeClass('btn-default').text('Tpl cache: off');
        }
        $disableTplCacheBtn.on('click', function () {
            ScaffoldsManager.cacheTemplates = !ScaffoldsManager.cacheTemplates;
            var labelSuffix = ScaffoldsManager.cacheTemplates ? 'on' : 'off';
            $disableTplCacheBtn
                .addClass(ScaffoldsManager.cacheTemplates ? 'btn-default' : 'btn-danger')
                .removeClass(ScaffoldsManager.cacheTemplates ? 'btn-danger' : 'btn-default')
                .text('Tpl cache: ' + labelSuffix);
            if (Modernizr.localstorage) {
                localStorage.setItem('debug-tools-templates-cache', labelSuffix);
            }
        });
        // js files cache
        var $disableJsFilesCacheBtn = $('<button type="button" class="btn btn-xs btn-default">JS files cache: on</button>');
        $buttons.append($disableJsFilesCacheBtn);
        if (Modernizr.localstorage) {
            Utils.cacheLoadedJsFiles = localStorage.getItem('debug-tools-js-files-cache') !== 'off';
        }
        if (!Utils.cacheLoadedJsFiles) {
            $disableJsFilesCacheBtn.addClass('btn-danger').removeClass('btn-default').text('JS files cache: off');
        }
        $disableJsFilesCacheBtn.on('click', function () {
            Utils.cacheLoadedJsFiles = !Utils.cacheLoadedJsFiles;
            var labelSuffix = Utils.cacheLoadedJsFiles ? 'on' : 'off';
            $disableJsFilesCacheBtn
                .addClass(Utils.cacheLoadedJsFiles ? 'btn-default' : 'btn-danger')
                .removeClass(Utils.cacheLoadedJsFiles ? 'btn-danger' : 'btn-default')
                .text('JS files cache: ' + labelSuffix);
            if (Modernizr.localstorage) {
                localStorage.setItem('debug-tools-js-files-cache', labelSuffix);
            }
        });
    }
};

/**
 *
 * @param {String} title
 * @param {String} content
 * @param {String?} footer
 * @param {String?} size - 'large' or 'small'. Default: 'medium'
 * @param {String?} id - modal ID
 * @return {jQuery}
 */
Utils.makeModal = function (title, content, footer, size, id) {
    if (!Utils.modalTpl) {
        Utils.modalTpl = doT.template($('#modal-template').html());
    }
    Utils.modalsCount++;
    var tplData = {
        id: id || ('cmf-custom-modal-' + Utils.modalsCount),
        title: title,
        content: content,
        footer: footer,
        size: size
    };
    return $(Utils.modalTpl(tplData));
};

Utils.copyToClipboardFrom = function (element, message) {
    var $el = $(element);
    if (!$el.length) {
        return;
    }
    var range = document.createRange();
    range.selectNodeContents($el[0]);
    var sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    document.execCommand('copy');
    sel.removeAllRanges();
    if (message && message.length) {
        toastr.info(message.replace(/:text/g, $el.text()));
    }
    return false;
};
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
                    api.ajax.reload(null, false);
                    break;
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
                                                    tplData.code = tplData.code.replace(':' + argName, data[argName].replace(/(['"])/g, '\\$1'));
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
var CmfFileUploads = {
    baseUploaderOptions: {
        language: $(document.body).attr('data-locale'),
        validateInitialCount: true,
        showUpload: false,
        allowedFileTypes: ['image', 'text', 'video', 'audio', 'object'],
        allowedPreviewTypes: ['image', 'video', 'audio'],
        previewFileType: 'any',
        previewFileIcon: '<i class="fa fa-file"></i>',
        initialPreviewAsData: true,
        overwriteInitial: true,
        fileActionSettings: {
            showDrag: false,
            showDownload: true
        },
        browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
        layoutTemplates: {
            main1: "{preview}\n" +
                "<div class=\'input-group {class}\'>\n" +
                "   <div class=\'input-group-btn\'>\n" +
                "       {browse}\n" +
                "       {upload}\n" +
                "       {remove}\n" +
                "   </div>\n" +
                "   {caption}\n" +
                "</div>"
        }
    },
    imageUploaderOptions: {
        allowedFileTypes: ['image'],
        allowedPreviewTypes: ['image'],
        previewFileType: 'image',
        initialPreviewFileType: 'image'
    },
    fileUploaderOptions: {
        fileActionSettings: {
            showDrag: false,
            showDownload: true,
            showBrowse: false
        },
        initialPreviewFileType: 'object',
        previewFileIconSettings: {
            'doc': '<i class="fa fa-file-word-o text-primary"></i>',
            'docx': '<i class="fa fa-file-word-o text-primary"></i>',
            'txt': '<i class="fa fa-file-text-o"></i>',
            'json': '<i class="fa fa-file-text-o"></i>',
            'js': '<i class="fa fa-file-text-o"></i>',
            'rtf': '<i class="fa fa-file-text-o"></i>',
            'xls': '<i class="fa fa-file-excel-o text-success"></i>',
            'xlsx': '<i class="fa fa-file-excel-o text-success"></i>',
            'csv': '<i class="fa fa-file-excel-o text-success"></i>',
            'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pptx': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
            'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'gzip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'rar': '<i class="fa fa-file-archive-o text-muted"></i>'
        }
    }
};

CmfFileUploads.initFileUploaders = function (data, isImages, additionalPluginOptions) {
    for (var filesGroupName in data.configs) {
        CmfFileUploads.initFileUploader(data, filesGroupName, isImages, additionalPluginOptions);
    }
};

CmfFileUploads.initFileUploader = function (data, filesGroupName, isImages, additionalPluginOptions) {
    var fileConfig = data.configs[filesGroupName];
    var isSingleFile = fileConfig.max_files_count === 1;
    fileConfig.defaultPluginOptions = $.extend(
        {layoutTemplates: {}},
        CmfFileUploads.baseUploaderOptions,
        isImages ? CmfFileUploads.imageUploaderOptions : CmfFileUploads.fileUploaderOptions,
        ($.isPlainObject(additionalPluginOptions) ? additionalPluginOptions : {}),
        {
            allowedFileExtensions: fileConfig.allowed_extensions,
            minFileCount: 0,
            maxFileCount: 1,
            maxFileSize: fileConfig.max_file_size,
            showCaption: isSingleFile,
            previewClass: (isSingleFile ? 'single-file-upload' : 'multi-file-upload')
        }
    );
    if (!isSingleFile) {
        fileConfig.defaultPluginOptions.layoutTemplates.main2 =
            '<button class="fileinput-dragger" type="button"><span class="fa fa-arrows"></span></button>' +
            '{preview}\n<div class="kv-upload-progress kv-hidden"></div>\n' +
            '<div class="clearfix"></div>\n' +
            '<div class="kv-upload-toolbar text-center">{remove}\n{cancel}\n{upload}\n{browse}\n</div>';

        fileConfig.defaultPluginOptions.layoutTemplates.preview =
            '<div class="file-preview {class}">\n' +
            '    {close}' +
            '    <div class="no-file">' + CmfConfig.getLocalizationStringsForComponent('file_uploader').no_file + '</div>' +
            '    <div class="{dropClass}">\n' +
            '    <div class="file-preview-thumbnails">\n' +
            '    </div>\n' +
            '    <div class="clearfix"></div>' +
            '    <div class="file-preview-status text-center text-success"></div>\n' +
            '    <div class="kv-fileinput-error"></div>\n' +
            '    </div>\n' +
            '</div>';
    }
    fileConfig.inputsAdded = 0;
    fileConfig.isCloning = !!data.is_cloning;
    fileConfig.isInModal = !!data.is_in_modal;

    Utils.makeTemplateFromText(
            $('#' + fileConfig.id + '-tpl').html(),
            'CmfFileUploads.initFileUploader for files group ' + filesGroupName
        )
        .done(function (inputTemplate) {
            fileConfig.inputTpl = inputTemplate;
            fileConfig.addInput = function (pluginOptions, existingFileData) {
                return CmfFileUploads.initFileUploaderInput(fileConfig, pluginOptions, existingFileData);
            };
            $('#' + fileConfig.id + '-add')
                .on('click', function () {
                    fileConfig.addInput();
                    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
                        $(this).remove();
                    }
                    return false;
                });
            // show existing files
            if (
                data.files
                && $.isPlainObject(data.files)
                && data.files.urls
                && data.files.urls[filesGroupName]
                && data.files.preview_info
                && data.files.preview_info[filesGroupName]
                && data.files.files
                && data.files.files[filesGroupName]
            ) {
                var existingFilesUrls = data.files.urls[filesGroupName];
                var existingFilesPreviewsInfo = data.files.preview_info[filesGroupName];
                var existingFiles = data.files.files[filesGroupName];
                if ($.isArray(existingFilesUrls) && $.isArray(existingFilesPreviewsInfo) && $.isArray(existingFiles)) {
                    for (var i = 0; i < existingFilesUrls.length; i++) {
                        fileConfig.addInput(
                            {
                                initialPreview: [existingFilesUrls[i]],
                                initialPreviewConfig: [existingFilesPreviewsInfo[i]],
                                initialCaption: [existingFilesPreviewsInfo[i].caption]
                            },
                            $.extend(
                                {is_cloning: fileConfig.isCloning},
                                existingFiles[i]
                            )
                        )
                    }
                }
            }
            // add empty inputs
            if (fileConfig.inputsAdded === 0 && (isSingleFile || fileConfig.min_files_count > 0)) {
                // add at least 1 input
                fileConfig.addInput();
            }
            // add required amount of inputs
            for (var k = fileConfig.inputsAdded; k < fileConfig.min_files_count; k++) {
                fileConfig.addInput();
            }
            if (!isSingleFile) {
                var container = $('#' + fileConfig.id + '-container');
                container.data('sortable', Sortable.create(
                    container[0],
                    {
                        handle: '.fileinput-dragger',
                        draggable: '.file-upload-input-container',
                        animation: 200,
                        forceFallback: true,
                        onUpdate: function (event) {
                            $(event.to).find('.file-upload-input-container').each(function (index, item) {
                                $(item).find('input[name$="][position]"]').val(String(index + 1));
                                $(item).find('input[name]').each(function (_, input) {
                                    input.name = String(input.name).replace(/\]\[[0-9]\]\[/, '][' + String(index) + '][');
                                })
                            });
                        }
                    }
                ));
            }
        });
};

CmfFileUploads.initFileUploaderInput = function (fileConfig, pluginOptions, existingFileData) {
    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
        return false;
    }
    if (!$.isPlainObject(existingFileData)) {
        existingFileData = {};
    }
    existingFileData = $.extend({index: fileConfig.inputsAdded}, existingFileData);
    var $renderedTemplate = $(fileConfig.inputTpl(existingFileData));
    $('#' + fileConfig.id + '-container').append($renderedTemplate);
    fileConfig.inputsAdded++;
    var $fileInput = $renderedTemplate.find('input[type="file"]');
    var options = $.extend(
        {},
        fileConfig.defaultPluginOptions,
        $.isPlainObject(pluginOptions) ? pluginOptions : {}
    );

    if (existingFileData.is_cloning && existingFileData.url) {
        var xhr = new XMLHttpRequest();
        xhr.onload = function(){
            var reader = new FileReader();
            reader.onloadend = function() {
                $('#' + $fileInput[0].id + '-file-data').val(JSON.stringify($.extend({data: reader.result}, existingFileData)));
            };
            reader.readAsDataURL(xhr.response);
        };
        xhr.open('GET', existingFileData.url);
        xhr.responseType = 'blob';
        xhr.send();
    }
    $fileInput
        .fileinput(options)
        .on('fileclear', function() {
            $('#' + this.id + '-deleted').val('1');
            $('#' + this.id + '-file-data').remove();
            var sortable = $('#' + fileConfig.id + '-container').data('sortable');
            if (sortable) {
                var order = sortable.toArray();
                var index = $.inArray(this.id, order);
                if (index >= 0) {
                    order.splice(index, 1);
                    order.push(this.id);
                    sortable.sort(order);
                }
            }
        })
        .on('change', function () {
            $('#' + this.id + '-file-data').remove();
        })
        .on('filezoomhidden', function (event, params) {
            params.modal.remove();
            if (fileConfig.isInModal) {
                $('body').addClass('modal-open');
                $('.modal.in').css('padding-left', '17px');
            }
        });
};