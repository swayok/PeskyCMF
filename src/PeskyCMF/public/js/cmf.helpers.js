
var FormHelper = {
    messageAnimDurationMs: 200
};

FormHelper.initForm = function (form, container, onSubmitSuccess, options) {
    var $form = $(form);
    var $container = $(container);
    if (!options) {
        options = {}
    }
    options = $.extend({}, {
        isJson: true,
        clearForm: true,
        onValidationErrors: null,
        beforeSubmit: null
    }, options);
    var customInitiator = $form.attr('data-initiator');
    if (customInitiator) {
        var ret = null;
        if (customInitiator.match(/^[a-zA-Z0-9_.$()\[\]]+$/) !== null) {
            eval('customInitiator = ' + customInitiator);
            if (typeof customInitiator === 'function') {
                ret = customInitiator.call(form, form, container, onSubmitSuccess);
            }
        }
        if (ret === false) {
            return;
        }
    }
    // init plugins
    $form
        .find('.selectpicker')
        .each(function () {
            // somehow it is loosing value that was set by $('select').val('val');
            var val = $(this).val();
            $(this).selectpicker().selectpicker('val', val);
        });
    $form
        .find('input.switch[type="checkbox"]')
        .bootstrapSwitch();
    // input masks
    $form
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
    $form
        .find('input[data-mask], textarea[data-mask]')
        .each(function () {
            $(this)
                .attr('data-inputmask-mask', $(this).attr('data-mask'))
                .removeAttr('data-mask')
                .inputmask();
            $(this).val($(this).val());
        });
    $form
        .find('input[data-regexp], textarea[data-regexp]')
        .each(function () {
            $(this)
                .attr({
                    'data-inputmask-alias': 'Regex',
                    'data-inputmask-regex': $(this).attr('data-regexp')
                })
                .removeAttr('data-regexp')
                .inputmask();
            $(this).val($(this).val());
        });
    $form
        .find('input[data-inputmask], textarea[data-inputmask]')
        .each(function () {
            $(this).inputmask();
            $(this).val($(this).val());
        });
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
            if (xhr.status === 400 && typeof options.onValidationErrors === 'function') {
                options.onValidationErrors(xhr, $form, $container);
            } else {
                FormHelper.handleAjaxErrors($form, xhr);
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
        }
    });
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

FormHelper.handleAjaxErrors = function ($form, xhr) {
    FormHelper.removeAllFormMessagesAndErrors($form)
        .done(function () {
            if (xhr.status === 400) {
                var response = Utils.convertXhrResponseToJsonIfPossible(xhr);
                if (!response) {
                    Utils.handleAjaxError(xhr);
                    return;
                }
                if (response._message) {
                    FormHelper.setFormMessage($form, response._message);
                }
                if (response.errors && $.isPlainObject(response.errors)) {
                    for (var inputName in response.errors) {
                        FormHelper.showErrorForInput($form, inputName, response.errors[inputName]);
                    }
                }
                return;
            }
            Utils.handleAjaxError(xhr);
        });
};

FormHelper.showErrorForInput = function ($form, inputName, message) {
    if ($form[0][inputName]) {
        var $container = $($form[0][inputName]).closest('.form-group, .checkbox').addClass('has-error');
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
        $errorEl.html(message).slideDown(FormHelper.messageAnimDurationMs);
        // mark current tab that it has an error inside
        var $tabs = $form.find('.nav-tabs');
        if ($tabs.length) {
            var tabId = $container.closest('.tab-pane').attr('id');
            if (tabId) {
                $tabs.find('a[href="#' + tabId + '"]').parent('li').addClass('has-error');
            }
        }
    }
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