<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), false)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('class', 'form-control', false)
    ->addAttribute('type', 'text', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
$hasAddons = $rendererConfig->hasPrefixText() || $rendererConfig->hasSuffixText();
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <?php if ($hasAddons) : ?>
        <div class="input-group">
            <?php if ($rendererConfig->hasPrefixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getPrefixText(); ?></span>
            <?php endif;?>
    <?php endif; ?>
            <?php if ($rendererConfig->getAttribute('type') === 'password'): ?>
                <input type="password" name="<?php echo $rendererConfig->getAttribute('name') ?>" class="hidden" formnovalidate disabled>
            <?php endif; ?>
            <input value="<?php echo $valueViewer->getDotJsInsertForValue() ?>"
                {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>

    <?php if ($hasAddons) : ?>
            <?php if ($rendererConfig->hasSuffixText()) : ?>
                <span class="input-group-addon"><?php echo $rendererConfig->getSuffixText(); ?></span>
            <?php endif ;?>
        </div>
    <?php endif; ?>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>
