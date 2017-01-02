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
        imageConfig.defaultPluginOptions = $.extend({}, CmfFileUploads.defaultImageUploaderOptions, {
            allowedFileExtensions: imageConfig.allowed_extensions,
            minFileCount: 0,
            maxFileCount: 1,
            maxFileSize: imageConfig.max_file_size
        });
        imageConfig.inputsAdded = 0;
        imageConfig.inputTpl = doT.template($('#' + imageConfig.id + '-tpl').html());
        imageConfig.addInput = function (pluginOptions, existingFileData) {
            var imageConfig = this;
            if (imageConfig.inputsAdded >= imageConfig.max_files_count) {
                return false;
            }
            var $renderedTemplate = $(imageConfig.inputTpl(
                $.extend({index: imageConfig.inputsAdded}, $.isPlainObject(existingFileData) ? existingFileData : {}))
            );
            $('#' + imageConfig.id + '-container').append($renderedTemplate);
            imageConfig.inputsAdded++;
            var $fileInput = $renderedTemplate.find('input[type="file"]');
            var options = $.extend(
                {},
                imageConfig.defaultPluginOptions,
                $.isPlainObject(pluginOptions) ? pluginOptions : {}/*,
                {minFileCount: imageConfig.inputsAdded >= imageConfig.min_files_count ? 1 : 0}*/
            );
            $fileInput
                .fileinput(options)
                .on('fileclear', function() {
                    $('#' + this.id + '-deleted').val('1');
                });
        };
        $('#' + imageConfig.id + '-add')
            .data('imageConfig', imageConfig)
            .on('click', function () {
                var imageConfig = $(this).data('imageConfig');
                imageConfig.addInput();
                if (imageConfig.inputsAdded >= imageConfig.max_files_count) {
                    $(this).remove();
                }
                return false;
            });
        // show existing files
        if (
            data.files
            && $.isPlainObject(data.files)
            && data.files.urls
            && data.files.urls[imageName]
            && data.files.preview_info
            && data.files.preview_info[imageName]
            && data.files.files
            && data.files.files[imageName]
        ) {
            var existingFilesUrls = data.files.urls[imageName];
            var existingFilesPreviewsInfo = data.files.preview_info[imageName];
            var existingFiles = data.files.files[imageName];
            if ($.isArray(existingFilesUrls) && $.isArray(existingFilesPreviewsInfo) && $.isArray(existingFiles)) {
                for (var i = 0; i < existingFilesUrls.length; i++) {
                    imageConfig.addInput(
                        {
                            initialPreview: [existingFilesUrls[i]],
                            initialPreviewConfig: [existingFilesPreviewsInfo[i]],
                            initialCaption: [existingFilesPreviewsInfo[i].caption]
                        },
                        existingFiles[i]
                    )
                }
            }
        }
        // add empty inputs
        if (imageConfig.inputsAdded === 0) {
            // add at least 1 input
            imageConfig.addInput();
        }
        // add required amount of inputs
        for (var k = imageConfig.inputsAdded; k < imageConfig.min_files_count; k++) {
            imageConfig.addInput();
        }
    }
};