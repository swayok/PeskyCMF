<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridFilterConfig $dataGridFilterConfig
 * @var string $translationPrefix
 * @var string $idSuffix
 * @var array $includes - views to include into this template.
 *      Possible use: add datatable cell templates and use them in $dataTablesInitializer
 *      All views receive:
            * var string $translationPrefix
            * var string $idSuffix
            * var \PeskyCMF\Db\CmfDbModel $model
            * var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var string|null $dataTablesInitializer - js function like
        funciton (containerSelector, dataTablesConfig, originalInitializer) {
            return originalInitializer(dataGridSelector, dataTablesConfig);
        }
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
$gridColumnsConfigs = $dataGridConfig->getFields();
?>

<div id="data-grid-tpl">
    <?php echo view('cmf::ui.default_page_header', [
        'header' => trans("$translationPrefix.datagrid.header"),
        'defaultBackUrl' => route('cmf_start_page'),
    ])->render(); ?>
    <div class="content">
        <div class="row"><div class="<?php echo $dataGridConfig->getCssClassesForContainer() ?>">
            <div class="box"><div class="box-body">
                <table class="table table-bordered table-hover table-striped" id="<?php echo $dataGridId ?>">
                <thead>
                    <tr>
                        <?php
                            foreach ($gridColumnsConfigs as $config) {
                                echo \Swayok\Html\Tag::th()
                                    ->setContent($config->getLabel(trans("$translationPrefix.datagrid.column.{$config->getName()}")))
                                    ->setClass('text-nowrap')
                                    ->setDataAttr('orderable', $config->isSortable() ? 'true' : 'false')
                                    ->setDataAttr('visible', $config->isVisible() ? null : 'false')
                                    ->setDataAttr('name', $config->getName())
                                    ->setDataAttr('data', $config->getName());
                            }
                        ?>
                    </tr>
                </thead>
                </table>
            </div></div>
        </div></div>

    </div>

    <?php
        if (!empty($includes)) {
            if (!is_array($includes)) {
                $includes = [$includes];
            }
            $dataForViews = compact('translationPrefix', 'idSuffix', 'model', 'dataGridConfig');
            foreach ($includes as $include) {
                echo view($include, $dataForViews)->render();
                echo "\n\n";
            }
        }
    ?>

    <?php
        $toolbar = [];
        $pkName = $model->getPkColumnName();
        $dblClickUrl = null;
        if ($dataGridConfig->isCreateAllowed()) {
            $toolbar['create'] = \Swayok\Html\Tag::a()
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.toolbar.create'))
                ->setClass('btn btn-primary')
                ->setHref(route('cmf_item_add_form', [$model->getTableName()], false))
                ->build();
        }

        $actionsTpl = '';
        if ($dataGridConfig->isDetailsViewerAllowed()) {
            $url = $dblClickUrl = route('cmf_item_details', [$model->getTableName(), ":{$pkName}:"], false);
            $btn = \Swayok\Html\Tag::a()
                ->setClass('row-action text-light-blue')
                ->setContent('<i class="glyphicon glyphicon-info-sign"></i>')
                ->setTitle(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.view_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setHref($url)
                ->build();
            $actionsTpl .= '{{? !!it.___details_allowed }}' . $btn . '{{?}}';
        }
        if ($dataGridConfig->isEditAllowed()) {
            $url = $dblClickUrl = route('cmf_item_edit_form', [$model->getTableName(), ":{$pkName}:"], false);
            $btn = \Swayok\Html\Tag::a()
                ->setClass('row-action text-green')
                ->setContent('<i class="glyphicon glyphicon-edit"></i>')
                ->setTitle(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.edit_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setHref($url)
                ->build();
            $actionsTpl .= '{{? !!it.___edit_allowed }}' . $btn . '{{?}}';
        }
        if ($dataGridConfig->isDeleteAllowed()) {
            $btn = \Swayok\Html\Tag::a()
                ->setContent('<i class="glyphicon glyphicon-trash"></i>')
                ->setClass('row-action text-red')
                ->setTitle(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.delete_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('block-datagrid', '1')
                ->setDataAttr('action', 'request')
                ->setDataAttr('method', 'delete')
                ->setDataAttr('url', route('cmf_api_delete_item', [$model->getTableName(), ":{$pkName}:"], false))
                ->setDataAttr('confirm', \PeskyCMF\Config\CmfConfig::transBase('.action.delete.please_confirm'))
                ->setHref('#')
                ->build();
            $actionsTpl .= '{{? !!it.___delete_allowed }}' . $btn . '{{?}}';
        }
        $customRowActions = $dataGridConfig->getRowActions();
        if (!empty($customRowActions)) {
            foreach ($customRowActions as $rowAction) {
                $actionsTpl .= $rowAction;
            }
        }
        $actionsTpl = '<div class="row-actions">' . preg_replace('%:([a-zA-Z0-9_]+):%is', '{{= it.$1 }}', $actionsTpl) . '</div>'
    ?>

    <script type="application/javascript">
        (function() {
            var dataTablesConfig = {
                processing: true,
                serverSide: true,
                scrollX: true,
                scrollY: '55vh',
                scrollCollapse: true,
                ajax: '<?php echo route('cmf_api_get_items', ['model' => $model->getTableName()], false) ?>',
                order: [[
                    '<?php echo $dataGridConfig->getField($dataGridConfig->getOrderBy())->getPosition(); ?>',
                    '<?php echo $dataGridConfig->getOrderDirection(); ?>'
                ]],
                pageLength: <?php echo $dataGridConfig->getLimit() ?>,
                toolbarItems: <?php echo json_encode(array_values($toolbar)); ?>,
                multiselect: false,
                <?php if (!empty($dblClickUrl)): ?>
                    doubleClickUrl: Utils.makeTemplateFromText(
                        '<?php echo addslashes(preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%is', '{{= it.$2 }}', $dblClickUrl)); ?>',
                        'Double click URL template'
                    ),
                <?php endif; ?>
                rowActions: Utils.makeTemplateFromText('<?php echo addslashes($actionsTpl); ?>', 'Data grid row actions template')
            };

            <?php
                $defaultConditions = $dataGridFilterConfig->getDefaultConditions();
                if (empty($defaultConditions['rules'])) {
                    $defaultConditions = 'null';
                } else {
                    $defaultConditions = json_encode($dataGridFilterConfig->getDefaultConditions(), JSON_UNESCAPED_UNICODE);
                }
            ?>

            var defaultSearchRules = <?php echo $defaultConditions; ?>;
            if (!!defaultSearchRules) {
                dataTablesConfig.search = {search: DataGridSearchHelper.encodeRulesForDataTable(defaultSearchRules)};
            }
            <?php
                $fitlers = [];
                foreach($dataGridFilterConfig->getFilters() as $filterConfig) {
                    if (!$filterConfig->hasFilterLabel()) {
                        $path = "$translationPrefix.datagrid.filter." . \Swayok\Utils\StringUtils::underscore($filterConfig->getColumnName());
                        $filterConfig->setFilterLabel(trans($path));
                    }
                    $fitlers[] = $filterConfig->buildConfig();
                }
            ?>
            var queryBuilderConfig = {
                filters: <?php echo json_encode($fitlers, JSON_UNESCAPED_UNICODE); ?>
            };
            DataGridSearchHelper.locale = <?php echo json_encode(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.toolbar.filter'), JSON_UNESCAPED_UNICODE); ?>;
            <?php if (empty($dataTablesInitializer)): ?>
                var dataGrid = ScaffoldDataGridHelper.init('#<?php echo $dataGridId; ?>', dataTablesConfig);
            <?php else: ?>
                var dataGrid = <?php echo $dataTablesInitializer; ?>('#<?php echo $dataGridId; ?>', dataTablesConfig, ScaffoldDataGridHelper.init);
            <?php endif; ?>
            DataGridSearchHelper.init(queryBuilderConfig, defaultSearchRules, dataGrid);
        })();
    </script>

</div>