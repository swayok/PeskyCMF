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
    CmfConfig.getDebugDialog().showDebug(
        'HTTP Error ' + xhr.status + ' ' + xhr.statusText,
        xhr.responseText
    );
};

Utils.handleAjaxSuccess = function (json) {
    try {
        if (json.redirect_with_reload) {
            document.location = json.redirect_with_reload;
        } else if (json.redirect) {
            switch (json.redirect) {
                case 'back':
                    page.back(json.redirect_fallback);
                    break;
                case 'reload':
                    page.reload();
                    break;
                default:
                    page.show(json.redirect, null, true, true, {env: {is_ajax_response: true}});
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
    if (json.modal && $.isPlainObject(json.modal) && json.modal.content) {
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
 * @return {jQuery}
 */
Utils.makeModal = function (title, content, footer, size) {
    if (!Utils.modalTpl) {
        Utils.modalTpl = doT.template($('#modal-template').html());
    }
    Utils.modalsCount++;
    var tplData = {
        id: 'cmf-custom-modal-' + Utils.modalsCount,
        title: title,
        content: content,
        footer: footer,
        size: size
    };
    return $(Utils.modalTpl(tplData));
};