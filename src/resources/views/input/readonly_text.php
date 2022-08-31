<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
?>

<div class="form-group">
    <label><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <div><?php echo $valueViewer->getDotJsInsertForValue([], 'string', null, false); ?></div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>
