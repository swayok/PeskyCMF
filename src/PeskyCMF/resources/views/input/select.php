<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$isMultiple = $rendererConfig->getAttribute('multiple', false);
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(true) . ($isMultiple ? '[]' : ''), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('class', 'form-control selectpicker', false);

$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
$isHidden = (bool)$rendererConfig->getData('isHidden', false);
?>

<div class="form-group <?php echo $isHidden ? 'hidden' : ''; ?>">
    <?php if (!$isHidden) : ?>
        <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel($rendererConfig); ?></label>
    <?php endif; ?>
    <select {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
        <?php if ($isMultiple): ?>
            data-value="<?php echo $fieldConfig->getDotJsJsonInsertForValue(true); ?>"
        <?php else: ?>
            data-value="<?php echo $fieldConfig->getDotJsInsertForValue(); ?>"
        <?php endif; ?>
    >
    <?php if (!$fieldConfig->hasOptionsLoader()) : ?>
        <?php if ($rendererConfig->areOptionsDifferent()) : ?>
            {{? !!it.isCreation }}
                <?php $options = $rendererConfig->getOptionsForCreate(); ?>
                <?php if (!$rendererConfig->isRequiredForCreate() && !array_key_exists('', $options)) : ?>
                    <option value=""><?php echo $fieldConfig->getEmptyOptionLabel() ?></option>
                <?php endif; ?>
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            {{??}}
                <?php $options = $rendererConfig->getOptionsForEdit(); ?>
                <?php if (!$rendererConfig->isRequiredForEdit() && !array_key_exists('', $options)) : ?>
                    <option value=""><?php echo $fieldConfig->getEmptyOptionLabel() ?></option>
                <?php endif; ?>
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            {{?}}
        <?php else : ?>
            <?php $options = $rendererConfig->getOptions(); ?>
            <?php if (!$rendererConfig->isRequired() && !array_key_exists('', $options)) : ?>
                <option value=""><?php echo $fieldConfig->getEmptyOptionLabel() ?></option>
            <?php endif; ?>
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        {{? it._options && it._options.<?php echo $fieldConfig->getName(); ?> }}
            {{= it._options.<?php echo $fieldConfig->getName(); ?> }}
        {{?}}
    <?php endif; ?>
    </select>
    <?php if ($rendererConfig->isRequired() && $fieldConfig->hasOptionsLoader()) : ?>
        <script type="application/javascript">
            $('#<?php echo $rendererConfig->getAttribute('id') ?>').find('option[value=""]').remove();
        </script>
    <?php endif; ?>
    <?php echo $isHidden ? '' : $fieldConfig->getFormattedTooltip(); ?>
</div>