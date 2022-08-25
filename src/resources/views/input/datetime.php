<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var array $config
 */
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true), false)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('type','text', false)
    ->addAttribute('class', 'form-control', false);
$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
?>

<div class="form-group">
    <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <div class="input-group w200">
        <input value="<?php echo $valueViewer->getDotJsInsertForValue(); ?>"
            {{? !!it.isCreation }}<?php echo $attributesForCreate; ?>{{??}}<?php echo $attributesForEdit; ?>{{?}}>
        <div class="input-group-addon cursor">
            <i class="fa fa-calendar"></i>
        </div>
    </div>
    <?php echo $valueViewer->getFormattedTooltip(); ?>
</div>

<?php
if (empty($config)) {
    $config = [];
}
$config = array_merge([
    'useCurrent' => $rendererConfig->isRequired(),
    'format' => 'YYYY-MM-DD HH:mm:ss',
    'locale' => $sectionConfig->getCmfConfig()->getLaravelApp()->getLocale(),
    'sideBySide' => true
], $config);
?>

<script type="application/javascript">
    setTimeout(function () {
        var $input = $('#<?php echo $rendererConfig->getAttribute('id'); ?>');
        var config = <?php echo json_encode($config); ?>;
        if (config.useCurrent) {
            config.defaultDate = moment();
        }
        if (!config.minDate) {
            delete config.minDate;
        } else if (config.minDate === 'now') {
            config.minDate = moment().startOf('minute');
        }
        $input.datetimepicker(config);
        $input.parent().find('.input-group-addon.cursor').on('click', function () {
            $input.data("DateTimePicker").show();
        });
    }, 50);
</script>
