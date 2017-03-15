<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>

<div class="form-group">
    <label><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <div><?php echo $valueViewer->getDotJsInsertForValue([], 'json_encode'); ?></div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>
