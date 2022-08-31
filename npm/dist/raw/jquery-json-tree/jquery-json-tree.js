/**
 * json-view - jQuery collapsible JSON plugin
 * @version v1.0.0
 * @link http://github.com/bazh/jquery.json-view
 * @license MIT
 */
;(function ($) {
    'use strict';

    var expand = function ($block) {
        $block.children('ul').show();
        $block.children('.dots, .comments').hide();
    };

    var collapse = function ($block) {
        $block.children('ul').hide();
        $block.children('.dots, .comments').show();
    };

    var collapser = function(collapsed, parent) {
        var item = $('<span />', {
            'class': 'collapser',
            on: {
                click: function() {
                    var $this = $(this);

                    $this.toggleClass('collapsed');
                    var $block = $this.parent().children('.block');

                    if ($this.hasClass('collapsed')) {
                        collapse($block);
                    } else {
                        expand($block);
                    }
                }
            }
        });

        if (collapsed) {
            item.addClass('collapsed');
        }

        return item;
    };

    var formatter = function(json, opts) {
        var options = $.extend({}, {
            nl2br: true,
            autoOpen: 5
        }, opts);
        if (typeof options.autoOpen !== 'number' || typeof options.autoOpen !== 'boolean') {
            options.autoOpen = parseInt(options.autoOpen);
            if (isNaN(options.autoOpen)) {
                options.autoOpen = 5;
            }
        } else if (options.autoOpen < 0 || options.autoOpen === false) {
            options.autoOpen = -1;
        }

        var htmlEncode = function(html) {
            if (!html.toString()) {
                return '';
            }

            return html.toString().replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        };

        var span = function(val, cls) {
            return $('<span />', {
                'class': cls,
                html: htmlEncode(val)
            });
        };        

        var genBlock = function(val, level, autoOpenLevel) {
            switch($.type(val)) {
                case 'object':
                    if (!level) {
                        level = 0;
                    }
                    if (typeof autoOpenLevel === 'undefined') {
                        autoOpenLevel = true;
                    }
                    var collapsed = (autoOpenLevel !== true && level > autoOpenLevel);

                    var output = $('<span />', {
                        'class': 'block'
                    });

                    var cnt = Object.keys(val).length;
                    if (!cnt) {
                        return output
                            .append(span('{', 'b'))
                            .append(' ')
                            .append(span('}', 'b'));
                    }

                    output.append(span('{', 'b'));

                    var items = $('<ul />', {
                        'class': 'obj collapsible level' + level
                    });

                    $.each(val, function(key, data) {
                        cnt--;
                        var item = $('<li />')
                            .append(span('"', 'q'))
                            .append(key)
                            .append(span('"', 'q'))
                            .append(': ')
                            .append(genBlock(data, level + 1, autoOpenLevel));

                        if (['object', 'array'].indexOf($.type(data)) !== -1 && !$.isEmptyObject(data)) {
                            item.prepend(collapser(collapsed));
                        }

                        if (cnt > 0) {
                            item.append(',');
                        }

                        items.append(item);
                    });

                    output.append(items);
                    output.append(span('...', 'dots'));
                    output.append(span('}', 'b'));
                    if (Object.keys(val).length === 1) {
                        output.append(span('// 1 item', 'comments'));
                    } else {
                        output.append(span('// ' + Object.keys(val).length + ' items', 'comments'));
                    }

                    return output;

                case 'array':
                    if (!level) {
                        level = 0;
                    }
                    if (typeof autoOpenLevel === 'undefined') {
                        autoOpenLevel = true;
                    }
                    collapsed = (autoOpenLevel !== true && level > autoOpenLevel);

                    var cnt = val.length;

                    var output = $('<span />', {
                        'class': 'block'
                    });

                    if (!cnt) {
                        return output
                            .append(span('[', 'b'))
                            .append(' ')
                            .append(span(']', 'b'));
                    }

                    output.append(span('[', 'b'));

                    var items = $('<ul />', {
                        'class': 'obj collapsible level' + level
                    });

                    $.each(val, function(key, data) {
                        cnt--;
                        var item = $('<li />')
                            .append(genBlock(data, level + 1, autoOpenLevel));

                        if (['object', 'array'].indexOf($.type(data)) !== -1 && !$.isEmptyObject(data)) {
                            item.prepend(collapser(collapsed, item));
                        }

                        if (cnt > 0) {
                            item.append(',');
                        }

                        items.append(item);
                    });

                    output.append(items);
                    output.append(span('...', 'dots'));
                    output.append(span(']', 'b'));
                    if (val.length === 1) {
                        output.append(span('// 1 item', 'comments'));
                    } else {
                        output.append(span('// ' + val.length + ' items', 'comments'));
                    }

                    return output;

                case 'string':
                    val = htmlEncode(val);
                    if (/^(http|https|file):\/\/[^\s]+$/i.test(val)) {
                        return $('<span />')
                            .append(span('"', 'q'))
                            .append($('<a />', {
                                href: val,
                                text: val
                            }))
                            .append(span('"', 'q'));
                    }
                    if (options.nl2br) {
                        var pattern = /\n/g;
                        if (pattern.test(val)) {
                            val = (val + '').replace(pattern, '<br />');
                        }
                    }

                    var text = $('<span />', { 'class': 'str' })
                        .html(val);

                    return $('<span />')
                        .append(span('"', 'q'))
                        .append(text)
                        .append(span('"', 'q'));

                case 'number':
                    return span(val.toString(), 'num');

                case 'undefined':
                    return span('undefined', 'undef');

                case 'null':
                    return span('null', 'null');

                case 'boolean':
                    return span(val ? 'true' : 'false', 'bool');
            }
        };

        return genBlock(json, 0, options.autoOpen);
    };

    return $.fn.jsonView = function(json, options) {
        var $this = $(this);

        options = $.extend({}, {
            nl2br: true,
            autoOpen: 5
        }, options);

        if (typeof json === 'string') {
            try {
                json = JSON.parse(json);
            } catch (err) {
            }
        }

        $this
            .append(
                $('<div />', {class: 'json-view'})
                    .append(formatter(json, options))
            )
            .find('.collapser.collapsed')
                .parent()
                    .children('.block')
                        .each(function () {
                            collapse($(this));
                        });

        return $this;
    };

})(jQuery);
