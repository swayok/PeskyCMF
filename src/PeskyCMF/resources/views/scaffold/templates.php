<?php
/**
 * @var \App\Db\BaseDbModel $model
 * @var \App\Admin\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \App\Admin\Scaffold\DataGrid\DataGridFilterConfig $dataGridFilterConfig
 * @var \App\Admin\Scaffold\ItemDetails\ItemDetailsConfig $itemDetailsConfig
 * @var \App\Admin\Scaffold\Form\FormConfig $formConfig
 */
$data = [
    'model' => $model,
    'translationPrefix' => "admin_area.{$model->getTableName()}",
    'idSuffix' => preg_replace('%[^a-z0-9]%is', '-', strtolower($model->getTableName()))
];
?>


<?php echo view('cmf::scaffold/datagrid', $data, ['dataGridConfig' => $dataGridConfig, 'dataGridFilterConfig' => $dataGridFilterConfig])->render(); ?>


<?php echo view('cmf::scaffold/form', $data, ['formConfig' => $formConfig])->render(); ?>


<?php echo view('cmf::scaffold/item_details', $data, ['itemDetailsConfig' => $itemDetailsConfig])->render(); ?>


