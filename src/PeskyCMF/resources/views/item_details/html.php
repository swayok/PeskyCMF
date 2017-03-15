<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>
{{? $.trim(<?php echo $valueViewer->getFailsafeValueForDotJs() ?>).length }}
</td></tr>
<tr><td colspan="2" class="item-details-html-cell">
    <?php echo $valueViewer->getDotJsInsertForValue([], 'srting', null, false); ?>
{{?}}
