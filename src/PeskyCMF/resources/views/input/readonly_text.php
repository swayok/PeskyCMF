<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
?>

<div class="form-group">
    <label><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <div>{{= it.<?php echo $fieldConfig->getName(); ?> || '' }}</div>
</div>