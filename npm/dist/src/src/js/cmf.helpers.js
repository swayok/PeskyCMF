
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