

var FormHelper = {
    messageAnimDurationMs: 200
};

FormHelper.initForm = function (form, container, onSubmitSuccess) {
    form = $(form);
    container = $(container);

    form.ajaxForm({
        clearForm: true,
        dataType: 'json',
        beforeSubmit: function () {
            FormHelper.removeAllFormMessagesAndErrors(form);
            Utils.showPreloader(container);
        },
        error: function (xhr) {
            Utils.hidePreloader(container);
            FormHelper.handleAjaxErrors(form, xhr);
        },
        success: function (json) {
            if ($.isFunction(onSubmitSuccess)) {
                onSubmitSuccess(json, form, container);
            } else {
                Utils.hidePreloader(container);
                Utils.handleAjaxSuccess(json);
            }
        }
    });
};

FormHelper.removeAllFormMessagesAndErrors = function (form) {
    return $.when(FormHelper.removeFormMessage(form), FormHelper.removeFormValidationMessages(form));
};

FormHelper.setFormMessage = function (form, message, type) {
    if (!type) {
        type = 'error';
    }
    toastr[type](message);
    /*
    var errorDiv = form.find('.form-error');
    if (!errorDiv.length) {
        errorDiv = $('<div class="form-error text-center"></div>').hide();
        form.prepend(errorDiv);
    }
    return errorDiv.slideUp(100, function () {
        errorDiv.html('<div class="alert alert-' + type + '">' + message + '</div>').slideDown(100);
    });*/
};

FormHelper.removeFormMessage = function (form) {
    /*var errorDiv = form.find('.form-error');
    return errorDiv.slideUp(100, function () {
        errorDiv.html('');
    })*/
};

FormHelper.removeFormValidationMessages = function (form) {
    form.find('.has-error').removeClass('has-error');
    return form.find('.error-text').slideUp(FormHelper.messageAnimDurationMs, function () {
        $(this).html('');
    });
};

FormHelper.handleAjaxErrors = function (form, xhr) {
    FormHelper.removeAllFormMessagesAndErrors(form).done(function () {
        if (xhr.status === 400 && xhr.responseText[0] === '{') {
            try {
                var response = JSON.parse(xhr.responseText);
            } catch (exc) {
                Utils.handleAjaxError(xhr);
                return;
            }
            if (response._message) {
                FormHelper.setFormMessage(form, response._message);
            }
            if (response.errors && $.isPlainObject(response.errors)) {
                for (var inputName in response.errors) {
                    if (form[0][inputName]) {
                        var container = $(form[0][inputName]).closest('.form-group, .checkbox').addClass('has-error');
                        var errorEl = container.find('.error-text');
                        if (errorEl.length == 0) {
                            errorEl = $('<div class="error-text bg-danger"></div>').hide();
                            container.append(errorEl);
                        }
                        errorEl.html(response.errors[inputName]).slideDown(FormHelper.messageAnimDurationMs);
                    }
                }
            }
            return;
        }
        Utils.handleAjaxError(xhr);
    });
};

var AdminUI = {
    $el: null,
    visible: false,
    userInfoTplSelector: '#user-panel-tpl',
    userInfoTpl: null,
    userInfoContainer: '#user-panel .info'
};

AdminUI.destroyUI = function () {
    var deferred = $.Deferred();
    var wrapper = Utils.getPageWrapper();
    wrapper.fadeOut(GlobalVars.contentChangeAnimationDurationMs, function () {
        Utils.showPreloader(wrapper);
        if (AdminUI.$el) {
            AdminUI.$el.detach();
        }
        wrapper.removeClass('with-ui').empty();
        wrapper.show();
        AdminUI.visible = false;
        deferred.resolve();
        $(document).trigger('appui:hidden');
    });
    return deferred;
};

AdminUI.showUI = function () {
    var deferred = $.Deferred();
    var wrapper = Utils.getPageWrapper();
    if (AdminUI.visible) {
        deferred.resolve();
    } else {
        Utils.showPreloader(wrapper);
        $.when(
            AdminUI.loadUI(),
            wrapper.fadeOut(GlobalVars.contentChangeAnimationDurationMs)
        ).done(function (uiEl) {
            wrapper.addClass('with-ui').empty().append(uiEl[0]);
            AdminUI.visible = true;
            AdminUI.updateUserInfo();
            wrapper.fadeIn(GlobalVars.contentChangeAnimationDurationMs);
            Utils.hidePreloader(wrapper);
            Utils.highlightLinks(window.adminApp.request.path);
            deferred.resolve();
            $(document).trigger('appui:shown');
        });
    }
    return deferred;
};

AdminUI.loadUI = function () {
    var deferred = $.Deferred();
    if (!AdminUI.$el) {
        Utils.downloadHtml(GlobalVars.uiUrl, true, false)
            .done(function (html) {
                AdminUI.$el = $('<div class="ui-container">' + html + '</div>');
                deferred.resolve(AdminUI.$el);
                $(document).trigger('appui:loaded');
            });
    } else {
        deferred.resolve(AdminUI.$el);
    }
    return deferred;
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
        AdminUI.userInfoTpl = html = Utils.makeTemplateFromText($(AdminUI.userInfoTplSelector).html(), 'User Info block template');
        container.addClass('fading fade-out').width();
        $(document).on('change:user', function (event, userInfo) {
            AdminUI.updateUserInfo(userInfo);
        });
    }
    container.html(AdminUI.userInfoTpl(userInfo)).removeClass('fade-out');
};