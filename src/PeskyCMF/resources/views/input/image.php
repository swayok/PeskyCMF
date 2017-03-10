<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(true), true)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('type', 'file', true);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel($rendererConfig); ?></label>
    <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    {{? !it.isCreation && !!it.icon }}
    <div class="image-preview" id="<?php echo $rendererConfig->getAttribute('id'); ?>-image-preview">
        <img src="<?php echo $fieldConfig->getDotJsInsertForValue() ?>?_=<?php echo time() ?>">
    </div>
    {{?}}
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>