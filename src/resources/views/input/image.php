<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), true)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('type', 'file', true);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    {{? !it.isCreation && !!(<?php echo $valueViewer->getFailsafeValueForDotJs(); ?>) }}
    <div class="image-preview mb15" id="<?php echo $rendererConfig->getAttribute('id'); ?>-image-preview">
        <img src="<?php echo $valueViewer->getDotJsInsertForValue() ?>?_=<?php echo time() ?>">
    </div>
    {{?}}
    <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>