<?php
/**
 * @var \PeskyCMF\Scaffold\Form\InputRendererConfig $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormFieldConfig $fieldConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $actionConfig
 * @var \PeskyCMF\Db\CmfTable $model
 */

$attributes = array(
    'name' => $fieldConfig->getName() . '[]',
    'id' => $fieldConfig->getDefaultId(),
    'class' => 'form-control select2',
);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForCreate()));
$attributesForEdit = \Swayok\Html\Tag::buildAttributes(array_merge($attributes, $rendererConfig->getAttributesForEdit()));
?>
<div class="form-group">
    <label for="<?php echo $attributes['id']; ?>"><?php echo $fieldConfig->getLabel(); ?></label>
    <div>
        <select multiple {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
            data-value="{{! it.<?php echo $fieldConfig->getName(); ?> && $.isArray(it.<?php echo $fieldConfig->getName(); ?>) ? JSON.stringify(it.<?php echo $fieldConfig->getName(); ?>) : (it.<?php echo $fieldConfig->getName(); ?> || '[]') }}"
        >
        </select>
    </div>
</div>

<script type="application/javascript">
    Utils.requireFiles('/packages/cmf-vendors/select2/js/select2.full.min.js')
        .done(function () {
            Utils.requireFiles('/packages/cmf-vendors/select2/js/i18n/<?php echo app()->getLocale(); ?>.js')
                .done(function () {
                    var $select = $('#<?php echo $attributes['id']; ?>');
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