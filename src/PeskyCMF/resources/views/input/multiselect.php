<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
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
    Utils.requireFiles('/packages/cmf-vendors/select2/js/select2.full.min.js')
        .done(function () {
            Utils.requireFiles('/packages/cmf-vendors/select2/js/i18n/<?php echo app()->getLocale(); ?>.js')
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
        });
</script>