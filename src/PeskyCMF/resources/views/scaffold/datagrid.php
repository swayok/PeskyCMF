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
/** @var \PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig[] $gridColumnsConfigs */
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
                <table id="<?php echo $dataGridId ?>" class="table table-bordered table-hover table-striped fluid-width">
                <thead>
                    <tr>
                        <?php
                            if ($dataGridConfig->isAllowedMultiRowSelection()) {
                                $dropdownBtn = \Swayok\Html\Tag::button()
                                    ->setType('button')
                                    ->setClass('rows-selection-options-dropdown-btn')
                                    ->setDataAttr('toggle' , 'dropdown')
                                    ->setAttribute('aria-haspopup', 'true')
                                    ->setAttribute('aria-expanded', 'false')
                                    ->setContent('<span class="glyphicon glyphicon-menu-hamburger"></span>')
                                    ->build();

                                $selectionActions = [
                                    \Swayok\Html\Tag::a()
                                        ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.select_all'))
                                        ->setClass('select-all')
                                        ->setHref('javascript: void(0)')
                                        ->build(),
                                    \Swayok\Html\Tag::a()
                                        ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.select_none'))
                                        ->setClass('select-none')
                                        ->setHref('javascript: void(0)')
                                        ->build(),
                                    \Swayok\Html\Tag::a()
                                        ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.invert_selection'))
                                        ->setClass('invert-selection')
                                        ->setHref('javascript: void(0)')
                                        ->build()
                                ];
                                $dropdownMenu = \Swayok\Html\Tag::ul()
                                    ->setClass('dropdown-menu')
                                    ->setContent('<li>' . implode('</li><li>', $selectionActions) . '</li>')
                                    ->build();

                                echo \Swayok\Html\Tag::th()
                                    ->setContent(
                                        \Swayok\Html\Tag::div()
                                            ->setClass('btn-group rows-selection-options float-none')
                                            ->setContent($dropdownBtn . $dropdownMenu)
                                            ->build()
                                    )
                                    ->setClass('text-nowrap text-center')
                                    ->build();
                            }
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
        $pkName = $model->getPkColumnName();
        $dblClickUrl = null;
        // bulk actions
        $bulkActions = [];
        if ($dataGridConfig->isAllowedMultiRowSelection()) {
            if ($dataGridConfig->isDeleteAllowed() && $dataGridConfig->isBulkItemsDeleteAllowed()) {
                $bulkActions[] = \Swayok\Html\Tag::a()
                    ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.delete_selected'))
                    ->setDataAttr('confirm', \PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.delete_selected_confirm'))
                    ->setDataAttr('action', 'bulk-selected')
                    ->setDataAttr('url', route('cmf_api_delete_bulk', [$tableNameForRoutes], false))
                    ->setDataAttr('id-field', $pkName)
                    ->setDataAttr('method', 'delete')
                    ->setHref('javascript: void(0)')
                    ->build();
            }
            if ($dataGridConfig->isEditAllowed() && $dataGridConfig->isBulkItemsEditingAllowed()) {
                $bulkActions[] = \Swayok\Html\Tag::a()
                    ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.edit_selected'))
                    ->setDataAttr('action', 'bulk-edit-selected')
                    ->setHref('javascript: void(0)')
                    ->build();
            }
        }
        if ($dataGridConfig->isDeleteAllowed() && $dataGridConfig->isFilteredItemsDeleteAllowed()) {
            $bulkActions[] = \Swayok\Html\Tag::a()
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.delete_filtered'))
                ->setDataAttr('action', 'bulk-filtered')
                ->setDataAttr('confirm', \PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.delete_filtered_confirm'))
                ->setDataAttr('url', route('cmf_api_delete_filtered', [$tableNameForRoutes], false))
                ->setDataAttr('method', 'delete')
                ->setHref('javascript: void(0)')
                ->build();
        }
        if ($dataGridConfig->isEditAllowed() && $dataGridConfig->isFilteredItemsEditingAllowed()) {
            $bulkActions[] = \Swayok\Html\Tag::a()
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.edit_filtered'))
                ->setDataAttr('action', 'bulk-edit-filtered')
                ->setDataAttr('url', route('cmf_api_edit_filtered', [$tableNameForRoutes], false))
                ->setDataAttr('method', 'put')
                ->setHref('javascript: void(0)')
                ->build();
        }
        foreach ($dataGridConfig->getBulkActionsToolbarItems() as $toolbarItem) {
            $bulkActions[] = $toolbarItem;
        }
        // main toolbar
        $toolbar = [];
        foreach ($dataGridConfig->getToolbarItems() as $toolbarItem) {
            $toolbar[] = $toolbarItem;
        }
        if (!empty($bulkActions)) {
            $dropdownBtn = \Swayok\Html\Tag::button()
                ->setType('button')
                ->setClass('btn btn-default dropdown-toggle')
                ->setDataAttr('toggle' , 'dropdown')
                ->setAttribute('aria-haspopup', 'true')
                ->setAttribute('aria-expanded', 'false')
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.bulk_actions.dropdown_label'))
                ->append('&nbsp;<span class="caret"></span>')
                ->build();

            $dropdownMenu = \Swayok\Html\Tag::ul()
                ->setClass('dropdown-menu')
                ->setContent('<li>' . implode('</li><li>', $bulkActions) . '</li>')
                ->build();

            $toolbar['bulk_actions'] = \Swayok\Html\Tag::div()
                ->setClass('btn-group bulk-actions float-none')
                ->setContent($dropdownBtn . $dropdownMenu)
                ->build();
        }
        if ($dataGridConfig->isCreateAllowed()) {
            $toolbar['create'] = \Swayok\Html\Tag::a()
                ->setContent(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.toolbar.create'))
                ->setClass('btn btn-primary')
                ->setHref(route('cmf_item_add_form', [$tableNameForRoutes], false))
                ->build();
        }
        // row actions
        $actionsTpl = '';
        if ($dataGridConfig->isDetailsViewerAllowed()) {
            $url = $dblClickUrl = route('cmf_item_details', [$tableNameForRoutes, ":{$pkName}:"], false);
            $btn = \Swayok\Html\Tag::a()
                ->setClass('row-action text-light-blue')
                ->setContent('<i class="glyphicon glyphicon-info-sign"></i>')
                ->setTitle(\PeskyCMF\Config\CmfConfig::transBase('.datagrid.actions.view_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', 'body')
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
                ->setDataAttr('container', 'body')
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
                ->setDataAttr('container', 'body')
                ->setDataAttr('block-datagrid', '1')
                ->setDataAttr('action', 'request')
                ->setDataAttr('method', 'delete')
                ->setDataAttr('url', route('cmf_api_delete_item', [$tableNameForRoutes, ":{$pkName}:"], false))
                ->setDataAttr('confirm', \PeskyCMF\Config\CmfConfig::transBase('.action.delete.please_confirm'))
                ->setHref('javascript: void(0)')
                ->build();
            $actionsTpl .= '{{? !!it.___delete_allowed }}' . $btn . '{{?}}';
        }
        $customRowActions = $dataGridConfig->getRowActions();
        if (!empty($customRowActions)) {
            foreach ($customRowActions as $rowAction) {
                $actionsTpl .= $rowAction;
            }
        }
        $actionsTpl = '<div class="row-actions text-nowrap">' . preg_replace('%:([a-zA-Z0-9_]+):%is', '{{= it.$1 }}', $actionsTpl) . '</div>'
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
                    <?php if ($dataGridConfig->isAllowedMultiRowSelection()) :?>
                    {
                        targets: 0,
                        orderable: false,
                        className: 'select-checkbox text-center',
                        width: '1%',
                        render: function () {
                            return '';
                        }
                    },
                    <?php endif; ?>
                    {
                        targets: <?php echo $actionsFieldConfig->getPosition(); ?>,
                        render: function (data, type, row) {
                            return rowActionsTpl(row);
                        }
                    }
                ];
                dataTablesConfig.multiselect = <?php echo $dataGridConfig->isAllowedMultiRowSelection() ? 'true' : 'false'; ?>;
                <?php if ($dataGridConfig->isAllowedMultiRowSelection()) :?>
                dataTablesConfig.select = {
                    style: 'multi+shift',
                    selector: 'td.select-checkbox',
                    info: false
                };
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($dblClickUrl)): ?>
                dataTablesConfig.doubleClickUrl = Utils.makeTemplateFromText(
                    '<?php echo addslashes(preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%i', '{{= it.$2 }}', $dblClickUrl)); ?>',
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