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
    'type' => 'password',
    'class' => 'form-control',
    'autocomplete' => 'off'
);
$attributesForCreate = $rendererConfig->getAttributesForCreate();
$attributesForEdit = $rendererConfig->getAttributesForEdit();
$visibleOnCreate = !array_key_exists('visible', $attributesForCreate) || !empty($attributesForCreate['visible']);
$visibleOnEdit = !array_key_exists('visible', $attributesForEdit) || !empty($attributesForEdit['visible']);
unset($attributesForCreate['visible'], $attributesForEdit['visible'], $attributesForEdit['value'], $attributesForEdit['value']);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForCreate));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForEdit));
?>
<?php if (!$visibleOnCreate) : ?>
    {{? !it.isCreation }}
<?php elseif (!$visibleOnEdit) : ?>
    {{? !!it.isCreation }}
<?php endif; ?>

<!-- disable chrome email & password autofill -->
<input type="text" name="login" class="hidden" formnovalidate disabled>
<input type="password" class="hidden" formnovalidate disabled>
<input type="text" name="email" class="hidden" formnovalidate value="test@test.com" disabled>
<input type="password" class="hidden" formnovalidate disabled>
<!-- end of autofill disabler -->
<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>
