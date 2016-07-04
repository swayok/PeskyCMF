<?php
/**
 * @var \PeskyCMF\Db\CmfDbModel $model
 * @var string $tableNameForRoutes
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
        funciton (tableSelector, dataTablesConfig, originalInitializer) {
            return originalInitializer(tableSelector, dataTablesConfig);
        }
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
/** @var \PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig $gridColumnsConfigs */
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
                            $invisibleColumns = [];
                            /** @var \PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig $config */
                            foreach ($gridColumnsConfigs as $config) {
                                $th = \Swayok\Html\Tag::th()
                                    ->setContent($config->isVisible()
                                        ? $config->getLabel(trans("$translationPrefix.datagrid.column.{$config->getName()}"))
                                        : '&nbsp'
                                    )
                                    ->setClass('text-nowrap')
                                    ->setDataAttr('visible', $config->isVisible() ? null : 'false')
                                    ->setDataAttr('orderable', $config->isVisible() && $config->isSortable() ? 'true' : 'false')
                                    ->setDataAttr('name', $config->getName())
                                    ->setDataAttr('data', $config->getName());
                                if ($config->isVisible()) {
                                    echo $th->build();
                                } else {
                                    $invisibleColumns[] = $th->build();
                                }
                            }
                            echo implode("\n", $invisibleColumns);
                        ?>
                    </tr>
                </thead>
                </table>

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
            </div></div>
        </div></div>

    </div>

    <?php
        $toolbar = [];
        $pkName = $model->getPkColumnName();
        $dblClickUrl = null;
        if ($dataGridConfig->isCreateAllowed()) {
            $toolbar['create'] = \Swayok\Html\Tag::a()
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.toolbar.create'))
                ->setClass('btn btn-primary')
                ->setHref(route('cmf_item_add_form', [$tableNameForRoutes], false))
                ->build();
        }

        $actionsTpl = '';
        if ($dataGridConfig->isDetailsViewerAllowed()) {
            $url = $dblClickUrl = route('cmf_item_details', [$tableNameForRoutes, ":{$pkName}:"], false);
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
            $url = $dblClickUrl = route('cmf_item_edit_form', [$tableNameForRoutes, ":{$pkName}:"], false);
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
                ->setDataAttr('url', route('cmf_api_delete_item', [$tableNameForRoutes, ":{$pkName}:"], false))
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
            <?php
                $dataTablesConfig = array_replace(
                    \PeskyCMF\Config\CmfConfig::getInstance()->data_tables_config(),
                    $dataGridConfig->getAdditionalDataTablesConfig(),
                    [
                        'processing' => true,
                        'serverSide' => true,
                        'ajax' => route('cmf_api_get_items', ['model' => $tableNameForRoutes], false),
                        'order' => [
                            [
                                $dataGridConfig->getField($dataGridConfig->getOrderBy())->getPosition(),
                                $dataGridConfig->getOrderDirection()
                            ]
                        ],
                        'pageLength' => $dataGridConfig->getLimit(),
                        'toolbarItems' => array_values($toolbar),
                    ]
                );
            ?>
            var dataTablesConfig = <?php echo json_encode($dataTablesConfig, JSON_UNESCAPED_UNICODE); ?>;
            var rowActionsTpl = Utils.makeTemplateFromText('<?php echo addslashes($actionsTpl); ?>', 'Data grid row actions template');
            <?php if ($dataGridConfig->isRowActionsFloating()): ?>
            dataTablesConfig.rowActions = rowActionsTpl;
            <?php else: ?>
                <?php
                    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig $actionsFieldConfig */
                    $actionsFieldConfig = $gridColumnsConfigs[$dataGridConfig::ROW_ACTIONS_COLUMN_NAME];
                ?>
                dataTablesConfig.columnDefs = [
                    {
                        targets: <?php echo $actionsFieldConfig->getPosition(); ?>,
                        render: function (data, type, row) {
                            return rowActionsTpl(row);
                        }
                    }
                ];
            <?php endif; ?>
            <?php if (!empty($dblClickUrl)): ?>
                dataTablesConfig.doubleClickUrl = Utils.makeTemplateFromText(
                    '<?php echo addslashes(preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%is', '{{= it.$2 }}', $dblClickUrl)); ?>',
                    'Double click URL template'
                );
            <?php endif; ?>

            <?php
                $defaultConditions = $dataGridFilterConfig->getDefaultConditions();
                if (empty($defaultConditions['rules'])) {
                    $defaultConditions = '[]';
                } else {
                    $defaultConditions = json_encode($defaultConditions, JSON_UNESCAPED_UNICODE);
                }
            ?>
            var defaultSearchRules = <?php echo $defaultConditions; ?>;
            if (defaultSearchRules.rules) {
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
                filters: <?php echo json_encode($fitlers, JSON_UNESCAPED_UNICODE); ?>,
                is_opened: <?php echo $dataGridConfig->isFilterShownByDefault() ? 'true' : 'false'; ?>
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