var debugDialog = function () {

    var model = $.observable({
        title: 'no title',
        content: 'no content',
        isVisible: false
    });

    var container = $('<div id="debug-dialog"></div>');
    var template = doT.template('{{? it.isVisible }}<div class="dialog opened">' +
            '<div class="dialog-close">&#x2716;</div>' +
            '<div class="dialog-title">{{= it.title }}</div>' +
            '<div class="dialog-content">' +
                '<iframe frameborder="0"></iframe>' +
        '</div>{{?}}');

    this.showDebug = showDebug;
    this.toggleVisibility = toggle;
    this.model = model;

    function showDebug (title, content) {
        if (!content.match(/^\s*<(html|!doctype)/i)) {
            content = content.replace(/^([\s\S]*?)((?:<!doctype[^>]*>)?\s*<html[\s\S]*?<body[^>]*>)/i, '$2$1', content);
        }
        content = content.replace(/<(\/?script[^>]*)>/i, '&lt;$1&gt;');
        model({
            isVisible: true,
            title: title,
            content: content
        });
    }

    function toggle (event){
        model().isVisible(!model().isVisible());
    }

    function render () {
        if (model().isVisible()) {
            container.html(template(model.toJSON()));

            if (!$.contains(document.body, container[0])) {
                $(document.body).append(container);
            }

            var iframe = container.find('iframe')[0];
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(model().content());
                iframe.contentWindow.document.close();
            }
        } else {
            container.html(template({
                isVisible: false
            }));
        }

        return this;
    }

    container.on('click', '.dialog-close', toggle);
    model.on('change', render, this);
};

GlobalVars.debugDialog = new debugDialog();