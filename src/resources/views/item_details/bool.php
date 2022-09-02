<?php
declare(strict_types=1);
/**
 * @var \PeskyCMF\Scaffold\Form\InputRenderer $rendererConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $sectionConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ValueCell $valueViewer
 * @var array|null $options
 * @var \PeskyORM\ORM\TableInterface $table
 */
if (empty($options)) {
    $options = [];
}
$yes = \Illuminate\Support\Arr::get($options, 'yes', function () use ($sectionConfig) {
    return $sectionConfig->getCmfConfig()->transGeneral('.item_details.field.bool.yes');
});
$no = \Illuminate\Support\Arr::get($options, 'no', function () use ($sectionConfig) {
    return $sectionConfig->getCmfConfig()->transGeneral('.item_details.field.bool.no');
});
echo $valueViewer->getConditionalDotJsInsertForValue($yes, $no);

