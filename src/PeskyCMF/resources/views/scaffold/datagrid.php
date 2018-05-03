<?php
/**
 * @var \PeskyORM\ORM\TableInterface $table
 * @var string $tableNameForRoutes
 * @var \PeskyCMF\Scaffold\DataGrid\DataGridConfig $dataGridConfig
 * @var \PeskyCMF\Scaffold\DataGrid\FilterConfig $dataGridFilterConfig
 */
$helper = $dataGridConfig->getRendererHelper();
$dataGridId = $helper->getId();
$gridColumnsConfigs = $helper->getSortedColumnConfigs();

?>

<?php View::startSection('scaffold-datagrid-table'); ?>
    <table id="<?php echo $dataGridId ?>" class="table table-bordered table-hover table-striped fluid-width">
        <thead>
            <tr>
                <?php echo $helper->getHtmlTableColumnsHeaders(); ?>
            </tr>
        </thead>
    </table>

    <?php echo $helper->getAdditionalViews(); ?>

<?php View::stopSection(); ?>

<?php View::startSection('scaffold-datagrid-js'); ?>
    <script type="application/javascript">
        (function() {
            var dataTablesConfig = <?php echo json_encode($helper->getDataTablesConfig(), JSON_UNESCAPED_UNICODE); ?>;
            dataTablesConfig.columnDefs = [];
            var fixedColumns = 0;

            // multiselect column
            if (dataTablesConfig.multiselect) {
                <?php if ($dataGridConfig->isMultiRowSelectionColumnFixed()) : ?>
                    fixedColumns++;
                <?php endif; ?>
                dataTablesConfig.columnDefs.push({
                    targets: 0,
                    orderable: false,
                    className: 'select-checkbox text-center',
                    width: '1%',
                    render: function () {
                        return '';
                    }
                });
                dataTablesConfig.select = {
                    style: 'multi+shift',
                    selector: 'td.select-checkbox',
                    info: false
                };
            }

            // nested view trigger column
            <?php if ($dataGridConfig->isNestedViewEnabled()) : ?>
                var nestedViewsTriggerTpl = null;
                Utils.makeTemplateFromText(
                        '<?php echo addslashes($helper->getNestedViewTriggerCellTemplate()); ?>',
                        'Data grid row actions template'
                    )
                    .done(function (template) {
                        nestedViewsTriggerTpl = template;
                    })
                    .fail(function (error) {
                        throw error;
                    });
                if (fixedColumns > 0) {
                    fixedColumns++;
                }
                dataTablesConfig.columnDefs.push({
                    targets: dataTablesConfig.multiselect ? 1 : 0,
                    orderable: false,
                    className: 'text-center posr ph5',
                    width: '1%',
                    render: function (data, type, row) {
                        return nestedViewsTriggerTpl(row);
                    }
                });
            <?php endif; ?>

            // row actions
            <?php if ($dataGridConfig->isRowActionsEnabled()) : ?>
                var rowActionsTpl = null;
                Utils.makeTemplateFromText(
                        '<?php echo addslashes($helper->getRowActionsDotJsTemplate()); ?>',
                        'Data grid row actions template'
                    )
                    .done(function (template) {
                        rowActionsTpl = template;
                    })
                    .fail(function (error) {
                        throw error;
                    });

                <?php
                    /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $actionsValueViewer */
                    $actionsValueViewer = $gridColumnsConfigs[$dataGridConfig::ROW_ACTIONS_COLUMN_NAME];
                ?>
                <?php if ($actionsValueViewer->getPosition() === (int)$dataGridConfig->isAllowedMultiRowSelection()): ?>
                    fixedColumns++;
                <?php endif; ?>

                dataTablesConfig.columnDefs.push({
                    targets: <?php echo $actionsValueViewer->getPosition(); ?>,
                    render: function (data, type, row) {
                        return rowActionsTpl(row);
                    },
                    width: <?php echo max($helper->getRowActionsCount() * 27, 80); ?>
                });
            <?php endif; ?>

            if (fixedColumns > 0) {
                dataTablesConfig.fixedColumns = {
                    leftColumns: fixedColumns
                };
            }

            // other columns
            <?php foreach ($gridColumnsConfigs as $columnConfig) : ?>
                <?php if ($columnConfig->hasCustomWidth()) : ?>
                    dataTablesConfig.columnDefs.push({
                        targets: <?php echo (int)$columnConfig->getPosition(); ?>,
                        width: <?php echo (int)$columnConfig->getWidth(); ?>
                    });
                <?php endif; ?>
            <?php endforeach; ?>

            // double click action on row
            <?php $dblClickUrl = $helper->getDoubleClickUrl(); ?>
            <?php if (!empty($dblClickUrl)): ?>
                dataTablesConfig.doubleClickUrl = null;
                Utils.makeTemplateFromText(
                        '<?php echo addslashes($dblClickUrl); ?>',
                        'Double click URL template'
                    )
                    .done(function (template) {
                        dataTablesConfig.doubleClickUrl = template;
                    })
                    .fail(function (error) {
                        throw error;
                    });
            <?php endif; ?>

            // default data grid filter conditions
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

            // data grid filters
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

            // data grid filters localization strings
            DataGridSearchHelper.locale = <?php echo json_encode($dataGridConfig->translateGeneral('toolbar.filter'), JSON_UNESCAPED_UNICODE); ?>;

            // init data grid using collected conditions
            var $dataGrid = $('#<?php echo $dataGridId; ?>');
            <?php if ($dataGridConfig->hasJsInitiator()): ?>
                <?php echo $dataGridConfig->getJsInitiator(); ?>($dataGrid, dataTablesConfig);
            <?php else: ?>
                ScaffoldDataGridHelper.init($dataGrid, dataTablesConfig);
            <?php endif; ?>
            ScaffoldDataGridHelper.setCurrentDataGrid($dataGrid);

        })();
    </script>
<?php View::stopSection(); ?>

<div id="data-grid-tpl">
    <?php echo view('cmf::ui.default_page_header', [
        'header' => $dataGridConfig->translate(null, 'header'),
        'defaultBackUrl' => cmfConfig()->home_page_url(),
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

