<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyORM\ORM\TableInterface $model
 * @var string|null $ckeditorInitializer - js function like
        function (textareaSelector) {
            $(textareaSelector).ckeditor();
        }
 * @var string|null $insertJsCode - any js code to be inserted before editor init
 */
$rendererConfig->addAttribute(
    'data-editor-name',
    request()->route()->getParameter('table_name', $model->getTableStructure()->getTableName()) . ':' . $fieldConfig->getName()
);
include __DIR__ . '/textarea.php';
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
