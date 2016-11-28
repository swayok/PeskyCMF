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
});