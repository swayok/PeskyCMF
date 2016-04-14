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
    'type' => 'text',
    'class' => 'form-control'
);
$attributesForCreate = $rendererConfig->getAttributesForCreate();
$attributesForEdit = $rendererConfig->getAttributesForEdit();
$visibleOnCreate = !array_key_exists('visible', $attributesForCreate) || !empty($attributesForCreate['visible']);
$visibleOnEdit = !array_key_exists('visible', $attributesForEdit) || !empty($attributesForEdit['visible']);
unset($attributesForCreate['visible'], $attributesForEdit['visible']);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForCreate));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForEdit));
?>
<?php if (!$visibleOnCreate) : ?>
    {{? !it.isCreation }}
<?php elseif (!$visibleOnEdit) : ?>
    {{? !!it.isCreation }}
<?php endif; ?>

<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <textarea {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
    >{{! it.<?php echo $fieldConfig->getName(); ?> || '' }}</textarea>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>
