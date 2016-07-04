<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbModel $model
 */

$rendererConfig
    ->addAttribute('multiple', true, false)
    ->addAttribute('class', 'select2 form-control', false);

/**
 * @var array $attributes
 */
include __DIR__ . '/select.php';
?>

<script type="application/javascript">
    Utils.requireFiles('/packages/adminlte/plugins/select2/select2.full.min.js')
        .done(function () {
            Utils.requireFiles('/packages/adminlte/plugins/select2/i18n/<?php echo app()->getLocale(); ?>.js')
                .done(function () {
                    var $select = $('#<?php echo $rendererConfig->getAttribute('id'); ?>');
                    var values = $select.attr('data-value');
                    try {
                        values = JSON.parse(values);
                    } catch (exc) {
                        console.log(exc);
                    }
                    if (!values || !$.isArray(values)) {
                        console.log('Invalid json for values input (array expected): ' + values);
                        values = [];
                    }
                    $select.val(values).select2({
                        width: '100%',
                        closeOnSelect: false
                    });
                });
        })
</script>
