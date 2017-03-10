<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), false)
    ->addAttribute('type', 'checkbox', true)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('value', 1, false)
    ->addAttribute('class', 'styled', false)
    ->addAttribute('required', false, false)
    ->setIsRequired(false)
    ->setIsRequiredForCreate(false)
    ->setIsRequiredForEdit(false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="checkbox checkbox-primary">
    <input name="<?php echo $rendererConfig->getAttribute('name'); ?>" id="_<?php echo $rendererConfig->getAttribute('id'); ?>" type="hidden" value="0">
    <input <?php echo $valueViewer->getDotJsInsertForValue('checked'); ?>
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>
