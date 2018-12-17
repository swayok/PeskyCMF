if (typeof CKEDITOR !== 'undefined') {

    CKEDITOR.editorConfig = function( config ) {
        @foreach($configs as $name => $value)
        config.{{ $name }} = {!! json_encode($value, JSON_PRETTY_PRINT) !!};

        @endforeach
    };

    CKEDITOR.on('instanceCreated', function(event) {
        var element = $(event.editor.element);
        if (element.attr('data-editor-name')) {
            event.editor.name = element.attr('data-editor-name');
        }

        event.editor.on('afterCommandExec', function (e) {
            // prevent css class inheritance on p and div tags when pressing ENTER
            if (e.data.name == 'enter') {
                var el = e.editor.getSelection().getStartElement();

                // when splitting a paragrah before it's first character, the second one is selected
                if (el.getHtml() != '<br>') {
                    if (el.hasPrevious() && el.getPrevious().getHtml() == '<br>') {
                        el = el.getPrevious();
                    } else {
                        // both paragraphs are non-empty, do nothing
                        return;
                    }
                }
                // modify el according to your needs
                if (el.$.nodeName.toLowerCase() === 'p' || el.$.nodeName.toLowerCase() === 'div') {
                    el.$.className = '';
                }
            }
        });
    });
}