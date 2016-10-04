var Utils = {
    bodyClass: false,
    loadedJsFiles: []
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
    toastr.options = GlobalVars.toastrOptions;
};

Utils.configureAjax = function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    if (GlobalVars.isDebug) {
        $(document).ajaxComplete(function (event, xhr, settings) {
            var request = Pilot.parseURL(settings.url);
            console.group('Ajax: %s %s', settings.type || 'GET', request.path, xhr.status, xhr.statusText);
            if (request.query.length) {
                console.groupCollapsed('GET');
                console.log(request.query);
                console.groupEnd();
            }
            if (settings.type === 'POST') {
                console.groupCollapsed('POST');
                if ($.isPlainObject(settings.data) || $.isArray(settings.data)) {
                    console.log(settings.data);
                } else {
                    console.log(Pilot.parseURL('/?' + settings.data).query);
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
            if ($.inArray(jsFiles[i], Utils.loadedJsFiles) >= 0 || $('script[src="' + jsFiles[i] + '"]').length) {
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
                    if (this.url) {
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
    GlobalVars.getDebugDialog().showDebug(
        'HTTP Error ' + xhr.status + ' ' + xhr.statusText,
        xhr.responseText
    );
};

Utils.handleAjaxSuccess = function (json) {
    try {
        if (json.redirect) {
            switch (json.redirect) {
                case 'back':
                    ScaffoldsManager.app.back(json.redirect_fallback);
                    break;
                case 'reload':
                    ScaffoldsManager.app.reload();
                    break;
                default:
                    ScaffoldsManager.app.nav(json.redirect);
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

Utils.fadeOut = function (el, callback) {
    $(document.body).queue(function (next) {
        el.fadeOut(GlobalVars.contentChangeAnimationDurationMs, function () {
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
        el.fadeIn(GlobalVars.contentChangeAnimationDurationMs, next);
    });
};

Utils.switchBodyClass = function (className) {
    Utils.removeBodyClass();
    if (!!className) {
        className = className.replace(/[^a-zA-Z0-9]+/, '-');
        $(document.body).addClass(className);
        Utils.bodyClass = className;
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
        GlobalVars.getDebugDialog().showDebug(title, content + '<pre>' + $('<div/>').text(text).html() + '</pre>');
        return '';
    }
};

Utils.downloadHtml = function (url, cache, template) {
    var deferred = $.Deferred();
    url = (url[0] === '/' ? url : GlobalVars.rootUrl + '/' + url).replace(/\.html$/i, '') + '.html';
    if (!cache || !Cache.views[url]) {
        return $.ajax({
            url: url,
            cache: false,
            dataType: 'html',
            method: 'GET'
        }).done(function (html) {
            if (template) {
                html = Utils.makeTemplateFromText(html, url);
            }
            if (cache) {
                Cache.views[url] = html;
            }
            deferred.resolve(html);
        }).fail(Utils.handleAjaxError);
    } else {
        deferred.resolve(Cache.views[url]);
        return deferred;
    }
};

Utils.getUser = function (reload) {
    var deferred = $.Deferred();
    if (
        !!reload
        || !Cache.user
        || Math.abs((new Date()).getMilliseconds() - Cache.userLastUpdateMs) > GlobalVars.userDataCacheTimeoutMs
    ) {
        $.ajax({
            url: GlobalVars.rootUrl + '/' + GlobalVars.userDataUrl,
            cache: false,
            dataType: 'json',
            method: 'GET'
        }).done(function (json) {
            var user = Utils.setUser(json);
            deferred.resolve(user);
        }).fail(Utils.handleAjaxError);
    } else {
        deferred.resolve(Cache.user);
    }
    return deferred;
};

Utils.setUser = function (userData) {
    Cache.user = userData;
    Cache.userLastUpdateMs = (new Date()).getMilliseconds();
    $(document).trigger('change:user', Cache.user);
    return Cache.user;
};

Utils.highlightLinks = function (url) {
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
    var defaultPageTitle = $.trim(String(GlobalVars.defaultPageTitle));
    if ($h1.length) {
        var $pageTitle = $h1.find('.page-title');
        document.title = ($pageTitle.length ? $pageTitle.text() : $h1.text()) + (defaultPageTitle.length ? ' - ' + defaultPageTitle : '');
    } else {
        document.title = defaultPageTitle;
    }
};

Utils.initDebuggingTools = function () {
    if (GlobalVars.isDebug) {
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
                right: '14px'
            })
            .hide();
        if (
            Modernizr.localstorage
            && (
                localStorage.getItem('debug-tools-opened')
                || localStorage.getItem('debug-tools-templates-cache-disabled')
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
    }
};