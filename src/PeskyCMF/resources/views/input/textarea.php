<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('class', 'form-control', false);
$attributesForCreate = $rendererConfig->getAttributesForCreate();
$attributesForEdit = $rendererConfig->getAttributesForEdit();
$visibleOnCreate = (bool)array_get($attributesForCreate, 'visible', true);
$visibleOnEdit = (bool)array_get($attributesForEdit, 'visible', true);
unset($attributesForCreate['visible'], $attributesForEdit['visible']);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($attributesForCreate);
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($attributesForEdit);
?>
<?php if (!$visibleOnCreate) : ?>
    {{? !it.isCreation }}
<?php elseif (!$visibleOnEdit) : ?>
    {{? !!it.isCreation }}
<?php endif; ?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <textarea {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
    >{{! it.<?php echo $fieldConfig->getName(); ?> || '' }}</textarea>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>
