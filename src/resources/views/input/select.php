<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\Form\FormInput $valueViewer
 * @var \PeskyCMF\Scaffold\Form\FormConfig $sectionConfig
 * @var \PeskyORM\ORM\TableInterface $table
 */
$isMultiple = $rendererConfig->getAttribute('multiple', false);
$rendererConfig
    ->addAttribute('name', $valueViewer->getName(true) . ($isMultiple ? '[]' : ''), false)
    ->addAttribute('id', $valueViewer->getDefaultId(), false)
    ->addAttribute('class', 'form-control selectpicker', false);

$attributesForCreate = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForCreate());
$attributesForEdit = \Swayok\Html\Tag::buildAttributes($rendererConfig->getAttributesForEdit());
$isHidden = (bool)$rendererConfig->getData('isHidden', false);
if ($valueViewer->isOptionsFilteringEnabled()) {
    $routeName = $sectionConfig->getScaffoldConfig()->getCmfConfig()->getRouteName('cmf_api_get_options_as_json');
    $routeData = [
        'resource' => $sectionConfig->getScaffoldConfig()->getResourceName(),
        'input_name' => $valueViewer->getName(),
    ];
    $additionalOptions = implode(' ', [
        'data-abs-ajax-url="' . route($routeName, $routeData) . '?id={{= it.id || \'\' }}"',
        'data-abs-ajax-type="GET"',
        'data-abs-min-length="' . $valueViewer->getMinCharsRequiredToInitOptionsFiltering() . '"'
    ]);
    $attributesForCreate .= ' ' . $additionalOptions;
    $attributesForEdit .= ' ' . $additionalOptions;
}
?>

<div class="form-group <?php echo $isHidden ? 'hidden' : ''; ?>">
    <?php if (!$isHidden) : ?>
        <label for="<?php echo $rendererConfig->getAttribute('id'); ?>"><?php echo $valueViewer->getLabel($rendererConfig); ?></label>
    <?php endif; ?>
    <select {{? !!it.isCreation }}<?php echo $attributesForCreate ?>{{??}}<?php echo $attributesForEdit ?>{{?}}
        <?php if ($isMultiple): ?>
            data-value="<?php echo $valueViewer->getDotJsInsertForValue([], 'array_encode'); ?>"
        <?php else: ?>
            data-value="<?php echo $valueViewer->getDotJsInsertForValue(); ?>"
        <?php endif; ?>
    >
    <?php if ($valueViewer->hasOptionsLoader()) : ?>
        {{? typeof it._options !== undefined && $.isPlainObject(it._options) && typeof it._options['<?php echo $valueViewer->getName(); ?>'] !== undefined }}
            {{= it._options['<?php echo $valueViewer->getName(); ?>'] }}
        {{?}}
    <?php else: ?>
        <?php
            $emptyOption = '<option value="">'. $valueViewer->getEmptyOptionLabel() . '</option>';
            $isEmptyoptionAllowed = !$isMultiple;
        ?>
        <?php if ($rendererConfig->areOptionsDifferent()) : ?>
            {{? !!it.isCreation }}
                <?php
                    $options = $rendererConfig->getOptionsForCreate();
                    if ($isEmptyoptionAllowed && !array_key_exists('', $options) && !$rendererConfig->isRequiredForCreate()) {
                        echo $emptyOption;
                    }
                ?>
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            {{??}}
                <?php
                    $options = $rendererConfig->getOptionsForEdit();
                    if ($isEmptyoptionAllowed && !array_key_exists('', $options) && !$rendererConfig->isRequiredForEdit()) {
                        echo $emptyOption;
                    }
                ?>
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            {{?}}
        <?php else : ?>
            <?php
                $options = $rendererConfig->getOptions();
                if ($isEmptyoptionAllowed && !array_key_exists('', $options) && !$rendererConfig->isRequired()) {
                    echo $emptyOption;
                }
            ?>
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo str_replace('"', '\"', $value) ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
    </select>
    <?php if ($isMultiple || ($rendererConfig->isRequired() && $valueViewer->hasOptionsLoader())) : ?>
        <script type="application/javascript">
            $('#<?php echo $rendererConfig->getAttribute('id') ?>').find('option[value=""]').remove();
        </script>
    <?php endif; ?>
    <?php echo $isHidden ? '' : $valueViewer->getFormattedTooltip(); ?>
</div>