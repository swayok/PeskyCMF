<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var array|null $options
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var string $translationPrefix
 */
if (empty($options)) {
    $options = [];
}
$yes = empty($options['yes']) ? \PeskyCMF\Config\CmfConfig::transBase('.item_details.field.bool.yes') : $options['yes'];
$no = empty($options['no']) ? \PeskyCMF\Config\CmfConfig::transBase('.item_details.field.bool.no') : $options['no'];
echo '{{? !!it.' . $fieldConfig->getName() . ' }}' . $yes . '{{??}}' . $no . '{{?}}';

