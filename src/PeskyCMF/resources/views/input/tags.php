<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfDbTable $model
 */
$rendererConfig
    ->addAttribute('name', $fieldConfig->getName() . '[]', false)
    ->addAttribute('id', $fieldConfig->getDefaultId(), false)
    ->addAttribute('class', 'form-control select2', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $fieldConfig->getLabel('', $rendererConfig); ?></label>
    <div>
        <select multiple {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
            data-value="<?php echo $fieldConfig->getDotJsJsonInsertForValue(true); ?>"
        >
        </select>
    </div>
    <?php echo $fieldConfig->getFormattedTooltip(); ?>
</div>

<script type="application/javascript">
    Utils.requireFiles('/packages/cmf-vendors/select2/js/select2.full.min.js')
        .done(function () {
            Utils.requireFiles('/packages/cmf-vendors/select2/js/i18n/<?php echo app()->getLocale(); ?>.js')
                .done(function () {
                    var $select = $('#<?php echo $rendererConfig->getAttribute('id'); ?>');
                    var tags = $select.attr('data-value');
                    try {
                        tags = JSON.parse(tags);
                    } catch (exc) {
                        console.log(exc);
                    }
                    if (!tags || !$.isArray(tags)) {
                        console.log('Invalid json for tags input (array expected): ' + tags);
                        tags = [];
                    }
                    for (var i = 0; i < tags.length; i++) {
                        $select.append('<option value="' + tags[i] + '" selected>' + tags[i] + '</option>');
                    }
                    $select.select2({
                        tags: true,
                        width: '100%',
                        selectOnClose: true
                    });
                });
        });
</script>