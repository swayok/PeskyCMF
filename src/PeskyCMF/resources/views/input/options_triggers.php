<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */
$defaultId = $rendererConfig->getAttribute('id', $fieldConfig->getDefaultId());
$defaultName = $rendererConfig->getAttribute('name', $fieldConfig->getName());
?>
<div class="section-divider">
    <span><?php echo $fieldConfig->getLabel(); ?></span>
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
                {{? !!it.<?php echo $fieldConfig->getName(); ?>.<?php echo $optionName; ?> }}checked{{?}}
                data-on-text="<?php echo $rendererConfig->getData('label_enable', \PeskyCMF\Config\CmfConfig::transBase('.form.field.bool.yes')) ?>"
                data-off-text="<?php echo $rendererConfig->getData('label_disable', \PeskyCMF\Config\CmfConfig::transBase('.form.field.bool.no')) ?>">
        </div>
    </div>
    <?php endforeach; ?>
</div>
<hr>