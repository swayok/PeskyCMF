<?php
/**
 * @var \App\Admin\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \App\Admin\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \App\Admin\Scaffold\Form\FormConfig $actionConfig
 * @var \App\Db\BaseDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
?>
<div class="form-group">
    <label for="<?php echo $id; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <?php
        $attributes = array(
            'name' => $fieldConfig->getName(),
            'id' => $id
        );
        $attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
        $attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
    ?>
    <select class="form-control" data-value="{{! it.<?php echo $fieldConfig->getName(); ?> || ''}}"
        {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
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