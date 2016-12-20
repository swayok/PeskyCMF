var CmfFileUploads = {
    defaultImageUploaderOptions: {
        language: $(document.body).attr('data-locale'),
        allowedFileTypes: ['image'],
        previewFileType: 'image',
        validateInitialCount: true,
        browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
        showUpload: false,
        layoutTemplates: {
            main1: "{preview}\n" +
            "<div class=\'input-group {class}\'>\n" +
            "   <div class=\'input-group-btn\'>\n" +
            "       {browse}\n" +
            "       {upload}\n" +
            "       {remove}\n" +
            "   </div>\n" +
            "   {caption}\n" +
            "</div>"
        },
        overwriteInitial: true,
        initialPreviewAsData: true
    }
};

CmfFileUploads.initImageUploaders = function (data) {
    for (var imageName in data.configs) {
        var imageConfig = data.configs[imageName];
        var options = $.extend({}, CmfFileUploads.defaultImageUploaderOptions, {
            allowedFileExtensions: imageConfig.allowed_extensions,
            minFileCount: imageConfig.min_files_count,
            maxFileCount: imageConfig.max_files_count,
            maxFileSize: imageConfig.max_file_size
        });
        if (
            data.files
            && $.isPlainObject(data.files)
            && data.files.urls
            && data.files.urls[imageName]
            && data.files.info
            && data.files.info[imageName]
        ) {
            var existingFilesUrls = data.files.urls[imageName];
            var existingFilesInfos = data.files.info[imageName];
            if (imageConfig.max_files_count === 1) {
                options.initialPreview = [existingFilesUrls];
                options.initialPreviewConfig = [existingFilesInfos];
            } else if ($.isArray(existingFilesUrls) && $.isArray(existingFilesInfos)) {
                options.initialPreview = existingFilesUrls;
                options.initialPreviewConfig = existingFilesInfos;
            }
        }
        $('#' + imageConfig.id)
            .fileinput(options)
            .on('fileclear', function(event) {
                $('#' + this.id + '-deleted').val('1');
            });
    }
};