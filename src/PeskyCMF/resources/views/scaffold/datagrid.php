<?php
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var string $tableNameForRoutes
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\FilterConfig $dataGridFilterConfig
 * @var string $idSuffix
 * @var array $includes - views to include into this template.
 *      Possible use: add datatable cell templates and use them in $dataTablesInitializer
 *      All views receive:
            * var string $idSuffix
            * var \PeskyCMF\Db\CmfDbTable $table
            * var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var string|null $dataTablesInitializer - js function like
        funciton (tableSelector, dataTablesConfig, originalInitializer) {
            return originalInitializer(tableSelector, dataTablesConfig);
        }
 */
$dataGridId = "scaffold-data-grid-{$idSuffix}";
$gridColumnsConfigs = $dataGridConfig->getDataGridColumns();
uasort($gridColumnsConfigs, function ($a, $b) {
    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $a */
    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $b */
    return ($a->getPosition() > $b->getPosition());
});

?>

<?php View::startSection('scaffold-datagrid-table'); ?>
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
                                ->setContent(cmfTransGeneral('.datagrid.actions.select_all'))
                                ->setClass('select-all')
                                ->setHref('javascript: void(0)')
                                ->build(),
                            \Swayok\Html\Tag::a()
                                ->setContent(cmfTransGeneral('.datagrid.actions.select_none'))
                                ->setClass('select-none')
                                ->setHref('javascript: void(0)')
                                ->build(),
                            \Swayok\Html\Tag::a()
                                ->setContent(cmfTransGeneral('.datagrid.actions.invert_selection'))
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
                    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $config */
                    foreach ($gridColumnsConfigs as $config) {
                        $th = \Swayok\Html\Tag::th()
                            ->setContent($config->isVisible() ? $config->getLabel() : '&nbsp;')
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
            $dataForViews = compact('idSuffix', 'table', 'dataGridConfig');
            foreach ($includes as $include) {
                echo view($include, $dataForViews)->render();
                echo "\n\n";
            }
        }
    ?>
<?php View::stopSection(); ?>

<?php View::startSection('scaffold-datagrid-js'); ?>
    <?php
        $pkName = $table->getPkColumnName();
        $dblClickUrl = null;
        // bulk actions
        $bulkActions = [];
        if ($dataGridConfig->isAllowedMultiRowSelection()) {
            if ($dataGridConfig->isDeleteAllowed() && $dataGridConfig->isBulkItemsDeleteAllowed()) {
                $bulkActions[] = \Swayok\Html\Tag::a()
                    ->setContent(cmfTransGeneral('.datagrid.bulk_actions.delete_selected'))
                    ->setDataAttr('confirm', cmfTransGeneral('.datagrid.bulk_actions.delete_selected_confirm'))
                    ->setDataAttr('action', 'bulk-selected')
                    ->setDataAttr('url', cmfRoute('cmf_api_delete_bulk', [$tableNameForRoutes], false))
                    ->setDataAttr('id-field', $pkName)
                    ->setDataAttr('method', 'delete')
                    ->setHref('javascript: void(0)')
                    ->build();
            }
            if ($dataGridConfig->isEditAllowed() && $dataGridConfig->isBulkItemsEditingAllowed()) {
                $bulkActions[] = \Swayok\Html\Tag::a()
                    ->setContent(cmfTransGeneral('.datagrid.bulk_actions.edit_selected'))
                    ->setDataAttr('action', 'bulk-edit-selected')
                    ->setDataAttr('id-field', $pkName)
                    ->setHref('javascript: void(0)')
                    ->build();
            }
        }
        if ($dataGridConfig->isDeleteAllowed() && $dataGridConfig->isFilteredItemsDeleteAllowed()) {
            $bulkActions[] = \Swayok\Html\Tag::a()
                ->setContent(cmfTransGeneral('.datagrid.bulk_actions.delete_filtered'))
                ->setDataAttr('action', 'bulk-filtered')
                ->setDataAttr('confirm', cmfTransGeneral('.datagrid.bulk_actions.delete_filtered_confirm'))
                ->setDataAttr('url', cmfRoute('cmf_api_delete_bulk', [$tableNameForRoutes], false))
                ->setDataAttr('method', 'delete')
                ->setHref('javascript: void(0)')
                ->build();
        }
        if ($dataGridConfig->isEditAllowed() && $dataGridConfig->isFilteredItemsEditingAllowed()) {
            $bulkActions[] = \Swayok\Html\Tag::a()
                ->setContent(cmfTransGeneral('.datagrid.bulk_actions.edit_filtered'))
                ->setDataAttr('action', 'bulk-edit-filtered')
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
                ->setContent(cmfTransGeneral('.datagrid.bulk_actions.dropdown_label'))
                ->append('&nbsp;<span class="caret"></span>')
                ->build();

            $dropdownMenu = \Swayok\Html\Tag::ul()
                ->setClass('dropdown-menu dropdown-menu-right')
                ->setContent('<li>' . implode('</li><li>', $bulkActions) . '</li>')
                ->build();

            $toolbar['bulk_actions'] = \Swayok\Html\Tag::div()
                ->setClass('btn-group bulk-actions float-none')
                ->setContent($dropdownBtn . $dropdownMenu)
                ->build();
        }
        if ($dataGridConfig->isCreateAllowed()) {
            $toolbar['create'] = \Swayok\Html\Tag::a()
                ->setContent(cmfTransGeneral('.datagrid.toolbar.create'))
                ->setClass('btn btn-primary')
                ->setHref(routeToCmfItemAddForm($tableNameForRoutes))
                ->build();
        }
        // row actions
        $actionsTpl = '';
        $actionsCount = 0;
        if ($dataGridConfig->isNestedViewEnabled()) {
            $actionsTpl .= \Swayok\Html\Tag::a()
                ->setClass('row-action link-muted show-children')
                ->setContent('<i class="glyphicon glyphicon-plus-sign"></i>')
                ->setTitle(cmfTransGeneral('.datagrid.actions.show_children'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', '#section-content .content')
                ->setHref('javascript: void(0)')
                ->build();
            $actionsTpl .= \Swayok\Html\Tag::a()
                ->setClass('row-action link-muted hide-children hidden')
                ->setContent('<i class="glyphicon glyphicon-minus-sign"></i>')
                ->setTitle(cmfTransGeneral('.datagrid.actions.hide_children'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', '#section-content .content')
                ->setHref('javascript: void(0)')
                ->build();
            $actionsCount++;
        }
        if ($dataGridConfig->isDetailsViewerAllowed()) {
            $url = $dblClickUrl = ':___details_url:';
            $btn = \Swayok\Html\Tag::a()
                ->setClass('row-action text-light-blue item-details')
                ->setContent('<i class="glyphicon glyphicon-info-sign"></i>')
                ->setTitle(cmfTransGeneral('.datagrid.actions.view_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', '#section-content .content')
                ->setHref(':___details_url:')
                ->build();
            $actionsTpl .= '{{? !!it.___details_allowed }}' . $btn . '{{?}}';
            $actionsCount++;
        }
        if ($dataGridConfig->isEditAllowed()) {
            $url = $dblClickUrl = routeToCmfItemEditForm($tableNameForRoutes, ":{$pkName}:");
            $btn = \Swayok\Html\Tag::a()
                ->setClass('row-action text-green item-edit')
                ->setContent('<i class="glyphicon glyphicon-edit"></i>')
                ->setTitle(cmfTransGeneral('.datagrid.actions.edit_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', '#section-content .content')
                ->setHref($url)
                ->build();
            $actionsTpl .= '{{? !!it.___edit_allowed }}' . $btn . '{{?}}';
            $actionsCount++;
        }
        if ($dataGridConfig->isDeleteAllowed()) {
            $btn = \Swayok\Html\Tag::a()
                ->setContent('<i class="glyphicon glyphicon-trash"></i>')
                ->setClass('row-action text-red item-delete')
                ->setTitle(cmfTransGeneral('.datagrid.actions.delete_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('container', '#section-content .content')
                ->setDataAttr('block-datagrid', '1')
                ->setDataAttr('action', 'request')
                ->setDataAttr('method', 'delete')
                ->setDataAttr('url', cmfRoute('cmf_api_delete_item', [$tableNameForRoutes, ":{$pkName}:"], false))
                ->setDataAttr('confirm', cmfTransGeneral('.action.delete.please_confirm'))
                ->setHref('javascript: void(0)')
                ->build();
            $actionsTpl .= '{{? !!it.___delete_allowed }}' . $btn . '{{?}}';
            $actionsCount++;
        }
        $customRowActions = $dataGridConfig->getRowActions();
        if (!empty($customRowActions)) {
            foreach ($customRowActions as $rowAction) {
                $actionsTpl .= $rowAction;
                $actionsCount++;
            }
        }
        $actionsTpl = '<div class="row-actions text-nowrap">' . preg_replace('%:([a-zA-Z0-9_]+):%is', '{{= it.$1 }}', $actionsTpl) . '</div>'
    ?>

    <script type="application/javascript">
        (function() {
            <?php
                $dataTablesConfig = array_replace(
                    \PeskyCMF\Config\CmfConfig::getPrimary()->data_tables_config(),
                    $dataGridConfig->getAdditionalDataTablesConfig(),
                    [
                        'pkColumnName' => $table::getPkColumnName(),
                        'processing' => true,
                        'serverSide' => true,
                        'ajax' => cmfRoute('cmf_api_get_items', ['table_name' => $tableNameForRoutes], false),
                        'pageLength' => $dataGridConfig->getRecordsPerPage(),
                        'toolbarItems' => array_values($toolbar),
                        'order' => [],
                    ]
                );
                if (!$dataGridConfig->getOrderBy() instanceof \PeskyORM\Core\DbExpr) {
                    $dataTablesConfig['order'] = [
                        [
                            $dataGridConfig->getValueViewer($dataGridConfig->getOrderBy())->getPosition(),
                            $dataGridConfig->getOrderDirection()
                        ]
                    ];
                }
                if ($dataGridConfig->isNestedViewEnabled()) {
                    $dataTablesConfig['nested_data_grid'] = [
                        'value_column' => '__' . $table::getPkColumnName(),
                        'filter_column' => '__' . $dataGridConfig->getColumnNameForNestedView()
                    ];
                }
                if ($dataGridConfig->isRowsReorderingEnabled()) {
                    $dataTablesConfig['rowsReordering'] = [
                        'columns' => $dataGridConfig->getRowsPositioningColumns(),
                        'url' => cmfRouteTpl(
                            'cmf_api_change_item_position',
                            ['table_name' => $tableNameForRoutes],
                            [
                                'id' => 'it.moved_row.__' . $pkName,
                                'next_id' => 'it.next_row.__' . $pkName . ' || 0',
                                'column',
                                'direction'
                            ]
                        )
                    ];
                }
            ?>
            var dataTablesConfig = <?php echo json_encode($dataTablesConfig, JSON_UNESCAPED_UNICODE); ?>;
            dataTablesConfig.resourceName = '<?php echo $tableNameForRoutes; ?>';
            var rowActionsTpl = null;
            Utils.makeTemplateFromText(
                    '<?php echo addslashes($actionsTpl); ?>',
                    'Data grid row actions template'
                )
                .done(function (template) {
                    rowActionsTpl = template;
                })
                .fail(function (error) {
                    throw error;
                });
            dataTablesConfig.columnDefs = [];
            var fixedColumns = 0;
            <?php if ($dataGridConfig->isRowActionsFloating()): ?>
                dataTablesConfig.rowActions = rowActionsTpl;
            <?php else: ?>
                <?php
                    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $actionsValueViewer */
                    $actionsValueViewer = $gridColumnsConfigs[$dataGridConfig::ROW_ACTIONS_COLUMN_NAME];
                ?>
                <?php if ($actionsValueViewer->getPosition() === (int)$dataGridConfig->isAllowedMultiRowSelection()): ?>
                    fixedColumns++;
                <?php endif; ?>
                dataTablesConfig.columnDefs = [
                    {
                        targets: <?php echo $actionsValueViewer->getPosition(); ?>,
                        render: function (data, type, row) {
                            return rowActionsTpl(row);
                        },
                        width: <?php echo max($actionsCount * 27, 80); ?>
                    }
                ];
            <?php endif; ?>
            <?php if ($dataGridConfig->isAllowedMultiRowSelection()) :?>
                fixedColumns++;
                dataTablesConfig.columnDefs.push({
                    targets: 0,
                    orderable: false,
                    className: 'select-checkbox text-center',
                    width: '1%',
                    render: function () {
                        return '';
                    }
                });
                dataTablesConfig.multiselect = true;
                dataTablesConfig.select = {
                    style: 'multi+shift',
                    selector: 'td.select-checkbox',
                    info: false
                };
            <?php endif; ?>
            <?php if ($dataGridConfig->isRowActionsColumnFixed()) : ?>
                if (fixedColumns > 0) {
                    dataTablesConfig.fixedColumns = {
                        leftColumns: fixedColumns
                    };
                }
            <?php endif; ?>
            <?php foreach ($gridColumnsConfigs as $columnConfig) : ?>
                <?php if ($columnConfig->hasCustomWidth()) : ?>
                    dataTablesConfig.columnDefs.push({
                        targets: <?php echo (int)$columnConfig->getPosition(); ?>,
                        width: <?php echo (int)$columnConfig->getWidth(); ?>
                    });
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!empty($dblClickUrl)): ?>
                dataTablesConfig.doubleClickUrl = null;
                Utils.makeTemplateFromText(
                        '<?php echo addslashes(preg_replace('%(:|\%3A)([a-zA-Z0-9_]+)\1%i', '{{= it.$2 }}', $dblClickUrl)); ?>',
                        'Double click URL template'
                    )
                    .done(function (template) {
                        dataTablesConfig.doubleClickUrl = template;
                    })
                    .fail(function (error) {
                        throw error;
                    });
            <?php endif; ?>
            <?php
                $defaultConditions = $dataGridFilterConfig->getDefaultConditions();
                if (empty($defaultConditions['rules'])) {
                    $defaultConditions = '[]';
                } else {
                    $defaultConditions = json_encode($defaultConditions, JSON_UNESCAPED_UNICODE);
                }
            ?>
            dataTablesConfig.defaultSearchRules = <?php echo $defaultConditions; ?>;
            if (dataTablesConfig.defaultSearchRules.rules) {
                dataTablesConfig.search = {search: DataGridSearchHelper.encodeRulesForDataTable(dataTablesConfig.defaultSearchRules)};
            }
            <?php
                $fitlers = [];
                foreach($dataGridFilterConfig->getFilters() as $filterConfig) {
                    $fitlers[] = $filterConfig->buildConfig();
                }
            ?>
            dataTablesConfig.queryBuilderConfig = {
                filters: <?php echo json_encode($fitlers, JSON_UNESCAPED_UNICODE); ?>,
                is_opened: <?php echo $dataGridConfig->isFilterOpenedByDefault() ? 'true' : 'false'; ?>
            };

            DataGridSearchHelper.locale = <?php echo json_encode(cmfTransGeneral('.datagrid.toolbar.filter'), JSON_UNESCAPED_UNICODE); ?>;

            <?php if ($dataGridConfig->hasJsInitiator()): ?>
                <?php echo $dataGridConfig->getJsInitiator(); ?>.call($('#<?php echo $dataGridId; ?>'));
            <?php endif ?>
            <?php if (empty($dataTablesInitializer)): ?>
                var dataGrid = ScaffoldDataGridHelper.init('#<?php echo $dataGridId; ?>', dataTablesConfig);
            <?php else: ?>
                var dataGrid = <?php echo $dataTablesInitializer; ?>('#<?php echo $dataGridId; ?>', dataTablesConfig, ScaffoldDataGridHelper.init);
            <?php endif; ?>
            ScaffoldDataGridHelper.setCurrentDataGrid(dataGrid);
        })();
    </script>
<?php View::stopSection(); ?>

<div id="data-grid-tpl">
    <?php echo view('cmf::ui.default_page_header', [
        'header' => $dataGridConfig->translate(null, 'header'),
        'defaultBackUrl' => \PeskyCMF\Config\CmfConfig::getPrimary()->home_page_url(),
    ])->render(); ?>
    <div class="content">
        <div class="row"><div class="<?php echo $dataGridConfig->getCssClassesForContainer() ?>">
            <div class="box"><div class="box-body scaffold-data-grid-container">
                <?php echo View::yieldContent('scaffold-datagrid-table'); ?>
            </div></div>
        </div></div>

    </div>

    <?php echo View::yieldContent('scaffold-datagrid-js'); ?>

</div>

