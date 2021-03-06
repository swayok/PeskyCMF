<?php
/**
 * @var \PeskyCMF\Scaffold\ScaffoldConfig $scaffoldConfig
 * @var \PeskyORM\ORM\TableInterface $table
 * @var string $tableNameForRoutes
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\FilterConfig $dataGridFilterConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 */
$data = compact([
    'table', 'tableNameForRoutes', 'dataGridConfig', 'dataGridFilterConfig', 'formConfig', 'itemDetailsConfig'
]);
$data['idSuffix'] = str_slug(strtolower($tableNameForRoutes));
?>

<!-- datagrid start -->

<?php echo view(
    $dataGridConfig->getTemplate(),
    $data,
    $dataGridConfig->getAdditionalDataForTemplate()
)->render(); ?>

<!-- datagrid end -->

<!-- itemForm start -->

<?php echo view(
    $formConfig->getTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>

<!-- itemForm end -->

<?php if ($dataGridConfig->isBulkItemsEditingAllowed()): ?>

<!-- bulkEditForm start -->

<?php echo view(
    $formConfig->getBulkEditingTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>

<?php endif; ?>

<!-- bulkEditForm end -->

<!-- itemDetails start -->

<?php echo view(
    $itemDetailsConfig->getTemplate(),
    $data,
    $itemDetailsConfig->getAdditionalDataForTemplate()
)->render(); ?>

<!-- itemDetails end -->
