<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$attributes = array(
    'name' => $fieldConfig->getName(),
    'type' => 'checkbox',
    'id' => $fieldConfig->getDefaultId(),
    'value' => 1,
    'class' => 'styled'
);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>
<div class="checkbox checkbox-primary">
    <input name="<?php echo $fieldConfig->getName(); ?>" id="_<?php echo $attributes['id']; ?>" type="hidden" value="0">
    <input {{? !!it.<?php echo $fieldConfig->getName(); ?> }}checked{{?}}
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
</div>