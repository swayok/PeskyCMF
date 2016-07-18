<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
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
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <select {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
        <?php if ($isMultiple): ?>
            data-value="{{! it.<?php echo $fieldConfig->getName(); ?> && $.isArray(it.<?php echo $fieldConfig->getName(); ?>) ? JSON.stringify(it.<?php echo $fieldConfig->getName(); ?>) : (it.<?php echo $fieldConfig->getName(); ?> || '[]') }}"
        <?php else: ?>
            data-value="{{! it.<?php echo $fieldConfig->getName(); ?> || '<?php echo $isMultiple ? '[]' : ''; ?>' }}"
        <?php endif; ?>
    >
    <?php if (!$fieldConfig->hasOptionsLoader()) : ?>
        <?php if ($rendererConfig->areOptionsDifferent()) : ?>
            {{? !!it.isCreation }}
            <?php foreach ($rendererConfig->getOptionsForCreate() as $value => $label): ?>
                <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
            {{??}}
            <?php foreach ($rendererConfig->getAttributesForEdit() as $value => $label): ?>
                <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
            {{?}}
        <?php else : ?>
            <?php foreach ($rendererConfig->getOptions() as $value => $label): ?>
                <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        {{? it._options && it._options.<?php echo $fieldConfig->getName(); ?> }}
        {{= it._options.<?php echo $fieldConfig->getName(); ?> }}
        {{?}}
    <?php endif; ?>
    </select>
</div>