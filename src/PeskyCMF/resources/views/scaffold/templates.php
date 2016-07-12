<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var string $tableNameForRoutes
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridFilterConfig $dataGridFilterConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 */
$data = [
    'model' => $model,
    'tableNameForRoutes' => $tableNameForRoutes,
    'translationPrefix' => \PeskyCMF\Config\CmfConfig::getInstance()->custom_dictionary_name() . ".{$tableNameForRoutes}",
    'idSuffix' => preg_replace('%[^a-z0-9]%i', '-', strtolower($tableNameForRoutes))
];
?>


<?php echo view(
    $dataGridConfig->getView(),
    $data + $dataGridConfig->getAdditionalDataForView(),
    [
        'dataGridConfig' => $dataGridConfig,
        'dataGridFilterConfig' => $dataGridFilterConfig
    ]
)->render(); ?>


<?php echo view(
    $formConfig->getView(),
    $data + $formConfig->getAdditionalDataForView(),
    ['formConfig' => $formConfig]
)->render(); ?>


<?php echo view(
    $formConfig->getBulkEditingView(),
    $data + $formConfig->getAdditionalDataForView(),
    ['formConfig' => $formConfig]
)->render(); ?>


<?php echo view(
    $itemDetailsConfig->getView(),
    $data + $itemDetailsConfig->getAdditionalDataForView(),
    ['itemDetailsConfig' => $itemDetailsConfig]
)->render(); ?>


