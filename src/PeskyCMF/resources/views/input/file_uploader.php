<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FileFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyORMLaravel\Db\Column\FileColumn $column
 * @var \PeskyORMLaravel\Db\Column\Utils\FileConfig $fileConfig
 */
$column = $valueViewer->getTableColumn();
$inputId = $valueViewer->getDefaultId();
$configName = 'default';
$configNameToInputId = [$configName => array_merge(['id' => $inputId], $fileConfig->getConfigsArrayForJs())];
$inputName = $valueViewer->getName(true);
$isImages = $column->isItAnImage();
?>

<div id="<?php echo $inputId; ?>-top-container">
    <div class="section-divider">
        <span><?php echo $valueViewer->getLabel() . ($fileConfig->getMinFilesCount() > 0 ? '*' : ''); ?></span>
    </div>
    <script type="text/html" id="<?php echo $inputId ?>-tpl">
        <div class="file-upload-input-container <?php echo $isImages ? 'image-upload' : ''; ?> form-group mb15 col-xs-12 col-md-<?php echo $fileConfig->getMaxFilesCount() > 1 ? '6' : '12' ?>"
            data-id="<?php echo $inputId; ?>">
            <input type="file" class="file-loading" data-old-file-uuid="{{= it.uuid }}"
                   id="<?php echo $inputId; ?>" name="<?php echo $inputName; ?>[file]">
            {{? it.uuid }}
                <input type="hidden" value="{{= it.uuid }}"
                       id="<?php echo $inputId; ?>-uuid" name="<?php echo $inputName; ?>[uuid]">
                <input type="hidden" value="0"
                       id="<?php echo $inputId; ?>-deleted" name="<?php echo $inputName; ?>[deleted]">
            {{??}}
                <input type="hidden" disabled value="" name="<?php echo $inputName; ?>[uuid]">
            {{?}}
            <input type="hidden" value="{{= it.info || '{}' }}"
                   id="<?php echo $inputId; ?>-info" name="<?php echo $inputName; ?>[info]">
            {{? it.is_cloning }}
                <input type="hidden" value="{{= it.file_data || '{}' }}"
                       id="<?php echo $inputId; ?>-file-data" name="<?php echo $inputName; ?>[file_data]">
            {{?}}
        </div>
    </script>
    <div id="<?php echo $inputId ?>-container" class="row">

    </div>
    <div class="form-group">
        <input type="hidden" disabled name="<?php echo $inputName; ?>[]" id="<?php echo $inputId; ?>-arr-for-errors">
        <input type="hidden" disabled name="<?php echo $inputName; ?>" id="<?php echo $inputId; ?>-noarr-for-errors">
        <?php echo $valueViewer->getFormattedTooltip(); ?>
    </div>
</div>

<script type="application/javascript">
    $(function () {
        var data = {
            files: <?php echo $valueViewer->getDotJsInsertForValue([], 'json_encode') ?>,
            configs: <?php echo json_encode($configNameToInputId); ?>,
            is_cloning: {{= !!it._is_cloning }},
            is_in_modal: {{= !!it.__modal }}
        };
        CmfFileUploads.initFileUploader(
            data,
            '<?php echo $configName; ?>',
            <?php echo $isImages ? 'true' : 'false'; ?>,
            <?php echo json_encode($valueViewer->getJsPluginOptions()); ?>
        );
    });
</script>