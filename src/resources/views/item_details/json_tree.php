<?php

declare(strict_types=1);

/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\Table\TableInterface $table
 * @var array $pluginOptions
 */
$id = 'json-' . \Ramsey\Uuid\Uuid::uuid4()->toString();
if (empty($pluginOptions) || !is_array($pluginOptions)) {
    $pluginOptions = [];
}
$pluginOptions = array_merge([
    'autoOpen' => 1,
], $pluginOptions);
?>
<pre
    class="json-text" id="<?php
echo $id; ?>"
>
</pre>

<script type="application/javascript">
    $(function () {
        Utils
            .requireFiles(
                ['/vendor/peskycmf/raw/jquery-json-tree/jquery-json-tree.js'],
                ['/vendor/peskycmf/raw/jquery-json-tree/jquery-json-tree.css'],
            )
            .done(function () {
                $('#<?php echo $id; ?>').jsonView(
                    <?php echo $valueViewer->getDotJsInsertForValue([], 'string', '{}', false); ?>,
                    <?php echo json_encode($pluginOptions, JSON_UNESCAPED_UNICODE); ?>
                )
            })
    })
</script>
