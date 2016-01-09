<?php
/**
 * @var \App\Admin\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \App\Admin\Scaffold\ItemDetails\ItemDetailsConfig $actionConfig
 * @var \App\Admin\Scaffold\ItemDetails\ItemDetailsFieldConfig $fieldConfig
 * @var array|null $options
 * @var \App\Db\BaseDbModel $model
 * @var string $translationPrefix
 */
if (empty($options)) {
    $options = [];
}
$yes = empty($options['yes']) ? trans("cmf::cmf.item_details.field.bool.yes") : $options['yes'];
$no = empty($options['no']) ? trans('cmf::cmf.item_details.field.bool.yes') : $options['no'];
echo '{{? !!it.' . $fieldConfig->getName() . ' }}' . $yes . '{{??}}' . $no . '{{?}}';

