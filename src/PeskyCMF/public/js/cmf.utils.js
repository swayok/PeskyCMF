var Utils = {
    bodyClass: false,
    loadedJsFiles: [],
    cacheLoadedJsFiles: true
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
            console.group('Ajax: %s %s', settings.type || 'GET', location.pathname, xhr.status, xhr.statusText);
            if (location.search.length > 1) {
                console.groupCollapsed('GET');
                console.log(qs.parse(location.search.slice(1)));
                console.groupEnd();
            }
            if (settings.type === 'POST') {
                console.groupCollapsed('POST');
                if ($.isPlainObject(settings.data) || $.isArray(settings.data)) {
                    console.log(settings.data);
                } else {
                    console.log(qs.parse(settings.data));
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
            if (
                Utils.cacheLoadedJsFiles
                && (
                    $.inArray(jsFiles[i], Utils.loadedJsFiles) >= 0
                    || $('script[src="' + jsFiles[i] + '"]').length
                )
            ) {
                loadedJsFiles++;
                if (jsFiles.length === loadedJsFiles) {
                    deferred.resolve();
                }
                continue;
            }
            $.getScript(jsFiles[i])
                .done(function (data, textStatus, jqxhr) {
                    loadedJsFiles++;
                    if (jsFiles.length === loadedJsFiles) {
                        deferred.resolve();
                    }
                    if (this.url && Utils.cacheLoadedJsFiles) {
                        Utils.loadedJsFiles.push(this.url.replace(/(\?|&)_=[0-9]+/, ''));
                    }
                })
                .fail(function (jqxhr, settings, exception) {
                    alert('Failed to load js file ' + this.url + '. Error: ' + exception);
                    deferred.reject();
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
                if (typeof jsFiles[i] !== 'string') {
                    alert('cssFiles argument in Utils.requireFiles() must contain only strings. Not a string detected in index ' + i);
                }
                if ($('link[href="' + jsFiles[i] + '"]').length) {
                    continue;
                }
                if (document.createStyleSheet) {
                    document.createStyleSheet(cssFiles[i]);
                } else {
                    $('body').before(
                        $('<link rel="stylesheet" href="' + cssFiles[i] + '" type="text/css" />')
                    );
                }
            }
        }
    }
    return deferred;
};

Utils.handleAjaxError = function (xhr) {
    var json = Utils.convertXhrResponseToJsonIfPossible(xhr);
    if (json) {
        if (json.redirect_with_reload) {
            document.location = json.redirect_with_reload;
        } else if (json.redirect) {
            if (json.redirect === 'back') {
                window.adminApp.back(json.redirect_fallback);
            } else if (json.redirect[0] === '/') {
                window.adminApp.nav(json.redirect);
            } else {
                document.location = json.redirect;
            }
        }
        if (json._message) {
            toastr.error(json._message);
        }
        if (json.redirect || json.redirect_with_reload || json._message) {
            return;
        }
    }
    CmfConfig.getDebugDialog().showDebug(
        'HTTP Error ' + xhr.status + ' ' + xhr.statusText,
        xhr.responseText
    );
};

Utils.handleAjaxSuccess = function (json) {
    try {
        if (json.redirect) {
            switch (json.redirect) {
                case 'back':
                    page.back(json.redirect_fallback);
                    break;
                case 'reload':
                    page();
                    break;
                default:
                    page.show(json.redirect);
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
};

Utils.handleMissingContainerError = function () {
    document.location.reload();
};

Utils.showPreloader = function (el) {
    $(el).addClass('has-preloader loading');
};

Utils.hidePreloader = function (el) {
    $(el).removeClass('loading');
};

Utils.hasActivePreloader = function (el) {
    return $(el).hasClass('has-preloader loading');
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
        className = className.replace(/[^a-zA-Z0-9 ]+/g, '-');
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

Utils.getBodyClass = function () {
    return Utils.bodyClass;
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
    try {
        return doT.template(text);
    } catch (exc) {
        var title = 'Failed to convert text into doT.js template' + (!clarification ? '' : ' (' + clarification + ')');
        var content = '<h1>' + exc.name + ': ' + exc.message + '</h1><pre>' + exc.stack + '</pre><h2>Template:</h2>';
        CmfConfig.getDebugDialog().showDebug(title, content + '<pre>' + $('<div/>').text(text).html() + '</pre>');
        return '';
    }
};

Utils.downloadHtml = function (url, cache, template) {
    var deferred = $.Deferred();
    url = (url[0] === '/' ? url : CmfConfig.rootUrl + '/' + url).replace(/\.html$/i, '') + '.html';
    if (!cache || !CmfCache.views[url]) {
        $.ajax({
            url: url,
            cache: false,
            dataType: 'html',
            method: 'GET'
        }).done(function (html) {
            if (template) {
                html = Utils.makeTemplateFromText(html, url);
            }
            if (cache) {
                CmfCache.views[url] = html;
            }
            deferred.resolve(html);
        }).fail(function (xhr) {
            Utils.handleAjaxError(xhr);
            deferred.reject(xhr);
        });
    } else {
        deferred.resolve(CmfCache.views[url]);
    }
    return deferred;
};

Utils.getUser = function (reload) {
    var deferred = $.Deferred();
    if (
        !!reload
        || !CmfCache.user
        || Math.abs((new Date()).getMilliseconds() - CmfCache.userLastUpdateMs) > CmfConfig.userDataCacheTimeoutMs
    ) {
        $.ajax({
            url: CmfConfig.rootUrl + '/' + CmfConfig.userDataUrl,
            cache: false,
            dataType: 'json',
            method: 'GET'
        }).done(function (json) {
            var user = Utils.setUser(json);
            deferred.resolve(user);
        }).fail(Utils.handleAjaxError);
    } else {
        deferred.resolve(CmfCache.user);
    }
    return deferred;
};

Utils.setUser = function (userData) {
    CmfCache.user = userData;
    CmfCache.userLastUpdateMs = (new Date()).getMilliseconds();
    $(document).trigger('change:user', CmfCache.user);
    return CmfCache.user;
};

Utils.highlightLinks = function (url) {
    if (typeof url !== 'string') {
        return;
    }
    $('li.current-page, a.current-page, li.treeview').removeClass('current-page active');
    var $links = $('a[href="' + url + '"], a[href="' + document.location.origin + url + '"]');
    if ($links.length) {
        $links.parent().filter('li').addClass('current-page active')
            .parent().filter('ul.treeview-menu').addClass('menu-open')
            .parent().filter('li.treeview').addClass('active');
        $links.not('li').find('> a').addClass('current-page active');
    }
    // in case when there is no active link in sidemenus - try to shorten url by 1 section and search for matches again
    if (!$('.sidebar-menu li.current-page.active').length) {
        var parentUrl = url.replace(/\/[^\/]+$/, '');
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
    var $h1 = $content && $content.length ? $content.find('h1').first() : $('#section-content h1, h1').first();
    var defaultPageTitle = $.trim(String(CmfConfig.defaultPageTitle));
    if ($h1.length) {
        var $pageTitle = $h1.find('.page-title');
        document.title = ($pageTitle.length ? $pageTitle.text() : $h1.text()) + (defaultPageTitle.length ? ' - ' + defaultPageTitle : '');
    } else {
        document.title = defaultPageTitle;
    }
};

Utils.initDebuggingTools = function () {
    if (CmfConfig.isDebug) {
        var $opener = $('<button type="button" class="btn btn-xs btn-default">&nbsp;</button>')
            .fadeOut()
            .css({
                width: '14px',
                cursor: 'default',
                'background-color': 'transparent',
                border: '0 none',
                margin: '0',
                position: 'absolute',
                top: '0',
                right: '0'
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