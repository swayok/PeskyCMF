<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */

$allAttributes = $rendererConfig->getAttributes();
$isMultiple = $rendererConfig->getAttribute('multiple', false);
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName() . ($isMultiple ? '[]' : ''), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('class', 'form-control selectpicker', false);

$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>
<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <select {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
        <?php if ($isMultiple): ?>
            data-value="{{! it.<?php echo $fieldConfig->getName(); ?> && $.isArray(it.<?php echo $fieldConfig->getName(); ?>) ? JSON.stringify(it.<?php echo $fieldConfig->getName(); ?>) : (it.<?php echo $fieldConfig->getName(); ?> || '[]') }}"
        <?php else: ?>
            data-value="{{! it.<?php echo $fieldConfig->getName(); ?> || '' }}"
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
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>