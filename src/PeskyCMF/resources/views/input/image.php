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
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <input {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    {{? !it.isCreation && !!(<?php echo $valueViewer->getFailsafeValueForDotJs(); ?>) }}
    <div class="image-preview" id="<?php echo $rendererConfig->getAttribute('id'); ?>-image-preview">
        <img src="<?php echo $valueViewer->getDotJsInsertForValue() ?>?_=<?php echo time() ?>">
    </div>
    {{?}}
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>