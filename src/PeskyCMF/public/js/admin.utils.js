var Utils = {
    bodyClass: false
};

Utils.configureAppLibs = function () {
    Utils.configureAjax();
    Utils.configureToastr();
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
            if (xhr.responseText[0] === '{') {
                try {
                    var json = JSON.parse(xhr.responseText);
                    console.log(json);
                } catch (exc) {}
            }
            if (!json) {
                console.log(xhr.responseText);
            }
            console.groupEnd();
            console.groupEnd();
        });
    }
};

Utils.handleAjaxError = function (xhr) {
    if (xhr.responseText[0] === '{') {
        try {
            var json = JSON.parse(xhr.responseText);
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
        } catch (e) {}
    }
    if (GlobalVars.isDebug) {
        GlobalVars.debugDialog.showDebug(
            xhr.status + ' ' + xhr.statusText,
            xhr.responseText
        );
    } else {
        toastr.error('HTTP Error ' + xhr.status + ': ' + xhr.statusText);
    }
};

Utils.handleAjaxSuccess = function (json) {
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
    if (json._message) {
        toastr.success(json._message);
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
        GlobalVars.debugDialog.showDebug(title, content + '<pre>' + $('<div/>').text(text).html() + '</pre>');
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
    var links = $('a[href="' + url + '"]');
    links.parent().filter('li').addClass('current-page active')
        .parent().filter('ul.treeview-menu').addClass('menu-open')
        .parent().filter('li.treeview').addClass('active');
    links.not('li').find('> a').addClass('current-page active');
};

Utils.cleanCache = function () {

};