<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $id,
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
    <label for="<?php echo $id; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <input value="{{! it.<?php echo $fieldConfig->getName(); ?> || (it.<?php echo $fieldConfig->getName(); ?> === 0 ? '0' : '') }}"
        {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>
