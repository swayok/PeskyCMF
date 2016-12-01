<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
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
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <input value="{{! it.<?php echo $fieldConfig->getName(); ?> || (it.<?php echo $fieldConfig->getName(); ?> === 0 ? '0' : '') }}"
        {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>
