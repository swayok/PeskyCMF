<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $fieldConfig->getDefaultId(),
    'type' => 'file',
);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>
<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    {{? !it.isCreation && !!it.icon }}
    <div class="image-preview" id="<?php echo $attributes['id']; ?>-image-preview">
        <img src="{{= it.icon }}?_=<?php echo time() ?>">
    </div>
    {{?}}
</div>