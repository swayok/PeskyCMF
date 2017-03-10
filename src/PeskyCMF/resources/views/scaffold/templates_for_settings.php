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

<div id="data-grid-tpl">
    <script type="application/javascript">
        window.adminApp.nav('<?php echo routeToCmfItemAddForm($tableNameForRoutes) ?>');
    </script>
</div>

<?php echo view(
    $formConfig->getTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>

<?php echo view(
    $itemDetailsConfig->getTemplate(),
    $data,
    $itemDetailsConfig->getAdditionalDataForTemplate()
)->render(); ?>


