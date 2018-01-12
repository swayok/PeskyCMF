<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\ImagesFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var \PeskyORMLaravel\Db\Column\ImagesColumn $column
 */
$column = $valueViewer->getTableColumn();
$defaultId = $valueViewer->getDefaultId();
$configNameToInputId = [];
?>

<div id="<?php echo $defaultId; ?>-container">
    <?php foreach ($column as $configName => $imageConfig): ?>
        <?php
            $inputId = $defaultId . '-' . preg_replace('%[^a-zA-Z0-9]+%', '-', $configName);
            $configNameToInputId[$configName] = array_merge(['id' => $inputId], $imageConfig->getConfigsArrayForJs());
            $inputName = $valueViewer->getName(true) . '[' . $configName . ']';
        ?>
        <div class="section-divider">
            <span><?php echo $sectionConfig->translate($valueViewer, $configName); ?></span>
        </div>
        <script type="text/html" id="<?php echo $inputId ?>-tpl">
            <div class="image-upload-input-container form-group mb15 col-xs-12 col-md-<?php echo $imageConfig->getMaxFilesCount() > 1 ? '6' : '12' ?>">
                <input type="file" class="file-loading" data-old-file-uuid="{{= it.uuid }}"
                       id="<?php echo $inputId; ?>-{{= it.index }}" name="<?php echo $inputName; ?>[{{= it.index }}][file]">
                {{? it.uuid }}
                    <input type="hidden" value="{{= it.uuid }}"
                           id="<?php echo $inputId; ?>-{{= it.index }}-uuid" name="<?php echo $inputName; ?>[{{= it.index }}][uuid]">
                    <input type="hidden" value="0"
                           id="<?php echo $inputId; ?>-{{= it.index }}-deleted" name="<?php echo $inputName; ?>[{{= it.index }}][deleted]">
                {{??}}
                    <input type="hidden" disabled value="" name="<?php echo $inputName; ?>[{{= it.index }}][uuid]">
                {{?}}
                <input type="hidden" value="{{= it.info || '{}' }}"
                       id="<?php echo $inputId; ?>-{{= it.index }}-info" name="<?php echo $inputName; ?>[{{= it.index }}][info]">
                <input type="hidden" value="{{= String((it.index || 0) + 1) }}"
                       id="<?php echo $inputId; ?>-{{= it.index }}-position" name="<?php echo $inputName; ?>[{{= it.index }}][position]">
                {{? it.is_cloning }}
                    <input type="hidden" value="{{= it.file_data || '{}' }}"
                           id="<?php echo $inputId; ?>-{{= it.index }}-file-data" name="<?php echo $inputName; ?>[{{= it.index }}][file_data]">
                {{?}}
            </div>
        </script>
        <div id="<?php echo $inputId ?>-container" class="row">

        </div>
        <div class="form-group">
            <input type="hidden" disabled name="<?php echo $inputName; ?>[]" id="<?php echo $inputId; ?>-arr-for-errors">
            <input type="hidden" disabled name="<?php echo $inputName; ?>" id="<?php echo $inputId; ?>-noarr-for-errors">
            <?php for ($i = 0; $i < $imageConfig->getMaxFilesCount(); $i++): ?>
                <input type="hidden" disabled name="<?php echo $inputName; ?>[<?php echo (string)$i; ?>]" id="<?php echo $inputId . '-' . $i; ?>-for-errors">
            <?php endfor; ?>
            <?php echo $valueViewer->getFormattedTooltipForImageConfig($configName); ?>
        </div>
        <?php if ($imageConfig->getMaxFilesCount() > 1): ?>
            <div class="mv15 text-center">
                <button type="button" class="btn btn-default btn-sm" id="<?php echo $inputId; ?>-add">
                    <?php echo $sectionConfig->translateGeneral('input.file_uploads.add_image') ?>
                </button>
            </div>
        <?php endif;?>
    <?php endforeach; ?>
</div>

<script type="application/javascript">
    $(function () {
        Utils.requireFiles(['/packages/cmf/js/inputs/cmf.fileuploads.js']).done(function () {
            var data = {
                files: <?php echo $valueViewer->getDotJsInsertForValue([], 'json_encode') ?>,
                configs: <?php echo json_encode($configNameToInputId); ?>,
                is_cloning: {{= !!it._is_cloning }},
                is_in_modal: {{= !!it.__modal }}
            };
            CmfFileUploads.initImageUploaders(data);
        });
    });
</script>