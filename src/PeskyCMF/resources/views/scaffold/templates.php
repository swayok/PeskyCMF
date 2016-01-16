<?php
/**
 * @var \App\Db\BaseDbModel $model
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridFilterConfig $dataGridFilterConfig
 * @var \PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var \PeskyCMF\Scaffold\Form\FormConfig $formConfig
 */
$data = [
    'model' => $model,
    'translationPrefix' => \PeskyCMF\Config\CmfConfig::getInstance()->custom_dictionary_name() . ".{$model->getTableName()}",
    'idSuffix' => preg_replace('%[^a-z0-9]%is', '-', strtolower($model->getTableName()))
];
?>


<?php echo view('cmf::scaffold/datagrid', $data, ['dataGridConfig' => $dataGridConfig, 'dataGridFilterConfig' => $dataGridFilterConfig])->render(); ?>


<?php echo view('cmf::scaffold/form', $data, ['formConfig' => $formConfig])->render(); ?>


<?php echo view('cmf::scaffold/item_details', $data, ['itemDetailsConfig' => $itemDetailsConfig])->render(); ?>


