<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var array|null $options
 * @var \PeskyORM\ORM\TableInterface $table
 */
if (empty($options)) {
    $options = [];
}
$yes = empty($options['yes']) ? cmfTransGeneral('.item_details.field.bool.yes') : $options['yes'];
$no = empty($options['no']) ? cmfTransGeneral('.item_details.field.bool.no') : $options['no'];
echo '{{? !!it.' . $valueViewer->getName() . ' }}' . $yes . '{{??}}' . $no . '{{?}}';

