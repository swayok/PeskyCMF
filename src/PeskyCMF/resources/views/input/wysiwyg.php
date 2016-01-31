<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
$configuratorFuncName = 'CkeditorConfig.configure' . $actionConfig->getModel()->getAlias() . (studly_case($fieldConfig->getName()));
include 'textarea.php';
?>

<script type="application/javascript">
    $(document).ready(function () {
        $('#<?php echo $id ?>').ckeditor(typeof CkeditorConfig === 'undefined' || typeof <?php echo $configuratorFuncName; ?> === 'undefined' ? {} : <?php echo $configuratorFuncName; ?>());
    });
</script>
