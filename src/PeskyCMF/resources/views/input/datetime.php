<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var array $config
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName(), false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('type','text', false)
    ->addAttribute('class', 'form-control', false);
$attributesForCreate = $rendererConfig->getAttributesForCreate();
$attributesForEdit = $rendererConfig->getAttributesForEdit();
$visibleOnCreate = (bool)array_get($attributesForCreate, 'visible', false);
$visibleOnEdit = (bool)array_get($attributesForEdit, 'visible', false);
unset($attributesForCreate['visible'], $attributesForEdit['visible']);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($attributesForCreate);
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($attributesForEdit);
?>
<?php if (!$visibleOnCreate) : ?>
    {{? !it.isCreation }}
<?php elseif (!$visibleOnEdit) : ?>
    {{? !!it.isCreation }}
<?php endif; ?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <div class="input-group w200">
        <input value="{{! it.<?php echo $fieldConfig->getName(); ?> || '' }}"
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
        <div class="input-group-addon cursor">
            <i class="fa fa-calendar"></i>
        </div>
    </div>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>

<?php if (!$visibleOnCreate || !$visibleOnEdit) : ?>
    {{?}}
<?php endif; ?>

<?php
if (empty($config)) {
    $config = [];
}
$config = array_merge([
    'useCurrent' => $rendererConfig->isRequired() ? true : false,
    'minDate' => date('Y-m-d H:i'),
    'format' => 'YYYY-MM-DD HH:mm',
    'locale' => app()->getLocale(),
    'sideBySide' => true
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

