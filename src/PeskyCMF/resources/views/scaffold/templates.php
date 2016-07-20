<?php
/**
 * @var \PeskyCMF\Scaffold\ScaffoldSectionConfig $scaffoldSection
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var string $tableNameForRoutes
 * @var string $localizationKey
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridFilterConfig $dataGridFilterConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 */
if (empty($localizationKey)) {
    $localizationKey = $tableNameForRoutes;
}

$data = compact([
    'model', 'tableNameForRoutes', 'dataGridConfig', 'dataGridFilterConfig', 'formConfig', 'itemDetailsConfig'
]);
$data['translationPrefix'] = $scaffoldSection->getLocalizationBasePath($tableNameForRoutes);
$data['idSuffix'] = str_slug(strtolower($tableNameForRoutes));
?>

<?php echo view(
    $dataGridConfig->getView(),
    $data,
    $dataGridConfig->getAdditionalDataForView()
)->render(); ?>


<?php echo view(
    $formConfig->getView(),
    $data,
    $formConfig->getAdditionalDataForView()
)->render(); ?>


<?php echo view(
    $formConfig->getBulkEditingView(),
    $data,
    $formConfig->getAdditionalDataForView()
)->render(); ?>


<?php echo view(
    $itemDetailsConfig->getView(),
    $data,
    $itemDetailsConfig->getAdditionalDataForView()
)->render(); ?>


