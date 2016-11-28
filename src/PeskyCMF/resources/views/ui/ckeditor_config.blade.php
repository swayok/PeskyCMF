CKEDITOR.editorConfig = function( config ) {
    @foreach($configs as $name => $value)
    config.{{ $name }} = {!! json_encode($value, JSON_PRETTY_PRINT) !!};

    @endforeach
};
