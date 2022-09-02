<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\WysiwygFormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var string|null $ckeditorInitializer - js function like
        function (textareaSelector) {
            $(textareaSelector).ckeditor();
        }
 */
$rendererConfig->addAttribute(
    'data-editor-name',
    $sectionConfig->getScaffoldConfig()->getResourceName() . ':' . $valueViewer->getName()
);
include __DIR__ . '/textarea.php';

?>

<script type="application/javascript">
    $(function () {
        window.CKEDITOR_BASEPATH = '/packages/cmf/raw/ckeditor/';
        Utils.requireFiles(['/packages/cmf/raw/ckeditor/ckeditor.js'])
            .done(function () {
                Utils.requireFiles([
                        '<?php echo $sectionConfig->getCmfConfig()->route('cmf_ckeditor_config_js', ['_' => csrf_token()]) ?>',
                        '/packages/cmf/raw/ckeditor/adapters/jquery.js'
                    ])
                    .done(function () {
                        <?php
                            echo $valueViewer->getCustomJsCode();
                            $initializerArgs = implode(',', [
                                '"#' . $rendererConfig->getAttribute('id') . '"',
                                json_encode($valueViewer->getWysiwygConfig(), JSON_UNESCAPED_UNICODE)
                            ]);
                            echo "{$valueViewer->getWysiwygInitializerFunctionName()}($initializerArgs);";
                        ?>
                    });
            })
    });
</script>