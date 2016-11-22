<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var string|null $ckeditorInitializer - js function like
        function (textareaSelector) {
            $(textareaSelector).ckeditor();
        }
 * @var string|null $insertJsCode - any js code to be inserted before editor init
 */
include 'textarea.php';
/**
 * @var array $attributes
 */
?>

<script type="application/javascript">
    $(document).ready(function () {
        <?php if (!empty($insertJsCode)) : ?>
            <?php echo $insertJsCode; ?>
        <?php endif; ?>
        <?php if (empty($ckeditorInitializer)) : ?>
            $('#<?php echo $attributes['id'] ?>').ckeditor();
        <?php else: ?>
            <?php echo $ckeditorInitializer; ?>('#<?php echo $attributes['id'] ?>');
        <?php endif; ?>
    });
</script>
