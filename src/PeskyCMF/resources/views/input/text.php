<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(true), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('class', 'form-control', false)
    ->addAttribute('type', 'text', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
$hasAddons = $rendererConfig->hasPrefixText() || $rendererConfig->hasSuffixText();
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel($rendererConfig); ?></label>
    <?php if ($hasAddons) : ?>
        <div class="input-group">
            <?php if ($rendererConfig->hasPrefixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getPrefixText(); ?></span>
            <?php endif;?>
    <?php endif; ?>

            <input value="<?php echo $fieldConfig->getDotJsInsertForValue() ?>"
                {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>

    <?php if ($hasAddons) : ?>
            <?php if ($rendererConfig->hasSuffixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getSuffixText(); ?></span>
            <?php endif ;?>
        </div>
    <?php endif; ?>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>
