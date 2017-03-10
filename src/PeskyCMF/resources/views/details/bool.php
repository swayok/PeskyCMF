<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $fieldConfig
 * @var array|null $options
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
if (empty($options)) {
    $options = [];
}
$yes = empty($options['yes']) ? cmfTransGeneral('.item_details.field.bool.yes') : $options['yes'];
$no = empty($options['no']) ? cmfTransGeneral('.item_details.field.bool.no') : $options['no'];
echo '{{? !!it.' . $fieldConfig->getName() . ' }}' . $yes . '{{??}}' . $no . '{{?}}';

