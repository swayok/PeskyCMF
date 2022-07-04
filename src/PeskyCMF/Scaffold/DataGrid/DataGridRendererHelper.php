<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Scaffold\MenuItem\CmfBulkActionMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRedirectMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRequestMenuItem;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class DataGridRendererHelper {

    /** @var DataGridConfig  */
    protected $dataGridConfig;
    /** @var string */
    protected $resourceName;
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
     */
    public function __construct(DataGridConfig $dataGridConfig) {
        $this->dataGridConfig = $dataGridConfig;
        $this->dataGridFilterConfig = $dataGridConfig->getScaffoldConfig()->getDataGridFilterConfig();
        $this->table = $dataGridConfig->getScaffoldConfig()->getTable();
        $this->resourceName = $dataGridConfig->getScaffoldConfig()->getResourceName();
    }

    /**
     * @return string
     */
    public function getIdSuffix() {
        if (!$this->idSuffix) {
            $this->idSuffix = str_slug(strtolower($this->resourceName));
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
            $dtName = $config::convertNameForDataTables($config->getName());
            $th = Tag::th()
                ->setContent($config->isVisible() ? $config->getLabel() : '&nbsp;')
                ->setClass('text-nowrap')
                ->setDataAttr('visible', $config->isVisible() ? null : 'false')
                ->setDataAttr('orderable', $config->isVisible() && $config->isSortable() ? 'true' : 'false')
                ->setDataAttr('name', $dtName)
                ->setDataAttr('data', $dtName)
                ->setDataAttr('default-content', ' ')
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
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function getBulkActions() {
        $bulkActions = [];
        $placeFirst = [];
        $pkName = $this->table->getPkColumnName();
        $isAllowedMultiRowSelection = $this->dataGridConfig->isAllowedMultiRowSelection();
        if ($isAllowedMultiRowSelection) {
            /** @noinspection StaticInvocationViaThisInspection */
            if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isBulkItemsDeleteAllowed()) {
                $action = CmfMenuItem::bulkActionOnSelectedRows(cmfRoute('cmf_api_delete_bulk', [$this->resourceName]), 'delete')
                    ->setTitle($this->dataGridConfig->translateGeneral('bulk_actions.delete_selected'))
                    ->setConfirm($this->dataGridConfig->translateGeneral('bulk_actions.message.delete_bulk.delete_selected_confirm'))
                    ->setPrimaryKeyColumnName($pkName)
                    ->renderAsBootstrapDropdownMenuItem();

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
                $action = '<li>' . $action . '</li>';
                if (array_key_exists('edit_selected', $bulkActions)) {
                    $bulkActions['edit_selected'] = $action;
                } else {
                    $placeFirst[] = $action;
                }
            }
        }
        if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isFilteredItemsDeleteAllowed()) {
            $action = CmfMenuItem::bulkActionOnFilteredRows(cmfRoute('cmf_api_delete_bulk', [$this->resourceName]), 'delete')
                ->setTitle($this->dataGridConfig->translateGeneral('bulk_actions.delete_filtered'))
                ->setConfirm($this->dataGridConfig->translateGeneral('bulk_actions.message.delete_bulk.delete_filtered_confirm'))
                ->renderAsBootstrapDropdownMenuItem();
            if (array_key_exists('delete_filtered', $bulkActions)) {
                $bulkActions['delete_filtered'] = $action;
            } else {
                $placeFirst[] = $action;
            }
        }
        if ($this->dataGridConfig->isEditAllowed() && $this->dataGridConfig->isFilteredItemsEditingAllowed()) {
            $action = Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.edit_filtered'))
                ->setDataAttr('action', 'bulk-edit-filtered')
                ->setHref('javascript: void(0)')
                ->build();
            $action = '<li>' . $action . '</li>';
            if (array_key_exists('edit_filtered', $bulkActions)) {
                $bulkActions['edit_filtered'] = $action;
            } else {
                $placeFirst[] = $action;
            }
        }
        foreach ($this->dataGridConfig->getBulkActionsToolbarItems() as $key => $bulkAction) {
            if ($bulkAction instanceof Tag) {
                $bulkAction = $bulkAction->build();
            } else if ($bulkAction instanceof CmfBulkActionMenuItem) {
                if (!$isAllowedMultiRowSelection && $bulkAction->getActionType() === CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED) {
                    // it is imposible to use bulk action on selected rows while there are no checkboxes to select rows
                    continue;
                }
                if (empty($bulkAction->getPrimaryKeyColumnName())) {
                    $bulkAction->setPrimaryKeyColumnName($pkName);
                }
                $bulkAction = $bulkAction->renderAsBootstrapDropdownMenuItem();
            }
            if (is_string($key)) {
                $bulkActions[$key] = $bulkAction;
            } else {
                $bulkActions[] = $bulkAction;
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
            } else if ($toolbarItem instanceof CmfMenuItem) {
                $toolbarItem = $toolbarItem->renderAsButton();
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
                ->setContent(implode('', $bulkActions))
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
            $item = $this->dataGridConfig
                ->getItemCreateMenuItem()
                ->setConditionToShow('true')
                ->renderAsButton(false);
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
            ->setContent('<i class="glyphicon glyphicon-folder-close"></i>')
            ->setTitle($this->dataGridConfig->translateGeneral('actions.show_children'))
            ->setDataAttr('toggle', 'tooltip')
            ->setHref('javascript: void(0)')
            ->build();
        $hideChildren = Tag::a()
            ->setClass('row-action link-muted hide-children hidden')
            ->setContent('<i class="glyphicon glyphicon-folder-open"></i>')
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
     * @param string $actionKey - one of 'edit', 'delete', 'clode', 'details' or key from $this->dataGridConfig->getRowActions()
     * @param bool $render - render row action if it is an object or not
     * @return CmfRedirectMenuItem|CmfRequestMenuItem|Tag|string
     */
    public function getRowActionDotJsTemplate($actionKey, $render = true) {
        switch ($actionKey) {
            case 'details':
                $rowAction = $this->dataGridConfig->getItemDetailsMenuItem('actions');
                return $render ? $rowAction->renderAsIcon('row-action item-details') : $rowAction;
            case 'edit':
                $rowAction = $this->dataGridConfig->getItemEditMenuItem('actions');
                return $render ? $rowAction->renderAsIcon('row-action item-edit') : $rowAction;
            case 'clone':
                $rowAction = $this->dataGridConfig->getItemCloneMenuItem('actions');
                return $render ? $rowAction->renderAsIcon('row-action item-clone') : $rowAction;
            case 'delete':
                $rowAction = $this->dataGridConfig->getItemDeleteMenuItem('actions');
                return $render ? $rowAction->renderAsIcon('row-action item-delete') : $rowAction;
            default:
                $customActions = $this->dataGridConfig->getRowActions();
                if (!isset($customActions[$actionKey])) {
                    throw new \InvalidArgumentException('Unknown row action key: ' . $actionKey);
                }
                $rowAction = $customActions[$actionKey];
                if (!$render) {
                    return $rowAction;
                } else if ($rowAction instanceof Tag) {
                    return $rowAction->build();
                } else if ($rowAction instanceof CmfMenuItem) {
                    return $rowAction->renderAsButton();
                } else {
                    return $rowAction;
                }
        }
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getRowActionsDotJsTemplate() {
        $this->rowActionsCount = 0;
        $rowActions = [];
        $placeFirst = [];


        foreach ($this->dataGridConfig->getRowActions() as $key => $rowAction) {
            if ($rowAction instanceof Tag) {
                $rowAction = $rowAction->build();
            } else if ($rowAction instanceof CmfMenuItem) {
                $rowAction = $rowAction->renderAsIcon('row-action');
            }
            if (is_string($key)) {
                $rowActions[$key] = $rowAction;
            } else {
                $rowActions[] = $rowAction;
            }
        }

        if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            $rowAction = $this->getRowActionDotJsTemplate('details', true);
            if (array_key_exists('details', $rowActions)) {
                $rowActions['details'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isEditAllowed()) {
            $rowAction = $this->getRowActionDotJsTemplate('edit', true);
            if (array_key_exists('edit', $rowActions)) {
                $rowActions['edit'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isCloningAllowed()) {
            $rowAction = $this->getRowActionDotJsTemplate('clone', true);
            if (array_key_exists('clone', $rowActions)) {
                $rowActions['clone'] = $rowAction;
            } else {
                $placeFirst[] = $rowAction;
            }
        }
        if ($this->dataGridConfig->isDeleteAllowed()) {
            $rowAction = $this->getRowActionDotJsTemplate('delete', true);
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
            return routeToCmfItemEditForm($this->resourceName, '{{= it.___pk_value }}');
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
            $this->dataGridConfig->getScaffoldConfig()->getCmfConfig()->data_tables_config(),
            $this->dataGridConfig->getAdditionalDataTablesConfig(),
            [
                'resourceName' => $this->resourceName,
                'pkColumnName' => $this->table->getPkColumnName(),
                'processing' => true,
                'serverSide' => true,
                'ajax' => cmfRoute('cmf_api_get_items', ['resource' => $this->resourceName], false),
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
                    preg_replace('%^\s*(asc|desc).*$%i', '$1', $this->dataGridConfig->getOrderDirection())
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
                    ['resource' => $this->resourceName],
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
            $dataTablesConfig['contextMenuTpl'] = $this->getContextMenuDotJsTemplate();
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
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getContextMenuDotJsTemplate() {
        $contextMenuItems = [
            'common' => []
        ];

        if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            $contextMenuItems['common']['details'] = $this->dataGridConfig->getItemDetailsMenuItem('context_menu');
        }
        if ($this->dataGridConfig->isEditAllowed()) {
            $contextMenuItems['common']['edit'] = $this->dataGridConfig->getItemEditMenuItem('context_menu');
        }
        if ($this->dataGridConfig->isCloningAllowed()) {
            $contextMenuItems['common']['clone'] = $this->dataGridConfig->getItemCloneMenuItem('context_menu');
        }
        if ($this->dataGridConfig->isDeleteAllowed()) {
            $contextMenuItems['common']['delete'] = $this->dataGridConfig->getItemDeleteMenuItem('context_menu');
        }
        // normalize into groups
        $freeFormGroup = [];
        foreach ($this->dataGridConfig->getContextMenuItems() as $key => $menuItem) {
            if (is_string($key) && empty($menuItem)) {
                // replace $menuItem with menu item from $commonActionsGroup (details, edit, clone, delete, etc...)
                if (array_key_exists($key, $contextMenuItems['common'])) {
                    $freeFormGroup[] = $contextMenuItems['common'][$key];
                    unset($contextMenuItems['common'][$key]);
                }
                continue;
            } else if ($menuItem instanceof Tag) {
                $freeFormGroup[] = $menuItem->build();
            } else if ($menuItem instanceof CmfMenuItem) {
                $freeFormGroup[] = $menuItem->renderAsBootstrapDropdownMenuItem();
            } else if (is_string($menuItem)) {
                $freeFormGroup[] = $menuItem;
            } else if (is_array($menuItem)) {
                // group of menu items
                if (count($freeFormGroup) > 0) {
                    $contextMenuItems[] = $freeFormGroup;
                    $freeFormGroup = [];
                }
                if (count($menuItem) > 0) {
                    $contextMenuItems[] = $menuItem;
                }
            } else {
                throw new \UnexpectedValueException(
                    '$menuItem must be an array. ' . gettype($menuItem) . ' received for key/index ' . $key
                );
            }
        }
        if (count($freeFormGroup) > 0) {
            $contextMenuItems[] = $freeFormGroup;
        }
        // normalize menu items and create dot.js template
        $template = [];
        foreach ($contextMenuItems as &$group) {
            if (count($group) === 0) {
                continue;
            }
            $groupTemplate = '';
            foreach ($group as &$menuItem) {
                if ($menuItem instanceof Tag) {
                    $menuItem = $menuItem->build();
                } else if ($menuItem instanceof CmfMenuItem) {
                    $menuItem = $menuItem->renderAsBootstrapDropdownMenuItem();
                } else if (!is_string($menuItem)) {
                    throw new \UnexpectedValueException(
                        '$menuItem must be an string or instance of Tag or CmfMenuItem class. ' . gettype($menuItem) . ' received'
                    );
                }
                $groupTemplate .= '<li>' . $menuItem . '</li>';
            }
            unset($menuItem);
            $template[] = $groupTemplate;
        }
        return '<ul class="dropdown-menu datagrid-context-menu">'
                . implode('<li role="separator" class="divider"></li>', $template)
            . '</ul>';
    }
}