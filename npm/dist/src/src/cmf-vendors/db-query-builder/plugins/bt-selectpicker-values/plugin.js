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

        this.on('afterCreateRuleInput', function (e, rule) {
            rule.$el.find('.rule-value-container').find('select').removeClass('form-control').selectpicker(options);
        });

        this.on('afterUpdateRuleValue', function (e, rule) {
            rule.$el.find('.rule-value-container').find('select').selectpicker('render');
        });

    }, {
        container: 'body',
        style: 'btn-inverse btn-xs',
        width: 'auto'
    });
 }));