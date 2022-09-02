<?php
declare(strict_types=1);

use Illuminate\Support\Str;
use PeskyCMF\CmfUrl;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\TableInterface;

/**
 * @var ScaffoldConfig $scaffoldConfig
 * @var TableInterface $table
 * @var string $tableNameForRoutes
 * @var DataGridConfig $dataGridConfig
 * @var FilterConfig $dataGridFilterConfig
 * @var ItemDetailsConfig $itemDetailsConfig
 * @var FormConfig $formConfig
 */

$data = compact([
    'table', 'tableNameForRoutes', 'dataGridConfig', 'dataGridFilterConfig', 'formConfig', 'itemDetailsConfig'
]);
$data['idSuffix'] = Str::slug(strtolower($tableNameForRoutes));
?>

<!-- datagrid start -->

<div id="data-grid-tpl">
    <script type="application/javascript">
        page.show('<?php echo CmfUrl::toItemAddForm($tableNameForRoutes, [], false, $formConfig->getCmfConfig()) ?>');
    </script>
</div>

<!-- datagrid end -->

<!-- itemForm start -->

<?php echo view(
    $formConfig->getTemplate(),
    $data,
    $formConfig->getAdditionalDataForTemplate()
)->render(); ?>

<!-- itemForm end -->

<!-- itemDetails start -->

<?php echo view(
    $itemDetailsConfig->getTemplate(),
    $data,
    $itemDetailsConfig->getAdditionalDataForTemplate()
)->render(); ?>

<!-- itemDetails end -->
