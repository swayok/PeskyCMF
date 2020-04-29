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
    ->addAttribute('class', 'styled', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>
<div class="checkbox checkbox-primary">
    <input name="<?php echo $fieldConfig->getName(); ?>" id="_<?php echo $rendererConfig->getAttribute('id'); ?>" type="hidden" value="0">
    <input {{? !!it.<?php echo $fieldConfig->getName(); ?> }}checked{{?}}
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel(); ?></label>
</div>