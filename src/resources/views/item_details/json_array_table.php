<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\JsonArrayValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>
<table class="table table-striped table-condensed table-bordered mn" id="<?php echo $valueViewer->getHtmlElementId() ?>">
<thead>
    <tr>
        <?php
            foreach ($valueViewer->getTableHeaders() as $header) {
                echo "<th>{$header}</th>";
            }
        ?>
    </tr>
</thead>
<tbody>
    {{~ <?php echo $valueViewer->getFailsafeValueForDotJs([], 'json', '[]'); ?> :row }}
        {{? $.isPlainObject(row) }}
        <tr>
            <?php foreach ($valueViewer->getJsonKeys() as $key): ?>
                <td>{{= row.<?php echo $key; ?> || '' }}</td>
            <?php endforeach; ?>
        </tr>
        {{?}}
    {{~}}
</tbody>
</table>