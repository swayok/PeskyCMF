<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\WysiwygFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var string|null $ckeditorInitializer - js function like
        * function (textareaSelector) {
            * $(textareaSelector).ckeditor();
        * }
 */
$rendererConfig->addAttribute(
    'data-editor-name',
    request()->route()->parameter('table_name', $table->getTableStructure()->getTableName()) . ':' . $valueViewer->getName()
);
include __DIR__ . '/textarea.php';

?>

<script type="application/javascript">
    $(document).ready(function () {
        <?php
            echo $valueViewer->getCustomJsCode();
            $initializerArgs = implode(',', [
                '"#' . $rendererConfig->getAttribute('id') . '"',
                json_encode($valueViewer->getWysiwygConfig(), JSON_UNESCAPED_UNICODE)
            ]);
            echo "{$valueViewer->getWysiwygInitializerFunctionName()}($initializerArgs);";
        ?>
    });
</script>