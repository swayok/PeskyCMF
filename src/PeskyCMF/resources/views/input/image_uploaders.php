<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\ImagesFormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var string $translationPrefix
 */
/** @var \PeskyCMF\Db\Column\ImagesColumn $column */
$column = $fieldConfig->getTableColumn();
$defaultId = $fieldConfig->getDefaultId();
$dotJsInputName = 'it.' . $fieldConfig->getName();
$configNameToInputId = [];
?>

<div id="<?php echo $defaultId; ?>-container">
    <?php foreach ($column as $configName => $imageConfig): ?>
        <?php
            $inputId = $defaultId . '-' . preg_replace('%[^a-zA-Z0-9]+%', '-', $configName);
            $configNameToInputId[$configName] = array_merge(['id' => $inputId], $imageConfig->getConfigsArrayForJs());
            $inputName = $fieldConfig->getName() . '[' . $configName . ']';
        ?>
        <div class="section-divider">
            <span><?php echo cmfTransCustom($translationPrefix . '.' . $fieldConfig->getName() . '.' . $configName) ?></span>
        </div>
        <div class="image-upload-input-container">
            <input type="file" id="<?php echo $inputId; ?>" data-name="<?php echo $inputName; ?>" class="file-loading" name="<?php echo $inputName; ?>[file]">
            <input type="hidden" id="<?php echo $inputId; ?>-info" name="<?php echo $inputName; ?>[info]" value="{}">
            <input type="hidden" id="<?php echo $inputId; ?>-deleted" name="<?php echo $inputName; ?>[deleted]" value="0">
        </div>
    <?php endforeach; ?>
</div>

<script type="application/javascript">
    $(function () {
        Utils.requireFiles(['/packages/cmf/js/inputs/cmf.fileuploads.js']).done(function () {
            var data = {
                files: {{= JSON.stringify(<?php echo $dotJsInputName ?>) }},
                configs: <?php echo json_encode($configNameToInputId); ?>
            };
            CmfFileUploads.initImageUploaders(data);
        });
    });
</script>
