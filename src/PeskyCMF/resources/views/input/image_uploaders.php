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

<div id="<?php echo $defaultId; ?>-container"></div>
<?php foreach ($column as $configName => $imageConfig): ?>
<?php
    $inputId = $defaultId . '-' . preg_replace('%[^a-zA-Z0-9]+%', '-', $configName);
    $inputName = $fieldConfig->getName() . '[' . $configName . ']';
?>
<div class="section-divider">
    <span><?php echo cmfTransCustom($translationPrefix . '.' . $fieldConfig->getName() . '.' . $configName) ?></span>
</div>
<input type="file" id="<?php echo $inputId; ?>" data-name="<?php echo $inputName; ?>">
<input type="hidden" id="<?php echo $inputId; ?>-base64" name="<?php echo $inputName; ?>">
<?php endforeach; ?>

<script type="application/javascript">
    $("#<?php echo $defaultId; ?>-container").find('input[type="file"]').fileinput();
</script>