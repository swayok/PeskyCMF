<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var string|null $ckeditorInitializer - js function like
        function (textareaSelector) {
            $(textareaSelector).ckeditor();
        }
 */
$id = $fieldConfig->getName() . '-input';
include 'textarea.php';
?>

<script type="application/javascript">
    $(document).ready(function () {
        <?php if (empty($ckeditorInitializer)) : ?>
            $('#<?php echo $id ?>').ckeditor();
        <?php else: ?>
            <?php echo $ckeditorInitializer; ?>('#<?php echo $id ?>');
        <?php endif; ?>
    });
</script>
