<?php
declare(strict_types=1);
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
    ->addAttribute('class', 'switch', true)
    ->addAttribute('required', false, true)
    ->setIsRequired(false)
    ->setIsRequiredForCreate(false)
    ->setIsRequiredForEdit(false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="form-group">
    <input name="<?php echo $rendererConfig->getAttribute('name'); ?>" id="_<?php echo $rendererConfig->getAttribute('id'); ?>" type="hidden" value="0">
    <label class="ib mr15 lh35" for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <div class="ib">
        <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
            <?php echo $valueViewer->getConditionalDotJsInsertForValue('checked', '') ?>
            data-on-text="<?php echo $rendererConfig->getData('label_yes', $sectionConfig->getCmfConfig()->transGeneral('.form.input.bool.yes')) ?>"
            data-off-text="<?php echo $rendererConfig->getData('label_no', $sectionConfig->getCmfConfig()->transGeneral('.form.input.bool.no')) ?>">
    </div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>