<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
$editorFuncNameSuffix = $actionConfig->getModel()->getAlias() . (studly_case($fieldConfig->getName()));
$configuratorFuncName = 'CkeditorConfig.configure' . $editorFuncNameSuffix;
$initiatedFuncName = 'CkeditorConfig.initiated' . $editorFuncNameSuffix;
include 'textarea.php';
?>

<script type="application/javascript">
    $(document).ready(function () {
        $('#<?php echo $id ?>').ckeditor(
            typeof CkeditorConfig === 'undefined' || typeof <?php echo $configuratorFuncName; ?> === 'undefined' ? {} : <?php echo $configuratorFuncName; ?>(),
            typeof CkeditorConfig === 'undefined' || typeof <?php echo $initiatedFuncName; ?> === 'undefined' ? null : <?php echo $initiatedFuncName; ?>
        );
    });
</script>
