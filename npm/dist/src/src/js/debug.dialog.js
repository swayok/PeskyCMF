var DebugDialog = function () {

    var model = {
        title: 'no title',
        content: 'no content',
        isVisible: false
    };

    var container = $('<div id="debug-dialog"></div>');
    var template = '<div class="dialog opened">' +
            '<div class="dialog-close">&#x2716;</div>' +
            '<div class="dialog-title"></div>' +
            '<div class="dialog-content">' +
                '<iframe frameborder="0"></iframe>' +
        '</div>';

    this.showDebug = showDebug;
    this.toggleVisibility = toggle;

    function showDebug (title, content) {
        if (!content) {
            content = '';
        }
        if (!$.isPlainObject(content) && content.length > 3 && content[0] === '{' && content[content.length - 1] === '}') {
            try {
                var json = JSON.parse(content);
                content = json;
            } catch (ignore) {}
        }
        if (typeof content !== 'string') {
            content = '<html><head></head><body><pre style="white-space: pre-wrap;">'
                + JSON.stringify(content, null, 3).replace(/\\\\/g, '\\').replace(/\\["']/g, '"').replace(/\\n/g, "\n")
                + '</pre></body></html>';
        } else if (!content.match(/^\s*<(html|!doctype)/i)) {
            content = content.replace(/^([\s\S]*?)((?:<!doctype[^>]*>)?\s*<html[\s\S]*?<body[^>]*>)/i, '$2$1', content);
        }
        content = content.replace(/<(\/?script[^>]*)>/i, '&lt;$1&gt;');
        $.extend(model, {
            isVisible: true,
            title: title,
            content: content
        });
        render();
    }

    function toggle (event){
        model.isVisible = !model.isVisible;
        render();
    }

    function render () {
        if (model.isVisible) {
            var tpl = $(template);
            container.empty().append(tpl.find('.dialog-title').html(model.title).end());

            if (!$.contains(document.body, container[0])) {
                $(document.body).append(container);
            }

            var iframe = container.find('iframe')[0];
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(model.content);
                iframe.contentWindow.document.close();
            }
        } else {
            container.empty();
        }
        return this;
    }

    container.on('click', '.dialog-close', toggle);
};