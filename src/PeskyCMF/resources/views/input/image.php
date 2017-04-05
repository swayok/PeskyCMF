<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $fieldConfig->getDefaultId(),
    'type' => 'file',
);
$attributesForCreate = array_merge($attributes, $rendererConfig->getAttributesForCreate());
$attributesForCreateAsString = \Swayok\Html\Tag::buildAttributes($attributesForCreate);
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>
<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <input {{? !!it.isCreation }}<?php echo $attributesForCreateAsString; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    {{? !it.isCreation && !!it.<?php echo $fieldConfig->getName(); ?> }}
    <div class="image-preview mt10" id="<?php echo $attributesForCreate['id']; ?>-image-preview">
        <img src="{{= it.<?php echo $fieldConfig->getName(); ?> }}?_=<?php echo time() ?>">
    </div>
    {{?}}
</div>