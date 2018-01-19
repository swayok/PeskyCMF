<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class DataGridRendererHelper {

    /** @var DataGridConfig  */
    protected $dataGridConfig;
    /** @var string */
    protected $tableNameForRoutes;
    /** @var TableInterface  */
    protected $table;
    /** @var FilterConfig */
    protected $dataGridFilterConfig;
    /** @var DataGridColumn */
    protected $sortedColumnConfigs;
    /** @var string */
    protected $idSuffix;
    /** @var string */
    protected $id;
    /** @var int */
    protected $rowActionsCount = 0;

    /**
     * DataGridRendererHelper constructor.
     * @param DataGridConfig $dataGridConfig
     * @param FilterConfig $dataGridFilterConfig
     * @param TableInterface $table
     * @param string $tableNameForRoutes
     */
    public function __construct(
        DataGridConfig $dataGridConfig,
        FilterConfig $dataGridFilterConfig,
        TableInterface $table,
        $tableNameForRoutes
    ) {
        $this->dataGridConfig = $dataGridConfig;
        $this->dataGridFilterConfig = $dataGridFilterConfig;
        $this->table = $table;
        $this->tableNameForRoutes = $tableNameForRoutes;
    }

    /**
     * @return string
     */
    public function getIdSuffix() {
        if (!$this->idSuffix) {
            $this->idSuffix = str_slug(strtolower($this->tableNameForRoutes));
        }
        return $this->idSuffix;
    }

    /**
     * @return string
     */
    public function getId() {
        if (!$this->id) {
            $this->id = 'scaffold-data-grid-' . $this->getIdSuffix();
        }
        return $this->id;
    }

    /**
     * @return string;
     * @throws \Swayok\Html\HtmlTagException
     */
    public function getHtmlTableMultiselectColumnHeader() {
        $dropdownBtn = Tag::button()
            ->setType('button')
            ->setClass('rows-selection-options-dropdown-btn')
            ->setDataAttr('toggle' , 'dropdown')
            ->setAttribute('aria-haspopup', 'true')
            ->setAttribute('aria-expanded', 'false')
            ->setContent('<span class="glyphicon glyphicon-menu-hamburger fs15"></span>')
            ->build();

        $selectionActions = [
            Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('actions.select_all'))
                ->setClass('select-all')
                ->setHref('javascript: void(0)')
                ->build(),
            Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('actions.select_none'))
                ->setClass('select-none')
                ->setHref('javascript: void(0)')
                ->build(),
            Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('actions.invert_selection'))
                ->setClass('invert-selection')
                ->setHref('javascript: void(0)')
                ->build()
        ];
        $dropdownMenu = Tag::ul()
            ->setClass('dropdown-menu')
            ->setContent('<li>' . implode('</li><li>', $selectionActions) . '</li>')
            ->build();

        return Tag::th()
            ->setContent(
                Tag::div()
                    ->setClass('btn-group rows-selection-options float-none')
                    ->setContent($dropdownBtn . $dropdownMenu)
                    ->build()
            )
            ->setClass('text-nowrap text-center')
            ->build();
    }

    /**
     * @return string
     */
    public function getHtmlTableNestedViewsColumnHeader() {
        return Tag::th()
            ->setContent('&nbsp;')
            ->setDataAttr('visible', 'true')
            ->setDataAttr('orderable', 'false')
            ->setDataAttr('name', $this->table->getPkColumnName())
            ->setDataAttr('data', $this->table->getPkColumnName())
            ->build();
    }

    /**
     * @return \PeskyCMF\Scaffold\AbstractValueViewer[]|DataGridColumn|DataGridColumn[]
     */
    public function getSortedColumnConfigs() {
        if (!$this->sortedColumnConfigs) {
            $this->sortedColumnConfigs = $this->dataGridConfig->getDataGridColumns();
            uasort($this->sortedColumnConfigs, function ($a, $b) {
                /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $a */
                /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $b */
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                return ($a->getPosition() > $b->getPosition());
            });
        }
        return $this->sortedColumnConfigs;
    }

    /**
     * @return string;
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \Swayok\Html\HtmlTagException
     */
    public function getHtmlTableColumnsHeaders() {
        $invisibleColumns = '';
        $visibleColumns = '';
        if ($this->dataGridConfig->isAllowedMultiRowSelection()) {
            $visibleColumns .= $this->getHtmlTableMultiselectColumnHeader() . "\n";
        }
        if ($this->dataGridConfig->isNestedViewEnabled()) {
            $visibleColumns .= $this->getHtmlTableNestedViewsColumnHeader() . "\n";
        }
        $columns = $this->getSortedColumnConfigs();
        /** @var \PeskyCMF\Scaffold\DataGrid\DataGridColumn $config */
        foreach ($columns as $config) {
            $th = Tag::th()
                ->setContent($config->isVisible() ? $config->getLabel() : '&nbsp;')
                ->setClass('text-nowrap')
                ->setDataAttr('visible', $config->isVisible() ? null : 'false')
                ->setDataAttr('orderable', $config->isVisible() && $config->isSortable() ? 'true' : 'false')
                ->setDataAttr('name', $config->getName())
                ->setDataAttr('data', $config->getName())
                ->build();
            if ($config->isVisible()) {
                $visibleColumns .= $th . "\n";
            } else {
                $invisibleColumns .= $th . "\n";
            }
        }
        return $visibleColumns . $invisibleColumns;
    }

    /**
     * @return array
     * @throws \LogicException
     */
    public function getBulkActions() {
        $bulkActions = [];
        $placeFirst = [];
        if ($this->dataGridConfig->isAllowedMultiRowSelection()) {
            $pkName = $this->table->getPkColumnName();
            foreach ($this->dataGridConfig->getBulkActionsToolbarItems() as $key => $bulkAction) {
                if ($bulkAction instanceof Tag) {
                    $bulkAction = $bulkAction->build();
                }
                if (is_string($key)) {
                    $bulkActions[$key] = $bulkAction;
                } else {
                    $bulkActions[] = $bulkAction;
                }
            }
            if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isBulkItemsDeleteAllowed()) {
                $action = Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.delete_selected'))
                    ->setDataAttr('confirm', $this->dataGridConfig->translateGeneral('bulk_actions.message.delete_bulk.delete_selected_confirm'))
                    ->setDataAttr('action', 'bulk-selected')
                    ->setDataAttr('url', cmfRoute('cmf_api_delete_bulk', [$this->tableNameForRoutes], false))
                    ->setDataAttr('id-field', $pkName)
                    ->setDataAttr('method', 'delete')
                    ->setHref('javascript: void(0)')
                    ->build();
                if (array_key_exists('delete_selected', $bulkActions)) {
                    $bulkActions['delete_selected'] = $action;
                } else {
                    $placeFirst[] = $action;
                }
            }
            if ($this->dataGridConfig->isEditAllowed() && $this->dataGridConfig->isBulkItemsEditingAllowed()) {
                $action = Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.edit_selected'))
                    ->setDataAttr('action', 'bulk-edit-selected')
                    ->setDataAttr('id-field', $pkName)
                    ->setHref('javascript: void(0)')
                    ->build();
                if (array_key_exists('edit_selected', $bulkActions)) {
                    $bulkActions['edit_selected'] = $action;
                } else {
                    $placeFirst = $action;
                }
            }
        }
        if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isFilteredItemsDeleteAllowed()) {
            $action = Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.delete_filtered'))
                ->setDataAttr('action', 'bulk-filtered')
                ->setDataAttr('confirm', $this->dataGridConfig->translateGeneral('bulk_actions.message.delete_bulk.delete_filtered_confirm'))
                ->setDataAttr('url', cmfRoute('cmf_api_delete_bulk', [$this->tableNameForRoutes], false))
                ->setDataAttr('method', 'delete')
                ->setHref('javascript: void(0)')
                ->build();
            if (array_key_exists('delete_filtered', $bulkActions)) {
                $bulkActions['delete_filtered'] = $action;
            } else {
                $placeFirst = $action;
            }
        }
        if ($this->dataGridConfig->isEditAllowed() && $this->dataGridConfig->isFilteredItemsEditingAllowed()) {
            $action = Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.edit_filtered'))
                ->setDataAttr('action', 'bulk-edit-filtered')
                ->setHref('javascript: void(0)')
                ->build();
            if (array_key_exists('edit_filtered', $bulkActions)) {
                $bulkActions['edit_filtered'] = $action;
            } else {
                $placeFirst = $action;
            }
        }
        return array_merge($placeFirst, array_values($bulkActions));
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     * @throws \Swayok\Html\HtmlTagException
     */
    public function getToolbarItems() {
        $toolbar = [];
        foreach ($this->dataGridConfig->getToolbarItems() as $key => $toolbarItem) {
            if ($toolbarItem instanceof Tag) {
                $toolbarItem = $toolbarItem->build();
            }
            if (is_string($key)) {
                $toolbar[$key] = $toolbarItem;
            } else {
                $toolbar[] = $toolbarItem;
            }
        }
        $bulkActions = $this->getBulkActions();
        if (!empty($bulkActions)) {
            $dropdownBtn = Tag::button()
                ->setType('button')
                ->setClass('btn btn-default dropdown-toggle')
                ->setDataAttr('toggle' , 'dropdown')
                ->setAttribute('aria-haspopup', 'true')
                ->setAttribute('aria-expanded', 'false')
                ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.dropdown_label'))
                ->append('&nbsp;<span class="caret"></span>')
                ->build();

            $dropdownMenu = Tag::ul()
                ->setClass('dropdown-menu dropdown-menu-right')
                ->setContent('<li>' . implode('</li><li>', $bulkActions) . '</li>')
                ->build();

            $item = Tag::div()
                ->setClass('btn-group bulk-actions float-none')
                ->setContent($dropdownBtn . $dropdownMenu)
                ->build();
            if (array_key_exists('bulk_actions', $toolbar)) {
                $toolbar['bulk_actions'] = $item;
            } else {
                $toolbar[] = $item;
            }
        }
        if ($this->dataGridConfig->isCreateAllowed()) {
            $item = Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('toolbar.create'))
                ->setClass('btn btn-primary')
                ->setHref(routeToCmfItemAddForm($this->tableNameForRoutes))
                ->build();
            if (array_key_exists('create', $toolbar)) {
                $toolbar['create'] = $item;
            } else {
                $toolbar[] = $item;
            }
        }
        return array_values($toolbar);
    }

    /**
     * @return string
     */
    public function getNestedViewTriggerCellTemplate() {
        $showChildren = Tag::a()
            ->setClass('row-action link-muted show-children')
            ->setContent('<i class="glyphicon glyphicon-plus-sign"></i>')
            ->setTitle($this->dataGridConfig->translateGeneral('actions.show_children'))
            ->setDataAttr('toggle', 'tooltip')
            ->setHref('javascript: void(0)')
            ->build();
        $hideChildren = Tag::a()
            ->setClass('row-action link-muted hide-children hidden')
            ->setContent('<i class="glyphicon glyphicon-minus-sign"></i>')
            ->setTitle($this->dataGridConfig->translateGeneral('actions.hide_children'))
            ->setDataAttr('toggle', 'tooltip')
            ->setHref('javascript: void(0)')
            ->build();
        $buttons =
            '{{? it.___max_nesting_depth <= 0 || it.___max_nesting_depth > (parseInt(it.___nesting_depth) || 0) }}'
                . $showChildren . $hideChildren
            . '{{?}}';
        return Tag::div()
            ->setClass('row-actions text-nowrap')
            ->setContent($buttons)
            ->build();
    }

    /**
     * @return string
     */
    public function getRowActionsDotJsTemplate() {
        $this->rowActionsCount = 0;
        $rowActions = [];
        $placeFirst = [];


        foreach ($this->dataGridConfig->getRowActions() as $key => $rowAction) {
            if ($rowAction instanceof Tag) {
                $rowAction = $rowAction->build();
            }
            if (is_string($key)) {
                $rowActions[$key] = $rowAction;
            } else {
                $rowActions[] = $rowAction;
            }
        }

        if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            $btn = Tag::a()
                ->setClass('row-action text-light-blue item-details')
                ->setContent('<i class="glyphicon glyphicon-info-sign"></i>')
                ->setTitle($this->dataGridConfig->translateGeneral('actions.view_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setHref('{{= it.___details_url }}')
                ->build();
            $rowAction = '{{? !!it.___details_allowed && it.___details_url }}' . $btn . '{{?}}';
            if (array_key_exists('details', $rowActions)) {
                $rowActions['details'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isEditAllowed()) {
            $btn = Tag::a()
                ->setClass('row-action text-green item-edit')
                ->setContent('<i class="glyphicon glyphicon-edit"></i>')
                ->setTitle($this->dataGridConfig->translateGeneral('actions.edit_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setHref('{{= it.___edit_url }}')
                ->build();
            $rowAction = '{{? !!it.___edit_allowed && it.___edit_url }}' . $btn . '{{?}}';
            if (array_key_exists('edit', $rowActions)) {
                $rowActions['edit'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isCloningAllowed()) {
            $btn = Tag::a()
                ->setClass('row-action text-primary item-clone')
                ->setContent('<i class="fa fa-copy"></i>')
                ->setTitle($this->dataGridConfig->translateGeneral('actions.clone_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setHref('{{= it.___clone_url }}')
                ->build();
            $rowAction = '{{? !!it.___cloning_allowed && it.___clone_url }}' . $btn . '{{?}}';
            if (array_key_exists('clone', $rowActions)) {
                $rowActions['clone'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isDeleteAllowed()) {
            $btn = Tag::a()
                ->setContent('<i class="glyphicon glyphicon-trash"></i>')
                ->setClass('row-action text-red item-delete')
                ->setTitle($this->dataGridConfig->translateGeneral('actions.delete_item'))
                ->setDataAttr('toggle', 'tooltip')
                ->setDataAttr('block-datagrid', '1')
                ->setDataAttr('action', 'request')
                ->setDataAttr('method', 'delete')
                ->setDataAttr('url', '{{= it.___delete_url }}')
                ->setDataAttr('confirm', $this->dataGridConfig->translateGeneral('message.delete_item_confirm'))
                ->setHref('javascript: void(0)')
                ->build();
            $rowAction = '{{? !!it.___delete_allowed && it.___delete_url }}' . $btn . '{{?}}';
            if (array_key_exists('delete', $rowActions)) {
                $rowActions['delete'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        $this->rowActionsCount = count($rowActions);
        return Tag::div()
            ->setClass('row-actions text-nowrap')
            ->setContent(implode('', array_merge($placeFirst, array_values($rowActions))))
            ->build();
    }

    /**
     * @return int
     */
    public function getRowActionsCount() {
        return $this->rowActionsCount;
    }

    /**
     * @return null|string
     */
    public function getDoubleClickUrl() {
        $doubleClickUrl = null;
        if ($this->dataGridConfig->isEditAllowed()) {
            return routeToCmfItemEditForm($this->tableNameForRoutes, '{{= it.___pk_value }}');
        } else if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            return '{{= it.___details_url }}';
        }
        return null;
    }

    /**
     *
     * @throws \UnexpectedValueException
     * @throws \Swayok\Html\HtmlTagException
     * @throws \InvalidArgumentException
     */
    public function getDataTablesConfig() {
        $dataTablesConfig = array_replace(
            CmfConfig::getPrimary()->data_tables_config(),
            $this->dataGridConfig->getAdditionalDataTablesConfig(),
            [
                'resourceName' => $this->tableNameForRoutes,
                'pkColumnName' => $this->table->getPkColumnName(),
                'processing' => true,
                'serverSide' => true,
                'ajax' => cmfRoute('cmf_api_get_items', ['table_name' => $this->tableNameForRoutes], false),
                'pageLength' => $this->dataGridConfig->getRecordsPerPage(),
                'toolbarItems' => $this->getToolbarItems(),
                'order' => [],
                'multiselect' => $this->dataGridConfig->isAllowedMultiRowSelection(),
            ]
        );
        if (!$this->dataGridConfig->getOrderBy() instanceof DbExpr) {
            $dataTablesConfig['order'] = [
                [
                    $this->dataGridConfig->getValueViewer($this->dataGridConfig->getOrderBy())->getPosition(),
                    $this->dataGridConfig->getOrderDirection()
                ]
            ];
        }
        if ($this->dataGridConfig->isNestedViewEnabled()) {
            $dataTablesConfig['nested_data_grid'] = [
                'value_column' => '__' . $this->table->getPkColumnName(),
                'filter_column' => '__' . $this->dataGridConfig->getColumnNameForNestedView()
            ];
        }
        if ($this->dataGridConfig->isRowsReorderingEnabled()) {
            $dataTablesConfig['rowsReordering'] = [
                'columns' => $this->dataGridConfig->getRowsPositioningColumns(),
                'url' => cmfRouteTpl(
                    'cmf_api_change_item_position',
                    ['table_name' => $this->tableNameForRoutes],
                    [
                        'id' => 'it.moved_row.___pk_value',
                        'before_or_after',
                        'other_id' => 'it.other_row.___pk_value || 0',
                        'sort_column',
                        'sort_direction'
                    ]
                )
            ];
        }
        if ($this->dataGridConfig->isContextMenuEnabled()) {
            $dataTablesConfig['contextMenu'] = $this->getContextMenuItems();
        }
        return $dataTablesConfig;
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function getAdditionalViews() {
        $ret = '';
        $dataForViews = [
            'idSuffix' => $this->getIdSuffix(),
            'table' => $this->table,
            'dataGridConfig' => $this->dataGridConfig
        ];
        foreach ($this->dataGridConfig->getAdditionalViewsForTemplate() as $view => $data) {
            if (is_int($view)) {
                $view = $data;
                $data = [];
            } else if (!is_array($data)) {
                $data = [];
            }
            $ret .= view($view, $dataForViews, $data)->render() . "\n\n";
        }
        return $ret;
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getContextMenuItems() {
        $contextMenuItems = [
            'common' => []
        ];

        if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            $contextMenuItems['common']['details'] = [
                'label' => $this->dataGridConfig->translateGeneral('context_menu.view_item'),
                'icon' => 'glyphicon glyphicon-info-sign',
                'class' => 'text-light-blue item-details',
                'show' => '!!it.___details_allowed && it.___details_url',
                'url' => '{{= it.___details_url || "" }}',
            ];
        }
        if ($this->dataGridConfig->isEditAllowed()) {
            $contextMenuItems['common']['edit'] = [
                'label' => $this->dataGridConfig->translateGeneral('context_menu.edit_item'),
                'icon' => 'glyphicon glyphicon-edit',
                'class' => 'text-green item-edit',
                'show' => '!!it.___edit_allowed && it.___edit_url',
                'url' => '{{= it.___edit_url || "" }}',
            ];
        }
        if ($this->dataGridConfig->isCloningAllowed()) {
            $contextMenuItems['common']['clone'] = [
                'label' => $this->dataGridConfig->translateGeneral('context_menu.clone_item'),
                'icon' => 'fa fa-copy',
                'class' => 'text-primary item-clone',
                'show' => '!!it.___cloning_allowed && it.___clone_url',
                'url' => '{{= it.___clone_url || "" }}',
            ];
        }
        if ($this->dataGridConfig->isDeleteAllowed()) {
            $contextMenuItems['common']['delete'] = [
                'label' => $this->dataGridConfig->translateGeneral('context_menu.delete_item'),
                'icon' => 'glyphicon glyphicon-trash',
                'class' => 'text-red item-delete',
                'show' => '!!it.___delete_allowed && it.___delete_url',
                'url' => '{{= it.___delete_url || "" }}',
                'method' => 'delete',
                'action' => 'request',
                'block_datagrid' => true,
                'confirm' => $this->dataGridConfig->translateGeneral('message.delete_item_confirm')
            ];
        }

        $freeFormGroup = [];
        foreach ($this->dataGridConfig->getContextMenuItems() as $key => $menuItem) {
            if (is_string($menuItem)) {
                // replace $menuItem with menu item from $commonActionsGroup (details, edit, clone, delete, etc...)
                $code = $menuItem;
                if (array_key_exists($code, $contextMenuItems['common'])) {
                    $freeFormGroup[] = $contextMenuItems['common'][$code];
                    unset($contextMenuItems['common'][$code]);
                }
                continue;
            } else if (!is_array($menuItem)) {
                throw new \UnexpectedValueException(
                    '$menuItem must be an array. ' . gettype($menuItem) . ' received for key/index ' . $key
                );
            }
            if (array_key_exists('label', $menuItem)) {
                if (array_has($menuItem, ['label', 'url'])) {
                    $freeFormGroup[] = $menuItem;
                } else {
                    throw new \UnexpectedValueException(
                        '$menuItem array must have at least "label" and "url" keys'
                    );
                }
            } else if (count($menuItem) > 0) {
                // group
                if (count($freeFormGroup) > 0) {
                    $contextMenuItems[] = $freeFormGroup;
                    $freeFormGroup = [];
                }
                $group = [];
                foreach ($menuItem as $subKey => $realMenuItem) {
                    if (is_string($realMenuItem)) {
                        // replace $menuItem with menu item from $commonActionsGroup (details, edit, clone, delete, etc...)
                        $code = $realMenuItem;
                        if (array_key_exists($code, $contextMenuItems['common'])) {
                            $group[] = $contextMenuItems['common'][$code];
                            unset($contextMenuItems['common'][$code]);
                        }
                        continue;
                    } else if (!is_array($realMenuItem)) {
                        throw new \UnexpectedValueException(
                            '$realMenuItem must be an array. ' . gettype($realMenuItem) . ' received for key ' . $key . '->' . $subKey
                        );
                    }
                    if (array_has($realMenuItem, ['label', 'url'])) {
                        $group[] = $realMenuItem;
                    } else {
                        throw new \UnexpectedValueException(
                            '$realMenuItem array must have at least "label" and "url" keys'
                        );
                    }
                }
                $contextMenuItems[] = $group;
            }
        }
        if (count($freeFormGroup) > 0) {
            $contextMenuItems[] = $freeFormGroup;
        }
        // normalize or remove $commonActionsGroup
        if (count($contextMenuItems['common']) > 0) {
            $contextMenuItems['common'] = array_values($contextMenuItems['common']);
        } else {
            array_shift($contextMenuItems['common']);
        }
        return array_values($contextMenuItems);
    }
}