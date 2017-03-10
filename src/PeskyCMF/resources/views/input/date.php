<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var array $config
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(true), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('type', 'text', false)
    ->addAttribute('class', 'form-control', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel($rendererConfig); ?></label>
    <div class="input-group w200">
        <input value="<?php echo $fieldConfig->getDotJsInsertForValue(); ?>"
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
        <div class="input-group-addon cursor">
            <i class="fa fa-calendar"></i>
        </div>
    </div>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>

<?php
if (empty($config)) {
    $config = [];
}
$config = array_merge([
    'useCurrent' => $rendererConfig->isRequired() ? true : false,
    'minDate' => date('Y-m-d'),
    'format' => 'YYYY-MM-DD',
    'locale' => app()->getLocale(),
    'sideBySide' => false
], $config);
?>

<script type="application/javascript">
    setTimeout(function () {
        var $input = $('#<?php echo $rendererConfig->getAttribute('id'); ?>');
        $input.datetimepicker(<?php echo json_encode($config); ?>);
        $input.parent().find('.input-group-addon.cursor').on('click', function () {
            $input.data("DateTimePicker").show();
        });
    }, 50);
</script>
