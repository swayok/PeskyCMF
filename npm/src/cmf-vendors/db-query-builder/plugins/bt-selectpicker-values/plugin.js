(function(root, factory) {
    if (typeof define == 'function' && define.amd) {
        define(['jquery', 'query-builder'], factory);
    }
    else {
        factory(root.jQuery);
    }
}(this, function($) {

    "use strict";

    $.fn.queryBuilder.define('bt-selectpicker-values', function (options) {
        if (!$.fn.selectpicker || !$.fn.selectpicker.Constructor) {
            Utils.error('MissingLibrary', 'Bootstrap Select is required to use "bt-selectpicker" plugin. Get it here: http://silviomoreto.github.io/bootstrap-select');
        }

        var selectors = {
            rule_filter: '.rule-filter-container [name$=_filter]',
            rule_operator: '.rule-operator-container [name$=_operator]',
            rule_value: '.rule-value-container select[name*=_value_]',
        };

        // init selectpicker
        this.on('afterCreateRuleFilters', function(e, rule) {
            rule.$el.find(selectors.rule_filter).removeClass('form-control').selectpicker(options);
        });

        this.on('afterCreateRuleOperators', function(e, rule) {
            rule.$el.find(selectors.rule_operator).removeClass('form-control').selectpicker(options);
        });

        this.on('afterCreateRuleInput', function(e, rule) {
            var $select = rule.$el.find(selectors.rule_value);
            if ($select.length) {
                options.liveSearch = $select.find('option').length > 10;
                $select.removeClass('form-control').selectpicker(options);
            }
        });

        // update selectpicker on change
        this.on('afterUpdateRuleFilter', function(e, rule) {
            rule.$el.find(selectors.rule_filter).selectpicker('render');
        });

        this.on('afterUpdateRuleOperator', function(e, rule) {
            rule.$el.find(selectors.rule_operator).selectpicker('render');
        });

        this.on('afterUpdateRuleValue', function (e, rule) {
            rule.$el.find(selectors.rule_value).selectpicker('render');
        });

        this.on('beforeDeleteRule', function(e, rule) {
            rule.$el.find(selectors.rule_filter).selectpicker('destroy');
            rule.$el.find(selectors.rule_operator).selectpicker('destroy');
        });

    }, {
        container: 'body',
        style: 'btn-inverse btn-xs',
        width: 'auto'
    });
 }));