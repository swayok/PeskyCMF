<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$defaultId = $rendererConfig->getAttribute('id', $valueViewer->getDefaultId());
$defaultName = $rendererConfig->getAttribute('name', $valueViewer->getName(true));
?>

<div class="section-divider">
    <span><?php echo $valueViewer->getLabel($rendererConfig); ?></span>
</div>
<div class="form-group">
    <?php foreach ($rendererConfig->getOptions() as $optionName => $optionLabel): ?>
    <?php
        $rendererConfig
            ->addAttribute('name', "{$defaultName}[{$optionName}]", true)
            ->addAttribute('type', 'checkbox', true)
            ->addAttribute('id',  "{$defaultId}-{$optionName}" , true)
            ->addAttribute('value', 1, false)
            ->addAttribute('class', 'switch', true);
        $attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
        $attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
    ?>
    <input name="<?php echo $rendererConfig->getAttribute('name'); ?>" id="_<?php echo $rendererConfig->getAttribute('id'); ?>" type="hidden" value="0">
    <div class="row">
        <label class="lh35 <?php echo $rendererConfig->getData('grid_class_for_label', 'col-xs-8 col-md-6'); ?>"
        for="<?php echo $rendererConfig->getAttribute('id') ?>">
            <?php echo $optionLabel; ?>
        </label>
        <div class="<?php echo $rendererConfig->getData('grid_class_for_input', 'col-xs-4 col-md-6'); ?>">
            <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}
                <?php echo $valueViewer->getConditionalDotJsInsertForValue('checked', '', [$optionName]); ?>
                data-on-text="<?php echo $rendererConfig->getData('label_enable', cmfTransGeneral('.form.input.bool.yes')) ?>"
                data-off-text="<?php echo $rendererConfig->getData('label_disable', cmfTransGeneral('.form.input.bool.no')) ?>">
        </div>
    </div>
    <?php endforeach; ?>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>
<hr style="border-color: #ddd">