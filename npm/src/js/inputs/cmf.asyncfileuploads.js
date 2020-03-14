/**
 * @param {string|jQuery} input
 * @param {string|jQuery} previewsContainer
 * @param {Object} options
 * @param {string|jQuery} options.dropContainer
 * @param {function|string|jQuery} options.preview_tpl
 * @param {function|string|jQuery} options.uploading_progress_tpl
 * @param {string}      options.id
 * @param {string}      options.name
 * @param {number}      options.min_files_count     // todo: implement min_files_count
 * @param {number}      options.max_files_count
 * @param {number}      options.max_width
 * @param {number}      options.max_height
 * @param {number}      options.aspect_ratio        // todo: implement aspect_ratio
 * @param {number}      options.max_file_size
 * @param {number}      options.preview_width
 * @param {Array}       options.allowed_mime_types
 * @param {Array}       options.files
 * @param {string}      options.upload_url
 * @param {string}      options.delete_url
 * @param {Object}      options.upload_data
 * @param {Object}      options.delete_data
 */
function CmfAsyncFilesUploader(input, previewsContainer, options) {
    var config = $.extend({}, CmfAsyncFilesUploader.defaults, options || {});
    config.locale = $.extend(true, {}, CmfAsyncFilesUploader.locale, config.locale || {});

    if (!config.upload_url || !config.delete_url) {
        throw "options argument must at least contain upload_url and delete_url properties";
    }

    var $input = $(input);
    if (!$input.length) {
        console.error('CmfAsyncFilesUploader: input argument is invalid');
        return;
    }
    var $previewsContainer = $(previewsContainer);
    if (!$previewsContainer.length) {
        console.error('CmfAsyncFilesUploader: previewsContainer argument is invalid');
        return;
    }
    var $dropContainer = config.dropContainer ? $(config.dropContainer) : $previewsContainer.closest('div');
    var filePreviewTpl = typeof config.preview_tpl === 'function'
        ? config.preview_tpl
        : doT.template($(config.preview_tpl).html());
    var fileUploadingProgressTpl = typeof config.uploading_progress_tpl === 'function'
        ? config.uploading_progress_tpl
        : doT.template($(config.uploading_progress_tpl).html());

    var processedFiles = [];
    var existingFiles = [];
    var uploadsInProgress = 0;

    var allowedMimes = {};
    for (var i in config.allowed_mime_types) {
        if ($.isPlainObject(config.allowed_mime_types[i])) {
            allowedMimes[i] = config.allowed_mime_types[i];
        } else if (CmfAsyncFilesUploader.mimes[config.allowed_mime_types[i]]) {
            allowedMimes[config.allowed_mime_types[i]] = CmfAsyncFilesUploader.mimes[config.allowed_mime_types[i]];
        }
    }

    var collectTplDataForNewFile = function (fileUID) {
        var deferred = $.Deferred();
        var details = processedFiles[fileUID];
        var isImage = allowedMimes[details.file.type] && allowedMimes[details.file.type].is_image;
        var tplData = $.extend(
            {},
            details.uploading,
            {
                is_new: true,
                input_name: config.name,
                uid: fileUID,
                name: details.file.name,
                size: Math.round(details.file.size / FileAPI.MB * 100) / 100,
                error: details.error,
                is_image: isImage,
                preview_width: config.preview_width,
                can_delete: details.uploading.uploaded_file_info || details.error || details.uploading.can_retry,
                width: isImage && details.info && details.info.width ? details.info.width : null,
                height: isImage && details.info && details.info.height ? details.info.height : null
            }
        );
        if (tplData.is_image) {
            if (processedFiles[fileUID].preview_canvas) {
                tplData.preview_canvas = processedFiles[fileUID].preview_canvas;
                deferred.resolve(tplData);
            } else {
                FileAPI.Image(details.file)
                    .resize(config.preview_width, 'width')
                    .get(function (error, img) {
                        tplData.preview_canvas = processedFiles[fileUID].preview_canvas = img;
                        deferred.resolve(tplData);
                    });
            }
            tplData.preview = '<div class="canvas-placeholder"></div>';
        } else {
            tplData.preview = allowedMimes[details.file.type]
                ? allowedMimes[details.file.type].preview
                : CmfAsyncFilesUploader.mimes.other.preview;
            deferred.resolve(tplData);
        }
        return deferred.promise();
    };

    var collectTplDataForExistingFile = function (fileInfo) {
        return {
            is_new: false,
            is_uploading: false,
            is_uploaded: true,
            can_retry: false,
            uploaded_percent: 100,
            input_name: config.name,
            uid: fileInfo.uuid,
            name: fileInfo.name,
            size: Math.round(fileInfo.size / FileAPI.MB * 100) / 100,
            error: null,
            is_image: fileInfo.is_image,
            preview_width: config.preview_width,
            can_delete: true,
            width: fileInfo.width,
            height: fileInfo.height,
            uploaded_file_info: fileInfo.uploaded_file_info,
            preview: fileInfo.is_image
                ? '<img alt="" class="va-t" src="' + fileInfo.url + '" width="' + config.preview_width + '">'
                : (allowedMimes[fileInfo.type] ? allowedMimes[fileInfo.type].preview : CmfAsyncFilesUploader.mimes.other.preview)
        };
    };

    var renderPreviewForNewFile = function (fileUID, newData) {
        if (!newData) {
            collectTplDataForNewFile(fileUID)
                .done(function (newData) {
                    if (newData) {
                        renderPreviewForNewFile(fileUID, newData);
                    }
                });
            return;
        }
        if (newData.is_uploading) {
            newData.progress_bar = fileUploadingProgressTpl(newData);
        }
        if (!processedFiles[fileUID].$el) {
            processedFiles[fileUID].$el = $(filePreviewTpl(newData));
            $previewsContainer.append(processedFiles[fileUID].$el);
        } else {
            processedFiles[fileUID].$el.html(
                $(filePreviewTpl(newData)).html()
            );
        }
        if (newData.preview_canvas) {
            processedFiles[fileUID].$el.find('.canvas-placeholder').after(newData.preview_canvas).remove();
        }
        processedFiles[fileUID].$el.find('[data-toggle="tooltip"]').tooltip();
    };

    var renderPreviewForExistingFile = function (fileInfo) {
        var tplData = collectTplDataForExistingFile(fileInfo);
        if (!existingFiles[fileInfo.uuid]) {
            existingFiles[fileInfo.uuid] = $(filePreviewTpl(tplData));
            $previewsContainer.append(existingFiles[fileInfo.uuid]);
        } else {
            existingFiles[fileInfo.uuid].html(
                $(filePreviewTpl(tplData)).html()
            );
        }
        existingFiles[fileInfo.uuid].find('[data-toggle="tooltip"]').tooltip();
    };

    var updateProgressBar = function (fileUID, uploadedPercent) {
        processedFiles[fileUID].$el.find('[data-container="progress-bar"]').html(
            fileUploadingProgressTpl({uploaded_percent: uploadedPercent})
        );
    };
    var makeFileUID = function (file) {
        return file.name + '_' + String(file.size);
    };
    var validateFileType = function (file) {
        if (!allowedMimes[file.type]) {
            return config.locale.error.mime_type_forbidden;
        }
        var extension = file.name.replace(/^.+\.([a-zA-Z0-9]{3,4})$/, '$1').toLowerCase();
        if (!allowedMimes[file.type].extensions.includes(extension) || file.name.toLowerCase() === extension) {
            return config.locale.error.mime_type_and_extension_missmatch;
        }
        if (allowedMimes[file.type].is_image && file.size / FileAPI.KB > config.max_file_size) {
            return config.locale.error.file_too_large.replace('{max_size_mb}', Math.round(config.max_file_size / 1024 * 100) / 100);
        }
        return null;
    };
    var getFilesCount = function (onlyValid) {
        var count = Object.keys(existingFiles).length;
        for (var key in processedFiles) {
            if (!onlyValid || !processedFiles[key].error || processedFiles[key].uploading.can_retry) {
                count++;
            }
        }
        return count;
    };
    var hasFileSlots = function () {
        if (config.max_files_count <= 0) {
            return true;
        }
        var count = getFilesCount(true);
        return count < config.max_files_count;
    };
    var processNewFile = function (file) {
        if (!hasFileSlots()) {
            // control files count
            toastr.error(config.locale.error.too_many_files.replace('{limit}', config.max_files_count));
            return null;
        }
        var error = validateFileType(file);
        var fileUID = makeFileUID(file);
        if (processedFiles[fileUID]) {
            // prevent duplicates
            toastr.error(config.locale.error.already_attached.replace('{name}', file.name));
            return null;
        }
        var deferred = $.Deferred();
        processedFiles[fileUID] = {
            UID: fileUID,
            file: file,
            error: error,
            info: null,
            $el: null,
            uploading: {
                is_uploading: false,
                is_uploaded: false,
                can_retry: false,
                uploaded_percent: 0
            }
        };
        renderPreviewForNewFile(fileUID);
        if (!error) {
            FileAPI.getInfo(file, function (isError, info) {
                var fileUID = file.name + '_' + String(file.size);
                if (!isError) {
                    processedFiles[fileUID].info = info;
                }
                renderPreviewForNewFile(fileUID);
                deferred.resolve(processedFiles[fileUID]);
            });
        } else {
            deferred.resolve(processedFiles[fileUID]);
        }
        return deferred.promise();
    };
    var triggerFilesCountChangeEvent = function () {
        $input.trigger('filescountchange', [getFilesCount(false), getFilesCount(true)]);
    };
    var uploadFiles = function (filesToUpload) {
        if (!filesToUpload || !filesToUpload.length) {
            return;
        }
        if (uploadsInProgress === 0) {
            $input.trigger('filesuploadingstarted');
        }
        uploadsInProgress++;
        return FileAPI.upload({
            url: config.upload_url,
            data: config.upload_data || {},
            files: {
                file: filesToUpload
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            imageTransform: {
                maxWidth: config.max_width,
                maxHeight: config.max_height,
                imageAutoOrientation: true
            },
            complete: function () {
                uploadsInProgress = Math.max(0, uploadsInProgress - 1);
                if (uploadsInProgress === 0) {
                    $input.trigger('filesuploadingended');
                }
            },
            fileupload: function (file, xhr) {
                var fileUID = makeFileUID(file);
                var details = processedFiles[fileUID];
                details.uploading.uploaded_percent = 0;
                if (details.uploading_cancelled) {
                    details.uploading_cancelled = false;
                    details.uploading.is_uploading = false;
                    details.uploading.is_uploaded = false;
                    details.uploading.can_retry = true;
                    xhr.abort();
                } else {
                    details.uploading.is_uploading = true;
                    details.uploading.can_retry = false;
                    details.xhr = xhr;
                }
                //console.log('upload', details);
                renderPreviewForNewFile(fileUID);
            },
            fileprogress: function (evt, file) {
                var fileUID = makeFileUID(file);
                var details = processedFiles[fileUID];
                details.uploading.uploaded_percent = Math.round(evt.loaded / evt.total * 10000) / 100;
                //console.log('progress', details);
                updateProgressBar(fileUID, details.uploading.uploaded_percent);
            },
            filecomplete: function (err, xhr, file) {
                var fileUID = makeFileUID(file);
                var details = processedFiles[fileUID];
                if (xhr.status === 0) {
                    // aborted
                    return;
                }
                details.uploading.is_uploading = false;
                details.uploading.is_uploaded = true;
                details.uploading.can_retry = false;
                details.uploading.uploaded_percent = 100;
                details.error = null;
                details.xhr = null;
                details.uploading_cancelled = false;
                //console.log('complete', details, xhr);
                if (xhr.status >= 500) {
                    // server error
                    details.error = config.locale.error.server_error;
                    details.uploading.can_retry = true;
                } else {
                    if (xhr.status === 422 || xhr.status === 400) {
                        // data validation error
                        try {
                            var errorsJson = JSON.parse(xhr.responseText);
                            if (errorsJson.errors && errorsJson.errors.file) {
                                details.error = errorsJson.errors.file.join('<br>');
                            } else if (errorsJson._message) {
                                details.error = config.locale.error.server_error;
                            } else {
                                details.error = config.locale.error.non_json_validation_error;
                            }
                        } catch (e) {
                            details.error = config.locale.error.non_json_validation_error;
                        }
                        triggerFilesCountChangeEvent();
                    } else if (xhr.status > 400) {
                        // some unexpected error
                        details.error = config.locale.error.unexpected_error;
                        triggerFilesCountChangeEvent();
                    } else if (xhr.responseText && xhr.responseText.length > 20) {
                        // its ok
                        details.uploading.uploaded_file_info = xhr.responseText;
                        triggerFilesCountChangeEvent();
                    } else {
                        // something wrong with response
                        details.error = config.locale.error.invalid_response;
                        triggerFilesCountChangeEvent();
                    }
                }
                renderPreviewForNewFile(fileUID);
            }
        });
    };

    var processReceivedFiles = function (files) {
        var promises = [];
        for (var key in files) {
            var promise = processNewFile(files[key]);
            if (promise) {
                promises.push(promise)
            }
        }
        $.when.apply($, promises).then(function () {
            triggerFilesCountChangeEvent();
            var validFiles = [];
            for (var i = 0; i < arguments.length; i++) {
                var processedFile = arguments[i];
                if (!processedFile.error && !processedFile.uploading.is_uploading) {
                    validFiles.push(processedFile.file);
                    processedFile.uploading.is_uploading = true;
                    processedFile.uploading.uploaded_percent = 0;
                    processedFile.uploading.can_retry = false;
                    renderPreviewForNewFile(processedFile.UID);
                }
            }
            if (validFiles.length) {
                uploadFiles(validFiles);
            }
        });
    };

    FileAPI.event.on($input[0], 'change', function (evt) {
        var selectedFiles = FileAPI.getFiles(evt);
        $input[0].value = '';
        processReceivedFiles(selectedFiles);
    });

    if (config.files && Array.isArray(config.files)) {
        for (var i in config.files) {
            renderPreviewForExistingFile(config.files[i]);
        }
        triggerFilesCountChangeEvent();
    }

    var allowFileDrop = true;

    var $dropHere = $('<div class="files-uploader-dropzone"></div>')
        .append(
            $('<div class="files-uploader-dropzone-text text-muted"></div>')
                .css({
                    position: 'absolute',
                    textAlign: 'center',
                    top: '50%',
                    width: '100%',
                    lineHeight: '40px',
                    marginTop: '-20px',
                    fontSize: '36px'
                })
                .text(config.locale.drop_files_here)
        )
        .css({
            position: 'absolute',
            display: 'none',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: 'rgba(255,255,255,0.8)',
            zIndex: 5
        })
        .on('dragleave drop', function () {
            $dropHere.fadeOut(200);
        });

    $dropContainer
        .css({
            position: 'relative'
        })
        .append($dropHere);

    FileAPI.event.on($previewsContainer[0], 'drop', function (evt) {
        evt.preventDefault();

        FileAPI.getDropFiles(evt, function (files) {
            processReceivedFiles(files);
        });
    });

    FileAPI.event.on($dropHere[0], 'drop', function (evt) {
        evt.preventDefault();

        FileAPI.getDropFiles(evt, function (files) {
            processReceivedFiles(files);
        });
    });

    $previewsContainer
        .on('dragover', function (event) {
            if (allowFileDrop) {
                $dropHere.fadeIn(200);
            }
        });

    Sortable.create($previewsContainer[0], {
        group: config.name,
        sorting: true,
        scroll: true,
        handle: '.files-uploader-dragger',
        draggable: 'tr',
        animation: 200,
        forceFallback: true,
        removeCloneOnHide: false,
        onStart: function () {
            allowFileDrop = false;
            $dropHere.fadeOut(200);
        },
        onEnd: function () {
            allowFileDrop = true;
        }
    });

    $previewsContainer
        .on('click', 'button.files-uploader-file-delete', function () {
            var fileUID = $(this).attr('data-uid');
            var isExisting = !!$(this).attr('data-existing');
            if (isExisting) {
                if (existingFiles[fileUID]) {
                    existingFiles[fileUID].remove();
                    delete existingFiles[fileUID];
                }
            } else {
                var details = processedFiles[fileUID];
                details.$el.remove();
                if (details.uploading && details.uploading.uploaded_file_info) {
                    // delete from server
                    $.ajax({
                        url: config.delete_url,
                        method: 'POST',
                        data: $.extend(
                            {},
                            config.delete_data || {},
                            {info: details.uploading.uploaded_file_info}
                        )
                    });
                    // ignore response - it doesn't really matter - just storage saving feature
                }
                delete processedFiles[fileUID];
            }
            triggerFilesCountChangeEvent();
        })
        .on('click', 'button.files-uploader-file-retry', function () {
            var fileUID = $(this).attr('data-uid');
            var details = processedFiles[fileUID];
            if (details && details.uploading.can_retry) {
                uploadFiles([details.file]);
            } else {
                $(this).remove();
            }
        })
        .on('click', 'button.files-uploader-file-cancel', function () {
            var fileUID = $(this).attr('data-uid');
            var details = processedFiles[fileUID];
            if (details.xhr) {
                details.xhr.abort();
            } else {
                details.uploading_cancelled = true;
            }
            details.uploading.is_uploading = false;
            details.uploading.is_uploaded = false;
            details.uploading.uploaded_percent = 0;
            details.uploading.can_retry = true;
            renderPreviewForNewFile(fileUID);
        });

    this.getFilesCount = getFilesCount;
    this.getPreviewsContainer = function () {
        return $previewsContainer;
    };

    this.reset = function () {
        $previewsContainer.html('');
        uploadsInProgress = 0;
        processedFiles = [];
        triggerFilesCountChangeEvent();
        $input[0].value = '';
    }
}

CmfAsyncFilesUploader.mimes = {
    'image/jpeg': {
        is_image: true,
        extensions: ['jpg', 'jpeg'],
        preview: '<i class="fa fa-file-image-o text-info fs40"></i>'
    },
    'image/png': {
        is_image: true,
        extensions: ['png'],
        preview: '<i class="fa fa-file-image-o text-info fs40"></i>'
    },
    'application/pdf': {
        extensions: ['pdf'],
        preview: '<i class="fa fa-file-pdf-o text-danger fs40"></i>'
    },
    'application/msword': {
        extensions: ['doc'],
        preview: '<i class="fa fa-file-word-o text-primary fs40"></i>'
    },
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': {
        extensions: ['docx'],
        preview: '<i class="fa fa-file-word-o text-primary fs40"></i>'
    },
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': {
        extensions: ['xlsx'],
        preview: '<i class="fa fa-file-excel-o text-success-dark fs40"></i>'
    },
    'text/csv': {
        extensions: ['csv'],
        preview: '<i class="fa fa-file-excel-o text-success-dark fs40"></i>'
    },
    'other': {
        preview: '<i class="fa fa-file-text-o text-muted fs40"></i>'
    }
};

CmfAsyncFilesUploader.locale = {
    drop_files_here: 'Drop files here',
    error: {
        mime_type_forbidden: 'Files of this type are forbidden.',
        mime_type_and_extension_missmatch: 'File extension does not fit file type.',
        already_attached: 'File {name} already attached.',
        not_enough_files: 'Minimum number of files required: {limit}.',
        too_many_files: 'Maximum files limit reached: {limit}.',
        file_too_large: 'File size is bigger then maximum allowed file size ({max_size_mb} MB).',
        server_error: 'Unknown error occured during file saving.',
        unexpected_error: 'Failed to process and save file.',
        non_json_validation_error: 'File validation failed on server side.',
        invalid_response: 'Invalid response received from server.',
    }
};

CmfAsyncFilesUploader.defaults = {
    preview_tpl: null,
    uploading_progress_tpl: null,
    mimes: [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ],
    max_width: 1920,
    max_height: 1920,
    max_file_size: 8 * 1024,
    min_files_count: 0,
    max_files_count: 0,
    preview_width: 100,
    upload_url: null,
    delete_url: null,
    locale: null
};

(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof module !== 'undefined' && module.exports) {
        // CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Global
        factory(jQuery);
    }
})(function ($) {
    /**
     * @param {?Object} [options] - see CmfAsyncFilesUploader.defaults
     * @param {string|jQuery|Object|null} options.previews_container - previews container selector, DOM element or jQuery object
     * @param {function|null} options.previews_container_finder - function which receives input as only argument and returns previews_container
     * @returns {CmfAsyncFilesUploader|jQuery}
     */
    $.fn.filesUploader = function (options) {
        if (this.length === 1 && typeof options === 'undefined') {
            return $(this[0]).data('filesUploader');
        }
        this.each(function (index, el) {
            if (!$(el).data('filesUploader')) {
                var previewsContainer = null;
                if (options) {
                    if (options.previews_container_finder && typeof options.previews_container_finder === 'function') {
                        previewsContainer = options.previews_container_finder(el);
                    } else if (options.previews_container) {
                        previewsContainer = options.previews_container;
                    }
                }
                delete options.previews_container_finder;
                delete options.previews_container;
                var uploader = new CmfAsyncFilesUploader(el, previewsContainer, options);
                $(el).data('filesUploader', uploader);
            }
        });
        return this;
    }
});

var CmfAsyncFilesUploaderHelper = {

};

/**
 * @param {string} templatesBaseId
 * @param {Object} configs
 * @param {Object} locale
 * @param {Object} existingFiles
 * @param {null|number} recordPrimaryKeyValue
 * @param {boolean} isCloning
 */
CmfAsyncFilesUploaderHelper.initInputsFromConfigs = function (
    templatesBaseId,
    configs,
    locale,
    existingFiles,
    recordPrimaryKeyValue,
    isCloning
) {
    var previewTpl = doT.template($('#' + templatesBaseId + '-files-uploader-preview-tpl').html());
    var previewProgressBarTpl = doT.template($('#' + templatesBaseId + '-files-uploader-uploading-progress-tpl').html());
    for (var i in configs) {
        configs[i].preview_tpl = previewTpl;
        configs[i].uploading_progress_tpl = previewProgressBarTpl;
        configs[i].locale = locale;
        configs[i].upload_data = {group: i};
        configs[i].delete_data = {group: i};
        configs[i].files = existingFiles[i];
        if (recordPrimaryKeyValue) {
            configs[i].upload_data.id = recordPrimaryKeyValue;
            configs[i].delete_data.id = recordPrimaryKeyValue;
        }
        CmfAsyncFilesUploaderHelper.initInputFromConfigs(configs[i], isCloning);
    }
};

/**
 * @param {Object} config
 * @param {string}      config.id
 * @param {string}      config.name
 * @param {number}      config.min_files_count
 * @param {number}      config.max_files_count
 * @param {function}    config.preview_tpl
 * @param {function}    config.uploading_progress_tpl
 * @param {string}      config.upload_url
 * @param {string}      config.delete_url
 * @param {number}      config.max_width
 * @param {number}      config.max_height
 * @param {number}      config.aspect_ratio
 * @param {number}      config.max_file_size
 * @param {number}      config.preview_width
 * @param {Array}       config.allowed_mime_types
 * @param {Array}       config.files
 * @param {boolean} isCloning
 */
CmfAsyncFilesUploaderHelper.initInputFromConfigs = function (
    config,
    isCloning
) {
    if (!config || !$.isPlainObject(config) || !config.hasOwnProperty('id')) {
        console.error('CmfAsyncFilesUploaderHelper.initInputFromConfigs(config) - config.id not provided');
        return;
    }
    var $fileInput = $('#' + config.id);
    var $previewsContainer = $('#' + config.id + '-previews');
    var $attachFileButton = $('#' + config.id + '-attach-button');
    config.previews_container_finder = function (input) {
        return $previewsContainer.find('> table > tbody');
    };
    if (!$fileInput.length) {
        console.error('File input #' + config.id + ' not found');
        return;
    }
    if (!$previewsContainer.length) {
        console.error('Uploaded files previews container #' + config.id + '-previews not found');
        return;
    }
    $fileInput.on('filescountchange', function (event, totalCount, validCount) {
        console.log('on change');
        if (totalCount > 0) {
            $previewsContainer.removeClass('hidden')
        } else {
            $previewsContainer.addClass('hidden');
        }
    });
    $fileInput.on('filesuploadingstarted', function (event) {
        $(this)
            .closest('form').prop('disabled', true)
            .find('input[type="submit"]').prop('disabled', true);
    });
    $fileInput.on('filesuploadingended', function (event) {
        $(this)
            .closest('form').prop('disabled', false)
            .find('input[type="submit"]').prop('disabled', false);
    });
    $fileInput.filesUploader(config); //< it MUST be placed after declaring event handlers
    $attachFileButton.on('click', function () {
        $fileInput.click();
    });
};
