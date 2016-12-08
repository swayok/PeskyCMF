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
$defaultId = $fieldConfig->getDefaultId()
?>

<div id="<?php echo $defaultId; ?>-container">
    <?php foreach ($column as $configName => $imageConfig): ?>
        <?php
            $inputId = $defaultId . '-' . preg_replace('%[^a-zA-Z0-9]+%', '-', $configName);
            $inputName = $fieldConfig->getName() . '[' . $configName . ']';
        ?>
        <div class="section-divider">
            <span><?php echo cmfTransCustom($translationPrefix . '.' . $fieldConfig->getName() . '.' . $configName) ?></span>
        </div>
        <div class="image-upload-input-container">
            <input type="file" id="<?php echo $inputId; ?>" data-name="<?php echo $inputName; ?>" class="file-loading" name="<?php echo $inputName; ?>[file]">
            <input type="hidden" id="<?php echo $inputId; ?>-info" name="<?php echo $inputName; ?>[info]" value="{}">
            <input type="hidden" id="<?php echo $inputId; ?>-deleted" name="<?php echo $inputName; ?>[deleted]" value="0">
            <script type="application/javascript">
                $("#<?php echo $inputId; ?>").fileinput({
                    language: '<?php echo app()->getLocale(); ?>',
                    allowedFileTypes: ['image'],
                    previewFileType: 'image',
                    allowedFileExtensions: <?php echo json_encode($imageConfig->getAllowedFileExtensions()); ?>,
                    minFileCount: <?php echo $imageConfig->getMinFilesCount(); ?>,
                    <?php if ($imageConfig->getMaxFilesCount() > 0): ?>
                    maxFileCount: <?php echo $imageConfig->getMaxFilesCount(); ?>,
                    <?php endif; ?>
                    validateInitialCount: true,
                    maxFileSize: <?php echo $imageConfig->getMaxFileSize(); ?>,
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
                    }
                });
            </script>
        </div>
    <?php endforeach; ?>
</div>
