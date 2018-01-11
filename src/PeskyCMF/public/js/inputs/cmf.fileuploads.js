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
            showDrag: false,
            showDownload: true
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
    var isSingleFile = imageConfig.max_files_count === 1;
    imageConfig.defaultPluginOptions = $.extend({layoutTemplates: {}}, CmfFileUploads.defaultImageUploaderOptions, {
        allowedFileExtensions: imageConfig.allowed_extensions,
        minFileCount: 0,
        maxFileCount: 1,
        maxFileSize: imageConfig.max_file_size,
        showCaption: isSingleFile,
        previewClass: (isSingleFile ? 'single-file-upload' : 'multi-file-upload')
    });
    if (!isSingleFile) {
        imageConfig.defaultPluginOptions.layoutTemplates.main2 =
            '<button class="fileinput-dragger" type="button"><span class="fa fa-arrows"></span></button>' +
            '{preview}\n<div class="kv-upload-progress kv-hidden"></div>\n' +
            '<div class="clearfix"></div>\n' +
            '<div class="kv-upload-toolbar text-center">{remove}\n{cancel}\n{upload}\n{browse}\n</div>';

        imageConfig.defaultPluginOptions.layoutTemplates.preview =
            '<div class="file-preview {class}">\n' +
            '    {close}' +
            '    <div class="no-file">' + CmfConfig.getLocalizationStringsForComponent('file_uploader').no_file + '</div>' +
            '    <div class="{dropClass}">\n' +
            '    <div class="file-preview-thumbnails">\n' +
            '    </div>\n' +
            '    <div class="clearfix"></div>' +
            '    <div class="file-preview-status text-center text-success"></div>\n' +
            '    <div class="kv-fileinput-error"></div>\n' +
            '    </div>\n' +
            '</div>';
    }
    imageConfig.inputsAdded = 0;
    imageConfig.isCloning = !!data.is_cloning;
    imageConfig.isInModal = !!data.is_in_modal;

    Utils.makeTemplateFromText(
            $('#' + imageConfig.id + '-tpl').html(),
            'CmfFileUploads.initImageUploader for image ' + imageName
        )
        .done(function (inputTemplate) {
            imageConfig.inputTpl = inputTemplate;
            imageConfig.addInput = function (pluginOptions, existingFileData) {
                return CmfFileUploads.initImageUploaderInput(imageConfig, pluginOptions, existingFileData);
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
                                {is_cloning: imageConfig.isCloning},
                                existingFiles[i]
                            )
                        )
                    }
                }
            }
            // add empty inputs
            if (imageConfig.inputsAdded === 0 && (isSingleFile || imageConfig.min_files_count > 0)) {
                // add at least 1 input
                imageConfig.addInput();
            }
            // add required amount of inputs
            for (var k = imageConfig.inputsAdded; k < imageConfig.min_files_count; k++) {
                imageConfig.addInput();
            }
            if (!isSingleFile) {
                Sortable.create($('#' + imageConfig.id + '-container')[0], {
                    handle: '.fileinput-dragger',
                    draggable: '.image-upload-input-container',
                    animation: 200,
                    forceFallback: true,
                    onUpdate: function (event) {
                        $(event.to).find('.image-upload-input-container').each(function (index, item) {
                            $(item).find('input[name$="][position]"]').val(String(index + 1));
                        });
                    }
                });
            }
        });
};

CmfFileUploads.initImageUploaderInput = function (imageConfig, pluginOptions, existingFileData) {
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