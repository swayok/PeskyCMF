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
                ['/packages/cmf-vendors/jquery-json-tree/jquery-json-tree.js'],
                ['/packages/cmf-vendors/jquery-json-tree/jquery-json-tree.css']
            )
            .done(function () {
                $('#<?php echo $id; ?>').jsonView(
                    <?php echo "{{= it.{$valueViewer->getName()} || ''}}"; ?>,
                    <?php echo json_encode($pluginOptions, JSON_UNESCAPED_UNICODE); ?>
                );
            });
    });
</script>
