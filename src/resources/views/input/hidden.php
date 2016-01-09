<?php
/**
 * @var \App\Admin\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \App\Admin\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \App\Admin\Scaffold\Form\FormConfig $actionConfig
 * @var \App\Db\BaseDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $id,
);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate(), ['type' => 'hidden']));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit(), ['type' => 'hidden']));
?>
<input value="{{= it.<?php echo $fieldConfig->getName(); ?> || '' }}"
    {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>