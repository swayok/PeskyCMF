<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\DataGrid;

use Illuminate\Support\Str;
use PeskyCMF\CmfUrl;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\MenuItem\CmfBulkActionMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfBulkActionRedirectMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRedirectMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRequestMenuItem;
use PeskyORM\DbExpr;
use PeskyORM\ORM\Table\TableInterface;
use Swayok\Html\Tag;

class DataGridRendererHelper
{
    protected DataGridConfig $dataGridConfig;
    protected string $resourceName;
    protected TableInterface $table;
    protected FilterConfig $dataGridFilterConfig;
    protected CmfConfig $cmfConfig;
    /** @var DataGridColumn[] */
    protected ?array $sortedColumnConfigs = null;
    protected ?string $idSuffix = null;
    protected ?string $id = null;
    protected int $rowActionsCount = 0;

    /**
     * DataGridRendererHelper constructor.
     * @param DataGridConfig $dataGridConfig
     */
    public function __construct(DataGridConfig $dataGridConfig)
    {
        $this->dataGridConfig = $dataGridConfig;
        $this->dataGridFilterConfig = $dataGridConfig->getScaffoldConfig()->getDataGridFilterConfig();
        $this->table = $dataGridConfig->getScaffoldConfig()->getTable();
        $this->resourceName = $dataGridConfig->getScaffoldConfig()->getResourceName();
        $this->cmfConfig = $dataGridConfig->getCmfConfig();
    }

    public function getIdSuffix(): string
    {
        if (!$this->idSuffix) {
            $this->idSuffix = Str::slug(strtolower($this->resourceName));
        }
        return $this->idSuffix;
    }

    public function getId(): string
    {
        if (!$this->id) {
            $this->id = 'scaffold-data-grid-' . $this->getIdSuffix();
        }
        return $this->id;
    }

    protected function getPkColumnName(): string
    {
        return $this->table->getPkColumnName();
    }

    public function getHtmlTableMultiselectColumnHeader(): string
    {
        $dropdownBtn = Tag::button()
            ->setType('button')
            ->setClass('rows-selection-options-dropdown-btn')
            ->setDataAttr('toggle', 'dropdown')
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
                ->build(),
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

    public function getHtmlTableNestedViewsColumnHeader(): string
    {
        return Tag::th()
            ->setContent('&nbsp;')
            ->setDataAttr('visible', 'true')
            ->setDataAttr('orderable', 'false')
            ->setDataAttr('name', $this->getPkColumnName())
            ->setDataAttr('data', $this->getPkColumnName())
            ->build();
    }

    /**
     * @return AbstractValueViewer[]|DataGridColumn[]
     */
    public function getSortedColumnConfigs(): array
    {
        if (!$this->sortedColumnConfigs) {
            $this->sortedColumnConfigs = $this->dataGridConfig->getDataGridColumns();
            uasort(
                $this->sortedColumnConfigs,
                static function (DataGridColumn $a, DataGridColumn $b) {
                    return ($a->getPosition() > $b->getPosition());
                }
            );
        }
        return $this->sortedColumnConfigs;
    }

    public function getHtmlTableColumnsHeaders(): string
    {
        $invisibleColumns = '';
        $visibleColumns = '';
        if ($this->dataGridConfig->isAllowedMultiRowSelection()) {
            $visibleColumns .= $this->getHtmlTableMultiselectColumnHeader() . "\n";
        }
        if ($this->dataGridConfig->isNestedViewEnabled()) {
            $visibleColumns .= $this->getHtmlTableNestedViewsColumnHeader() . "\n";
        }
        $columns = $this->getSortedColumnConfigs();
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

    public function getBulkActions(): array
    {
        $bulkActions = [];
        $pkName = $this->getPkColumnName();
        $isAllowedMultiRowSelection = $this->dataGridConfig->isAllowedMultiRowSelection();
        if ($isAllowedMultiRowSelection) {
            if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isBulkItemsDeleteAllowed()) {
                $url = CmfUrl::route(
                    'cmf_api_delete_bulk',
                    ['resource' => $this->resourceName],
                    false,
                    $this->cmfConfig
                );
                $bulkActions['delete_selected'] = CmfMenuItem::bulkActionOnSelectedRows($url, 'delete')
                    ->setTitle($this->dataGridConfig->translateGeneral('bulk_actions.delete_selected'))
                    ->setConfirm(
                        $this->dataGridConfig->translateGeneral(
                            'bulk_actions.message.delete_bulk.delete_selected_confirm'
                        )
                    )
                    ->setPrimaryKeyColumnName($pkName)
                    ->renderAsBootstrapDropdownMenuItem();
            }
            if ($this->dataGridConfig->isEditAllowed() && $this->dataGridConfig->isBulkItemsEditingAllowed()) {
                $action = Tag::a()
                    ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.edit_selected'))
                    ->setDataAttr('action', 'bulk-edit-selected')
                    ->setDataAttr('id-field', $pkName)
                    ->setHref('javascript: void(0)')
                    ->build();
                $bulkActions['edit_selected'] = '<li>' . $action . '</li>';
            }
        }
        if ($this->dataGridConfig->isDeleteAllowed() && $this->dataGridConfig->isFilteredItemsDeleteAllowed()) {
            $url = CmfUrl::route('cmf_api_delete_bulk', ['resource' => $this->resourceName], false, $this->cmfConfig);
            $bulkActions['delete_filtered'] = CmfMenuItem::bulkActionOnFilteredRows($url, 'delete')
                ->setTitle($this->dataGridConfig->translateGeneral('bulk_actions.delete_filtered'))
                ->setConfirm(
                    $this->dataGridConfig->translateGeneral(
                        'bulk_actions.message.delete_bulk.delete_filtered_confirm'
                    )
                )
                ->renderAsBootstrapDropdownMenuItem();
        }
        if ($this->dataGridConfig->isEditAllowed() && $this->dataGridConfig->isFilteredItemsEditingAllowed()) {
            $action = Tag::a()
                ->setContent($this->dataGridConfig->translateGeneral('bulk_actions.edit_filtered'))
                ->setDataAttr('action', 'bulk-edit-filtered')
                ->setHref('javascript: void(0)')
                ->build();
            $bulkActions['edit_filtered'] = '<li>' . $action . '</li>';
        }
        foreach ($this->dataGridConfig->getBulkActionsToolbarItems() as $key => $bulkAction) {
            if ($bulkAction instanceof Tag) {
                $bulkAction = $bulkAction->build();
            } elseif (
                $bulkAction instanceof CmfBulkActionMenuItem
                || $bulkAction instanceof CmfBulkActionRedirectMenuItem
            ) {
                if (
                    !$isAllowedMultiRowSelection
                    && $bulkAction->getActionType() === $bulkAction::ACTION_TYPE_BULK_SELECTED
                ) {
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
        return array_values($bulkActions);
    }

    public function getToolbarItems(): array
    {
        $toolbar = [];
        foreach ($this->dataGridConfig->getToolbarItems() as $key => $toolbarItem) {
            if ($toolbarItem instanceof Tag) {
                $toolbarItem = $toolbarItem->build();
            } elseif ($toolbarItem instanceof CmfMenuItem) {
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
                ->setDataAttr('toggle', 'dropdown')
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

    public function getNestedViewTriggerCellTemplate(): string
    {
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
     * @param string $actionKey - one of 'edit', 'delete', 'clode', 'details'
     *      or key from $this->dataGridConfig->getRowActions()
     * @param bool   $render - render row action if it is an object or not
     * @return CmfRedirectMenuItem|CmfRequestMenuItem|Tag|string
     */
    public function getRowActionDotJsTemplate(
        string $actionKey,
        bool $render = true
    ): CmfRequestMenuItem|CmfRedirectMenuItem|Tag|string {
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
                }
                if ($rowAction instanceof Tag) {
                    return $rowAction->build();
                }
                if ($rowAction instanceof CmfMenuItem) {
                    return $rowAction->renderAsButton();
                }
                return $rowAction;
        }
    }

    public function getRowActionsDotJsTemplate(): string
    {
        $this->rowActionsCount = 0;
        $rowActions = [];
        $placeFirst = [];


        foreach ($this->dataGridConfig->getRowActions() as $key => $rowAction) {
            if ($rowAction instanceof Tag) {
                $rowAction = $rowAction->build();
            } elseif ($rowAction instanceof CmfMenuItem) {
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

    public function getRowActionsCount(): int
    {
        return $this->rowActionsCount;
    }

    public function getDoubleClickUrl(): ?string
    {
        if ($this->dataGridConfig->isEditAllowed()) {
            return $this->dataGridConfig->getScaffoldConfig()->getUrlToItemEditForm('{{= it.___pk_value }}');
        }
        if ($this->dataGridConfig->isDetailsViewerAllowed()) {
            return '{{= it.___details_url }}';
        }
        return null;
    }

    public function getDataTablesConfig(): array
    {
        $dataTablesConfig = array_replace(
            $this->cmfConfig->dataTablesConfig(),
            $this->dataGridConfig->getAdditionalDataTablesConfig(),
            [
                'resourceName' => $this->resourceName,
                'pkColumnName' => $this->getPkColumnName(),
                'processing' => true,
                'serverSide' => true,
                'ajax' => CmfUrl::route(
                    'cmf_api_get_items',
                    ['resource' => $this->resourceName],
                    false,
                    $this->cmfConfig
                ),
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
                    preg_replace('%^\s*(asc|desc).*$%i', '$1', $this->dataGridConfig->getOrderDirection()),
                ],
            ];
        }
        if ($this->dataGridConfig->isNestedViewEnabled()) {
            $dataTablesConfig['nested_data_grid'] = [
                'value_column' => '__' . $this->getPkColumnName(),
                'filter_column' => '__' . $this->dataGridConfig->getColumnNameForNestedView(),
            ];
        }
        if ($this->dataGridConfig->isRowsReorderingEnabled()) {
            $dataTablesConfig['rowsReordering'] = [
                'columns' => $this->dataGridConfig->getRowsPositioningColumns(),
                'url' => CmfUrl::routeTpl(
                    'cmf_api_change_item_position',
                    ['resource' => $this->resourceName],
                    [
                        'id' => 'it.moved_row.___pk_value',
                        'before_or_after',
                        'other_id' => 'it.other_row.___pk_value || 0',
                        'sort_column',
                        'sort_direction',
                    ],
                    false,
                    $this->cmfConfig
                ),
            ];
        }
        if ($this->dataGridConfig->isContextMenuEnabled()) {
            $dataTablesConfig['contextMenuTpl'] = $this->getContextMenuDotJsTemplate();
        }

        return $dataTablesConfig;
    }

    public function getAdditionalViews(): string
    {
        $ret = '';
        $dataForViews = [
            'idSuffix' => $this->getIdSuffix(),
            'table' => $this->table,
            'dataGridConfig' => $this->dataGridConfig,
        ];
        foreach ($this->dataGridConfig->getAdditionalViewsForTemplate() as $view => $data) {
            if (is_int($view)) {
                $view = $data;
                $data = [];
            } elseif (!is_array($data)) {
                $data = [];
            }
            $ret .= view($view, $dataForViews, $data)->render() . "\n\n";
        }
        return $ret;
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function getContextMenuDotJsTemplate(): string
    {
        $contextMenuItems = [
            'common' => [],
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
            }
            if ($menuItem instanceof Tag) {
                $freeFormGroup[] = $menuItem->build();
            } elseif ($menuItem instanceof CmfMenuItem) {
                $freeFormGroup[] = $menuItem->renderAsBootstrapDropdownMenuItem();
            } elseif (is_string($menuItem)) {
                $freeFormGroup[] = $menuItem;
            } elseif (is_array($menuItem)) {
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
                } elseif ($menuItem instanceof CmfMenuItem) {
                    $menuItem = $menuItem->renderAsBootstrapDropdownMenuItem();
                } elseif (!is_string($menuItem)) {
                    throw new \UnexpectedValueException(
                        '$menuItem must be an string or instance of Tag or CmfMenuItem class. '
                        . gettype($menuItem) . ' received'
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
