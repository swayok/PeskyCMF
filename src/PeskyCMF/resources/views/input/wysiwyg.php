<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\WysiwygFormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyORM\ORM\TableInterface $model
 * @var string|null $ckeditorInitializer - js function like
        * function (textareaSelector) {
            * $(textareaSelector).ckeditor();
        * }
 */
$rendererConfig->addAttribute(
    'data-editor-name',
    request()->route()->getParameter('table_name', $model->getTableStructure()->getTableName()) . ':' . $fieldConfig->getName()
);
include __DIR__ . '/textarea.php';

?>

<script type="application/javascript">
    $(document).ready(function () {
        <?php
            echo $fieldConfig->getCustomJsCode();
            $initializerArgs = implode(',', [
                '"#' . $rendererConfig->getAttribute('id') . '"',
                json_encode($fieldConfig->getWysiwygConfig(), JSON_UNESCAPED_UNICODE)
            ]);
            echo "{$fieldConfig->getWysiwygInitializerFunctionName()}($initializerArgs);";
        ?>
    });
</script>