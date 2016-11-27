<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 * @var array $config
 * @var array $enabler
 */
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $fieldConfig->getDefaultId(),
    'type' => 'text',
    'class' => 'form-control'
);
$attributesForCreate = $rendererConfig->getAttributesForCreate();
$attributesForEdit = $rendererConfig->getAttributesForEdit();
$visibleOnCreate = !array_key_exists('visible', $attributesForCreate) || !empty($attributesForCreate['visible']);
$visibleOnEdit = !array_key_exists('visible', $attributesForEdit) || !empty($attributesForEdit['visible']);
unset($attributesForCreate['visible'], $attributesForEdit['visible']);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForCreate));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $attributesForEdit));
?>
<?php if (!$visibleOnCreate) : ?>
    {{? !it.isCreation }}
<?php elseif (!$visibleOnEdit) : ?>
    {{? !!it.isCreation }}
<?php endif; ?>

<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <div class="input-group w200">
        <input value="{{! it.<?php echo $fieldConfig->getName(); ?> || '' }}"
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
        <div class="input-group-addon cursor">
            <i class="fa fa-calendar"></i>
        </div>
    </div>
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
    'minDate' => date('Y-m-d'),
    'format' => 'YYYY-MM-DD',
    'locale' => app()->getLocale(),
    'sideBySide' => false
], $config);
?>

<script type="application/javascript">
    setTimeout(function () {
        var $input = $('#<?php echo $attributes['id']; ?>');
        $input.datetimepicker(<?php echo json_encode($config); ?>);
        <?php if (!empty($enabler)) : ?>
            var toggleVisibility = function ($enablerInput) {
                var val = $enablerInput[0].type === 'checkbox' ? $enablerInput[0].checked : $enablerInput.val().length > 0;
                if (val) {
                    $input.closest('.form-group').slideDown(100);
                } else {
                    $input.closest('.form-group').slideUp(100);
                }
            };
            var $enablerInput = $('#<?php echo \PeskyCMF\Scaffold\Form\FormFieldConfig::makeDefaultId($enabler); ?>');
            toggleVisibility($enablerInput);
            $enablerInput.on('change switchChange.bootstrapSwitch', function () {
                toggleVisibility($(this));
            });
        <?php endif; ?>
        $input.parent().find('.input-group-addon.cursor').on('click', function () {
            $input.data("DateTimePicker").show();
        });
    }, 50);
</script>

