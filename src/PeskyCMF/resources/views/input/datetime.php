<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var array $config
 * @var array $enabler
 */
$id = $fieldConfig->getName() . '-input';
$attributes = array(
    'name' => $fieldConfig->getName(),
    'id' => $id,
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
    <label for="<?php echo $id; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
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
    'useCurrent' => true,
    'minDate' => date('Y-m-d H:i'),
    'format' => 'YYYY-MM-DD HH:mm',
    'locale' => app()->getLocale(),
    'sideBySide' => true
], $config);
?>

<script type="application/javascript">
    setTimeout(function () {
        var $input = $('#<?php echo $id; ?>');
        $input.datetimepicker(<?php echo json_encode($config); ?>);
        <?php if (!empty($enabler)) : ?>
            $('#<?php echo $enabler; ?>-input').on('change', function () {
                var val = this.type === 'checkbox' ? this.checked : $(this).val().length > 0;
                if (val) {
                    $input.closest('.form-group').show();
                } else {
                    $input.closest('.form-group').hide();
                }
            }).change();
        <?php endif; ?>
        $input.parent().find('.input-group-addon.cursor').on('click', function () {
            $input.data("DateTimePicker").show();
        });
    }, 50);
</script>

