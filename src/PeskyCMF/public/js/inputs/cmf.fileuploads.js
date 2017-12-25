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
        initialPreviewAsData: true,
        fileActionSettings: {
            showDrag: false
        }
    }
};

CmfFileUploads.initImageUploaders = function (data) {
    for (var imageName in data.configs) {
        CmfFileUploads.initImageUploader(data, imageName);
    }
};

CmfFileUploads.initImageUploader = function (data, imageName) {
    var imageConfig = data.configs[imageName];
    imageConfig.defaultPluginOptions = $.extend({}, CmfFileUploads.defaultImageUploaderOptions, {
        allowedFileExtensions: imageConfig.allowed_extensions,
        minFileCount: 0,
        maxFileCount: 1,
        maxFileSize: imageConfig.max_file_size
    });
    imageConfig.inputsAdded = 0;
    imageConfig.isCloning = !!data.is_cloning;
    imageConfig.isInModal = !!data.is_in_modal;

    Utils.makeTemplateFromText(
            $('#' + imageConfig.id + '-tpl').html(),
            'initImageUploaders for image ' + imageName
        )
        .done(function (template) {
            imageConfig.inputTpl = template;
            imageConfig.addInput = function (pluginOptions, existingFileData) {
                if (imageConfig.inputsAdded >= imageConfig.max_files_count) {
                    return false;
                }
                if (!$.isPlainObject(existingFileData)) {
                    existingFileData = {};
                }
                existingFileData = $.extend({index: imageConfig.inputsAdded}, existingFileData);
                var $renderedTemplate = $(imageConfig.inputTpl(existingFileData));
                $('#' + imageConfig.id + '-container').append($renderedTemplate);
                imageConfig.inputsAdded++;
                var $fileInput = $renderedTemplate.find('input[type="file"]');
                var options = $.extend(
                    {},
                    imageConfig.defaultPluginOptions,
                    $.isPlainObject(pluginOptions) ? pluginOptions : {}/*,
                    {minFileCount: imageConfig.inputsAdded >= imageConfig.min_files_count ? 1 : 0}*/
                );

                if (existingFileData.is_cloning && existingFileData.url) {
                    var xhr = new XMLHttpRequest();
                    xhr.onload = function(){
                        var reader = new FileReader();
                        reader.onloadend = function() {
                            $('#' + $fileInput[0].id + '-file-data').val(JSON.stringify($.extend({data: reader.result}, existingFileData)));
                        };
                        reader.readAsDataURL(xhr.response);
                    };
                    xhr.open('GET', existingFileData.url);
                    xhr.responseType = 'blob';
                    xhr.send();
                }
                $fileInput
                    .fileinput(options)
                    .on('fileclear', function() {
                        $('#' + this.id + '-deleted').val('1');
                        $('#' + this.id + '-file-data').remove();
                    })
                    .on('change', function () {
                        $('#' + this.id + '-file-data').remove();
                    })
                    .on('filezoomhidden', function (event, params) {
                        params.modal.remove();
                        if (imageConfig.isInModal) {
                            $('body').addClass('modal-open');
                            $('.modal.in').css('padding-left', '17px');
                        }
                    });
            };
            $('#' + imageConfig.id + '-add')
                .on('click', function () {
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
                            $.extend(
                                {url: existingFilesUrls[i], is_cloning: imageConfig.isCloning},
                                existingFiles[i]
                            )
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
        });
};