<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var array $config
 * @var array $enabler
 */
include 'text.php';
/**
 * @var array $attributes
 */
if (empty($config)) {
    $config = [];
}
$config = array_merge(['format' => 'yyyy-mm-dd'], $config);
?>

<script type="application/javascript">
    setTimeout(function () {
        var input = $('#<?php echo $attributes['id']; ?>');
        input.datepicker(<?php echo json_encode($config); ?>);
        <?php if (!empty($enabler)) : ?>
            $('#<?php echo $enabler; ?>-input').on('change', function () {
                var val = this.type === 'checkbox' ? this.checked : $(this).val().length > 0;
                if (val) {
                    input.closest('.form-group').show();
                } else {
                    input.closest('.form-group').hide();
                }
            }).change();
        <?php endif; ?>
    }, 50);
</script>

