<?php
/**
 * @var \App\Admin\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \App\Admin\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \App\Admin\Scaffold\Form\FormConfig $actionConfig
 * @var \App\Db\BaseDbModel $model
 */
?>

<div class="form-group">
    <label><?php echo $fieldConfig->getLabel(); ?></label>
    <div>{{= it.<?php echo $fieldConfig->getName(); ?> || '' }}</div>
</div>