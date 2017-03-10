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

<?php echo view(
    $dataGridConfig->getTemplate(),
    $data,
    $dataGridConfig->getAdditionalDataForTemplate()
)->render(); ?>


<?php echo view(
    $formConfig->getTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>


<?php echo view(
    $formConfig->getBulkEditingTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>


<?php echo view(
    $itemDetailsConfig->getTemplate(),
    $data,
    $itemDetailsConfig->getAdditionalDataForTemplate()
)->render(); ?>


