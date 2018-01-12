var CmfFileUploads = {
    baseUploaderOptions: {
        language: $(document.body).attr('data-locale'),
        validateInitialCount: true,
        showUpload: false,
        allowedFileTypes: ['image', 'text', 'video', 'audio', 'object'],
        allowedPreviewTypes: ['image', 'video', 'audio'],
        previewFileType: 'any',
        previewFileIcon: '<i class="fa fa-file"></i>',
        initialPreviewAsData: true,
        overwriteInitial: true,
        fileActionSettings: {
            showDrag: false,
            showDownload: true
        },
        browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
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
        }
    },
    imageUploaderOptions: {
        allowedFileTypes: ['image'],
        allowedPreviewTypes: ['image'],
        previewFileType: 'image',
        initialPreviewFileType: 'image'
    },
    fileUploaderOptions: {
        fileActionSettings: {
            showDrag: false,
            showDownload: true,
            showBrowse: false
        },
        previewFileIconSettings: {
            'doc': '<i class="fa fa-file-word-o text-primary"></i>',
            'docx': '<i class="fa fa-file-word-o text-primary"></i>',
            'txt': '<i class="fa fa-file-text-o"></i>',
            'json': '<i class="fa fa-file-text-o"></i>',
            'js': '<i class="fa fa-file-text-o"></i>',
            'rtf': '<i class="fa fa-file-text-o"></i>',
            'xls': '<i class="fa fa-file-excel-o text-success"></i>',
            'xlsx': '<i class="fa fa-file-excel-o text-success"></i>',
            'csv': '<i class="fa fa-file-excel-o text-success"></i>',
            'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pptx': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
            'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'gzip': '<i class="fa fa-file-archive-o text-muted"></i>',
            'rar': '<i class="fa fa-file-archive-o text-muted"></i>'
        }
    }
};

CmfFileUploads.initFileUploaders = function (data, isImages) {
    for (var filesGroupName in data.configs) {
        CmfFileUploads.initFileUploader(data, filesGroupName, isImages);
    }
};

CmfFileUploads.initFileUploader = function (data, filesGroupName, isImages) {
    var fileConfig = data.configs[filesGroupName];
    var isSingleFile = fileConfig.max_files_count === 1;
    fileConfig.defaultPluginOptions = $.extend(
        {layoutTemplates: {}},
        CmfFileUploads.baseUploaderOptions,
        isImages ? CmfFileUploads.imageUploaderOptions : CmfFileUploads.fileUploaderOptions,
        {
            allowedFileExtensions: fileConfig.allowed_extensions,
            minFileCount: 0,
            maxFileCount: 1,
            maxFileSize: fileConfig.max_file_size,
            showCaption: isSingleFile,
            previewClass: (isSingleFile ? 'single-file-upload' : 'multi-file-upload')
        }
    );
    if (!isSingleFile) {
        fileConfig.defaultPluginOptions.layoutTemplates.main2 =
            '<button class="fileinput-dragger" type="button"><span class="fa fa-arrows"></span></button>' +
            '{preview}\n<div class="kv-upload-progress kv-hidden"></div>\n' +
            '<div class="clearfix"></div>\n' +
            '<div class="kv-upload-toolbar text-center">{remove}\n{cancel}\n{upload}\n{browse}\n</div>';

        fileConfig.defaultPluginOptions.layoutTemplates.preview =
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
    fileConfig.inputsAdded = 0;
    fileConfig.isCloning = !!data.is_cloning;
    fileConfig.isInModal = !!data.is_in_modal;

    Utils.makeTemplateFromText(
            $('#' + fileConfig.id + '-tpl').html(),
            'CmfFileUploads.initFileUploader for files group ' + filesGroupName
        )
        .done(function (inputTemplate) {
            fileConfig.inputTpl = inputTemplate;
            fileConfig.addInput = function (pluginOptions, existingFileData) {
                return CmfFileUploads.initFileUploaderInput(fileConfig, pluginOptions, existingFileData);
            };
            $('#' + fileConfig.id + '-add')
                .on('click', function () {
                    fileConfig.addInput();
                    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
                        $(this).remove();
                    }
                    return false;
                });
            // show existing files
            if (
                data.files
                && $.isPlainObject(data.files)
                && data.files.urls
                && data.files.urls[filesGroupName]
                && data.files.preview_info
                && data.files.preview_info[filesGroupName]
                && data.files.files
                && data.files.files[filesGroupName]
            ) {
                var existingFilesUrls = data.files.urls[filesGroupName];
                var existingFilesPreviewsInfo = data.files.preview_info[filesGroupName];
                var existingFiles = data.files.files[filesGroupName];
                if ($.isArray(existingFilesUrls) && $.isArray(existingFilesPreviewsInfo) && $.isArray(existingFiles)) {
                    for (var i = 0; i < existingFilesUrls.length; i++) {
                        fileConfig.addInput(
                            {
                                initialPreview: [existingFilesUrls[i]],
                                initialPreviewConfig: [existingFilesPreviewsInfo[i]],
                                initialCaption: [existingFilesPreviewsInfo[i].caption]
                            },
                            $.extend(
                                {is_cloning: fileConfig.isCloning},
                                existingFiles[i]
                            )
                        )
                    }
                }
            }
            // add empty inputs
            if (fileConfig.inputsAdded === 0 && (isSingleFile || fileConfig.min_files_count > 0)) {
                // add at least 1 input
                fileConfig.addInput();
            }
            // add required amount of inputs
            for (var k = fileConfig.inputsAdded; k < fileConfig.min_files_count; k++) {
                fileConfig.addInput();
            }
            if (!isSingleFile) {
                Sortable.create($('#' + fileConfig.id + '-container')[0], {
                    handle: '.fileinput-dragger',
                    draggable: '.file-upload-input-container',
                    animation: 200,
                    forceFallback: true,
                    onUpdate: function (event) {
                        $(event.to).find('.file-upload-input-container').each(function (index, item) {
                            $(item).find('input[name$="][position]"]').val(String(index + 1));
                        });
                    }
                });
            }
        });
};

CmfFileUploads.initFileUploaderInput = function (fileConfig, pluginOptions, existingFileData) {
    if (fileConfig.inputsAdded >= fileConfig.max_files_count) {
        return false;
    }
    if (!$.isPlainObject(existingFileData)) {
        existingFileData = {};
    }
    existingFileData = $.extend({index: fileConfig.inputsAdded}, existingFileData);
    var $renderedTemplate = $(fileConfig.inputTpl(existingFileData));
    $('#' + fileConfig.id + '-container').append($renderedTemplate);
    fileConfig.inputsAdded++;
    var $fileInput = $renderedTemplate.find('input[type="file"]');
    var options = $.extend(
        {},
        fileConfig.defaultPluginOptions,
        $.isPlainObject(pluginOptions) ? pluginOptions : {}
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
            if (fileConfig.isInModal) {
                $('body').addClass('modal-open');
                $('.modal.in').css('padding-left', '17px');
            }
        });
};