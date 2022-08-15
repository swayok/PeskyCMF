<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\AsyncFilesFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyORMLaravel\Db\Column\FilesColumn $column
 * @var \PeskyORMLaravel\Db\Column\Utils\FilesGroupConfig[] $filesConfigs
 */
$column = $valueViewer->getTableColumn();
$defaultId = $valueViewer->getDefaultId();
$configNameToInputId = [];
$isImages = $column->isItAnImage();
?>

<div id="<?php echo $defaultId; ?>-container">
    <?php foreach ($filesConfigs as $configName => $fileConfig): ?>
        <?php
            $inputId = $defaultId . '-' . preg_replace('%[^a-zA-Z0-9]+%', '-', $configName);
            $inputName = $valueViewer->getName(true) . '[' . $configName . ']';
            $configNameToInputId[$configName] = array_merge(
                $valueViewer->getConfigsArrayForJs($configName),
                $fileConfig->getConfigsArrayForJs(),
                [
                    'id' => $inputId,
                    'name' => $inputName . '[]'
                ]
            );

        ?>
        <div class="section-divider">
            <span><?php echo $sectionConfig->translate($valueViewer, $configName); ?></span>
        </div>
        <div
            id="<?php echo $inputId ?>-previews"
            class="form-group fluid-width files-uploader mb10 hidden"
        >
            <table class="table table-bordered table-condensed">
                <tbody></tbody>
            </table>
        </div>
        <div class="form-group">
            <input type="hidden" disabled name="<?php echo $inputName; ?>[]" id="<?php echo $inputId; ?>-arr-for-errors">
            <input type="hidden" disabled name="<?php echo $inputName; ?>" id="<?php echo $inputId; ?>-noarr-for-errors">
            <?php echo $valueViewer->getFormattedTooltipForFileConfig($configName); ?>
        </div>
        <div class="mv15 text-center">
            <input
                type="file"
                style="display: none !important;"
                <?php echo $fileConfig->getMaxFilesCount() !== 1 ? 'multiple' : '' ?>
                id="<?php echo $inputId; ?>"
            >
            <button type="button" class="btn btn-default btn-sm" id="<?php echo $inputId; ?>-attach-button">
                <?php echo $sectionConfig->translateGeneral('input.file_uploads.' . ($isImages ? 'add_image' : 'add_file')) ?>
            </button>
        </div>
    <?php endforeach; ?>
</div>

<script type="text/html" id="<?php echo $defaultId; ?>-files-uploader-preview-tpl">
    <tr class="files-uploader-file" data-uid="{{= it.uid }}">
        <td class="files-uploader-file-preview va-t text-center bg-white" width="{{= it.preview_width }}">
            {{? it.is_image }}
                <div class="files-uploader-file-preview-image">
                    {{= it.preview }}
                </div>
            {{??}}
                <div class="files-uploader-file-preview-file pt5 pb5">
                    {{= it.preview }}
                </div>
            {{?}}
        </td>
        <td class="files-uploader-file-info va-t fluid-width bg-white" {{= it.is_uploading || it.can_retry || it.can_delete ? '' : 'colspan="2"' }}>
            <div class="files-uploader-file-name">
                <span class="files-uploader-file-info-label">
                    <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.file_name') ?>:
                </span>
                <span class="files-uploader-file-info-value">{{= it.name }}</span>
            </div>
            <div class="files-uploader-file-size mt5">
                <span class="files-uploader-file-info-label">
                    <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.file_size') ?>:
                </span>
                <span class="files-uploader-file-info-value">
                    {{= it.size }} <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.file_size_measure_mb') ?>
                    {{? it.is_uploaded }}
                        {{? it.error }}
                            <i
                                class="fa fa-warning text-warning fs14 ml5"
                                data-toggle="tooltip"
                                title="<?php echo $sectionConfig->translateGeneral('input.async_files_uploads.tooltip.failed_to_upload') ?>"
                            ></i>
                        {{??}}
                            <i
                                class="fa fa-check-circle text-success fs14 ml5"
                                data-toggle="tooltip"
                                title="<?php echo $sectionConfig->translateGeneral('input.async_files_uploads.tooltip.uploaded') ?>"
                            ></i>
                        {{?}}
                    {{?}}
                </span>
            </div>
            {{? it.is_image }}
                <div class="files-uploader-image-dimensions mt5">
                    <span class="files-uploader-file-info-label">
                        <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.image_dimensions') ?>:
                    </span>
                    <span class="files-uploader-file-info-value">{{= it.width }}x{{= it.height }}</span>
                </div>
            {{?}}
            {{? it.is_uploading }}
                <div class="files-uploader-file-upload-status mt10" data-container="progress-bar">
                    {{= it.progress_bar }}
                </div>
            {{?}}
            {{? it.error }}
                <div class="files-uploader-file-error text-danger mt5">
                    {{= it.error }}
                </div>
            {{?}}

            {{? it.uploaded_file_info }}
                <input
                    type="hidden"
                    name="{{= it.input_name }}"
                    value="{{= it.uploaded_file_info }}"
                >
            {{?}}
        </td>
        {{? it.is_uploading || it.can_retry || it.can_delete }}
            <td class="files-uploader-file-actions va-t posr bg-white" width="70">
                {{? !it.is_uploading }}
                    {{? it.can_delete }}
                        <button
                            type="button"
                            class="btn btn-danger btn-xs fs12 files-uploader-file-delete fluid-width mb10"
                            data-uid="{{= it.uid }}"
                            data-existing="{{= it.is_new ? '0' : '1' }}"
                        >
                            <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.delete_file') ?>
                        </button>
                    {{?}}
                    {{? it.is_new && it.can_retry }}
                        <button
                            type="button"
                            class="btn btn-default btn-xs fs12 files-uploader-file-retry fluid-width"
                            data-uid="{{= it.uid }}"
                        >
                            <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.retry_upload') ?>
                        </button>
                    {{?}}
                {{??}}
                    <button
                        type="button"
                        class="btn btn-default btn-xs fs12 files-uploader-file-cancel fluid-width"
                        data-uid="{{= it.uid }}"
                    >
                        <?php echo $sectionConfig->translateGeneral('input.async_files_uploads.cancel_uploading') ?>
                    </button>
                {{?}}
                <div
                    class="files-uploader-dragger text-center"
                    style="cursor: move; cursor: row-resize; position: absolute; bottom: 10px; right: 0; width: 100%"
                    title="<?php echo $sectionConfig->translateGeneral('input.async_files_uploads.reorder') ?>"
                >
                    <i class="fa fa-arrows fs18 text-muted"></i>
                </div>
            </td>
        {{?}}
    </tr>
</script>

<script type="text/html" id="<?php echo $defaultId; ?>-files-uploader-uploading-progress-tpl">
    <div>
        <div class="progress progress-bar-xs mbn">
            <div
                class="progress-bar progress-bar-info"
                role="progressbar"
                aria-valuenow="{{= it.uploaded_percent }}"
                aria-valuemin="0"
                aria-valuemax="100"
                style="width: {{= it.uploaded_percent }}%;"
            >
            </div>
        </div>
        <div class="text-info fw600 text-center mt5 progress-bar-percent">{{= it.uploaded_percent }}%</div>
    </div>
</script>

<script type="application/javascript">
    $(function () {
        var files = <?php echo $valueViewer->getDotJsInsertForValue([], 'json_encode') ?>;
        var configs = <?php echo json_encode($configNameToInputId); ?>;
        var locale = <?php echo json_encode($sectionConfig->translateGeneral('input.async_files_uploads.js_locale')); ?>;
        CmfAsyncFilesUploaderHelper.initInputsFromConfigs(
            '<?php echo $defaultId; ?>',
            configs,
            locale,
            files,
            {{= it.___pk_value || 'null' }},
            {{= !!it._is_cloning ? 'true' : 'false' }}
        );
    });
</script>
