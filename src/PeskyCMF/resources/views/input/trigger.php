<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfTable $model
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(), false)
    ->addAttribute('type', 'checkbox', true)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('value', 1, false)
    ->addAttribute('class', 'switch', true);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>
<div class="form-group">
    <input name="<?php echo $fieldConfig->getName(); ?>" id="_<?php echo $rendererConfig->getAttribute('id'); ?>" type="hidden" value="0">
    <label class="ib mr15 lh35" for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <div class="ib">
        <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
            {{? !!it.<?php echo $fieldConfig->getName(); ?> }}checked{{?}}
            data-on-text="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.field.bool.yes') ?>"
            data-off-text="<?php echo \PeskyCMF\Config\CmfConfig::transBase('.form.field.bool.no') ?>">
    </div>
</div>