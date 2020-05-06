<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
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
<pre class="json-text" id="<?php echo $id; ?>">
</pre>

<script type="application/javascript">
    $(function () {
        Utils
            .requireFiles(
                ['/packages/cmf/raw/jquery-json-tree/jquery-json-tree.js'],
                ['/packages/cmf/raw/jquery-json-tree/jquery-json-tree.css']
            )
            .done(function () {
                $('#<?php echo $id; ?>').jsonView(
                    <?php echo $valueViewer->getDotJsInsertForValue([], 'json_encode'); ?>,
                    <?php echo json_encode($pluginOptions, JSON_UNESCAPED_UNICODE); ?>
                );
            });
    });
</script>
