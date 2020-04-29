<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfTable $model
 */
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $fieldConfig->getDefaultId(),
);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate(), ['type' => 'hidden']));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit(), ['type' => 'hidden']));
?>
<input value="{{= it.<?php echo $fieldConfig->getName(); ?> || '' }}"
    {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>