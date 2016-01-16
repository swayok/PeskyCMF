<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \App\Db\BaseDbModel $model
 */
$id = $fieldConfig->getName() . '-input';
?>
<div class="checkbox checkbox-primary">
    <?php
        $attributes = array(
            'name' => $fieldConfig->getName(),
            'type' => 'checkbox',
            'id' => $id,
            'value' => 1,
            'class' => 'styled'
        );
        $attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
        $attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
    ?>
    <input name="<?php echo $fieldConfig->getName(); ?>" id="_<?php echo $id; ?>" type="hidden" value="0">
    <input {{? !!it.<?php echo $fieldConfig->getName(); ?> }}checked{{?}}
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
    <label for="<?php echo $id; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
</div>