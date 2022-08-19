<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\DataGrid;

use Illuminate\Support\Arr;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\MenuItem\CmfMenuItem;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

class DataGridConfig extends ScaffoldSectionConfig
{
    
    public const ROW_ACTIONS_COLUMN_NAME = '__actions';
    
    public const ORDER_ASC = 'asc';
    public const ORDER_DESC = 'desc';
    public const ORDER_ASC_NULLS_FIRST = 'asc nulls first';
    public const ORDER_ASC_NULLS_LAST = 'asc nulls last';
    public const ORDER_DESC_NULLS_FIRST = 'desc nulls first';
    public const ORDER_DESC_NULLS_LAST = 'desc nulls last';
    
    protected static array $orderOptions = [
        self::ORDER_ASC,
        self::ORDER_DESC,
        self::ORDER_ASC_NULLS_FIRST,
        self::ORDER_ASC_NULLS_LAST,
        self::ORDER_DESC_NULLS_FIRST,
        self::ORDER_DESC_NULLS_LAST,
    ];
    
    protected bool $allowRelationsInValueViewers = true;
    
    protected bool $allowComplexValueViewerNames = true;
    
    protected string $template = 'cmf::scaffold.datagrid';
    protected int $recordsPerPage = 25;
    protected int $offset = 0;
    protected int $maxLimit = 100;
    /**
     * @var string|DbExpr
     */
    protected $orderBy;
    protected ?string $orderDirection = self::ORDER_ASC;
    
    /**
     * Add a checkboxes column to datagrid so user can select several rows and perform bulk-actions
     */
    protected bool $allowMultiRowSelection = false;
    protected bool $allowBulkItemsEditing = false;
    protected bool $allowBulkItemsDelete = true;
    protected bool $allowFilteredItemsEditing = false;
    protected bool $allowFilteredItemsDelete = false;
    protected ?\Closure $bulkActionsToolbarItems = null;
    protected bool $isRowActionsEnabled = true;
    protected ?\Closure $rowActions = null;
    protected array $additionalDataTablesConfig = [];
    protected bool $isRowActionsColumnFixed = true;
    protected bool $isFilterOpened = false;
    protected ?string $enableNestedViewBasedOnColumn = null;
    protected int $nestedViewsDepthLimit = -1;
    protected array $rowsPositioningColumns = [];
    protected ?\Closure $contextMenuItems = null;
    protected bool $isContextMenuEnabled = true;
    protected bool $isMultiRowSelectionColumnFixed = true;
    /**
     * @var array|\Closure
     */
    protected $additionalViewsForTemplate = [];
    protected ?DataGridRendererHelper $rendererHelper = null;
    protected bool $openInModal = false;
    /**
     * optimize datagrid selects for usage in big tables so that it will be faster to select rows
     */
    protected bool $isBigTable = false;
    
    public function __construct(TableInterface $table, ScaffoldConfig $scaffoldConfig)
    {
        parent::__construct($table, $scaffoldConfig);
        $this->recordsPerPage = $this->getCmfConfig()->rows_per_page();
        $this->setOrderBy($table->getPkColumnName());
    }
    
    /**
     * @private
     * @param bool $isEnabled
     * @param string|null $size
     * @return static
     * @throws \BadMethodCallException
     */
    public function setModalConfig(bool $isEnabled = false, ?string $size = null)
    {
        throw new \BadMethodCallException('Data grid cannot be opened in modal');
    }
    
    /**
     * Alias for setValueViewers
     * @param array $datagridColumns
     * @return static
     */
    public function setColumns(array $datagridColumns)
    {
        return $this->setValueViewers($datagridColumns);
    }
    
    /**
     * @return DataGridColumn[]|AbstractValueViewer[]
     */
    public function getDataGridColumns(): array
    {
        return $this->getValueViewers();
    }
    
    public function hasDataGridColumn(string $name): bool
    {
        return $this->hasValueViewer($name);
    }
    
    protected function createValueRenderer(): DataGridCellRenderer
    {
        return DataGridCellRenderer::create();
    }
    
    /**
     * @return static
     */
    public function setInvisibleColumns(...$columnNames)
    {
        return call_user_func_array([$this, 'setAdditionalColumnsToSelect'], $columnNames);
    }
    
    /**
     * Mimics setInvisibleColumns()
     * @return static
     */
    public function setAdditionalColumnsToSelect(...$columnNames)
    {
        parent::setAdditionalColumnsToSelect($columnNames);
        foreach ($this->getAdditionalColumnsToSelect() as $name) {
            $valueViewer = DataGridColumn::create()->setIsVisible(false);
            $this->addValueViewer($name, $valueViewer);
        }
        return $this;
    }
    
    /**
     * @param AbstractValueViewer|DataGridColumn $viewer
     * @return int
     */
    protected function getNextValueViewerPosition(AbstractValueViewer $viewer): int
    {
        if ($viewer->isVisible()) {
            /** @var DataGridColumn $otherValueViewer */
            $count = 0;
            foreach ($this->valueViewers as $otherValueViewer) {
                if ($otherValueViewer->isVisible()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return -1;
        }
    }
    
    /**
     * @param bool $shown - true: filter will be opened on data grid load | false: filter will be hidden
     * @return static
     */
    public function setFilterIsOpenedByDefault(bool $shown = true): DataGridConfig
    {
        $this->isFilterOpened = $shown;
        return $this;
    }
    
    /**
     * @return static
     */
    public function openFilterByDefault()
    {
        $this->isFilterOpened = true;
        return $this;
    }
    
    /**
     * @return static
     */
    public function closeFilterByDefault()
    {
        $this->isFilterOpened = false;
        return $this;
    }
    
    public function isFilterOpenedByDefault(): bool
    {
        return $this->isFilterOpened;
    }
    
    /**
     * @return static
     */
    public function setIsBigTable(bool $isBigTable = true)
    {
        $this->isBigTable = $isBigTable;
        return $this;
    }
    
    public function isBigTable(): bool
    {
        return $this->isBigTable;
    }
    
    public function getRecordsPerPage(): int
    {
        return $this->recordsPerPage;
    }
    
    /**
     * @return static
     */
    public function setRecordsPerPage(int $recordsPerPage)
    {
        $this->recordsPerPage = min($this->maxLimit, $recordsPerPage);
        return $this;
    }
    
    public function getOffset(): int
    {
        return $this->offset;
    }
    
    /**
     * @return static
     */
    public function setOffset(int $offset)
    {
        $this->offset = max($offset, 0);
        return $this;
    }
    
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }
    
    /**
     * @param string|DbExpr $orderBy
     * @param null|string $direction - 'asc', 'desc', 'asc nulls first' or 'desc nulls last' in any case or DataGridConfig::ORDER_* constants
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setOrderBy($orderBy, ?string $direction = null)
    {
        if (!($orderBy instanceof DbExpr) && !$this->getTable()->getTableStructure()->hasColumn($orderBy)) {
            throw new \InvalidArgumentException(get_class($this->getTable()->getTableStructure()) . " has no column [$orderBy]");
        }
        if ($orderBy instanceof DbExpr) {
            $orderBy->setWrapInBrackets(false);
        }
        $this->setOrderDirection($direction ?? static::ORDER_ASC);
        $this->orderBy = $orderBy;
        return $this;
    }
    
    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }
    
    /**
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setOrderDirection(string $orderDirection)
    {
        $orderDirection = strtolower($orderDirection);
        if (!in_array($orderDirection, static::$orderOptions, true)) {
            throw new \InvalidArgumentException(
                "Invalid order direction [$orderDirection]. Expected one of: " . implode(', ', static::$orderOptions)
            );
        }
        $this->orderDirection = $orderDirection;
        return $this;
    }
    
    public function isAllowedMultiRowSelection(): bool
    {
        return $this->allowMultiRowSelection;
    }
    
    /**
     * @return static
     */
    public function setMultiRowSelection(bool $allowMultiRowSelection)
    {
        $this->allowMultiRowSelection = $allowMultiRowSelection;
        return $this;
    }
    
    /**
     * Pass 'true' to fix/stick multi-row selection column in data grid so it will not move during
     * horisontal scrolling of data grid
     * @return static
     */
    public function setIsMultiRowSelectionColumnFixed(bool $isFixed)
    {
        $this->isMultiRowSelectionColumnFixed = $isFixed;
        return $this;
    }
    
    public function isMultiRowSelectionColumnFixed(): bool
    {
        return $this->isMultiRowSelectionColumnFixed;
    }
    
    /**
     * @return static
     */
    public function setIsBulkItemsDeleteAllowed(bool $isAllowed)
    {
        $this->allowBulkItemsDelete = $isAllowed;
        $this->setMultiRowSelection(true);
        return $this;
    }
    
    public function isBulkItemsDeleteAllowed(): bool
    {
        return $this->allowBulkItemsDelete;
    }
    
    /**
     * Bulk editable columns provided via FormConfig->setBulkEditableColumns() or FormConfig->addBulkEditableColumns()
     * @return static
     */
    public function setIsBulkItemsEditingAllowed(bool $isAllowed)
    {
        $this->allowBulkItemsEditing = $isAllowed;
        $this->setMultiRowSelection(true);
        return $this;
    }
    
    public function isBulkItemsEditingAllowed(): bool
    {
        return $this->allowBulkItemsEditing;
    }
    
    /**
     * @return static
     */
    public function setIsFilteredItemsDeleteAllowed(bool $isAllowed)
    {
        $this->allowFilteredItemsDelete = $isAllowed;
        return $this;
    }
    
    public function isFilteredItemsDeleteAllowed(): bool
    {
        return $this->allowFilteredItemsDelete;
    }
    
    /**
     * @return static
     */
    public function setIsFilteredItemsEditingAllowed(bool $isAllowed)
    {
        $this->allowFilteredItemsEditing = $isAllowed;
        return $this;
    }
    
    public function isFilteredItemsEditingAllowed(): bool
    {
        return $this->allowFilteredItemsEditing;
    }
    
    /**
     * @return string[]
     * @throws \UnexpectedValueException
     */
    public function getBulkActionsToolbarItems(): array
    {
        if (empty($this->bulkActionsToolbarItems)) {
            return [];
        }
        $bulkActionsToolbarItems = call_user_func($this->bulkActionsToolbarItems, $this);
        if (!is_array($bulkActionsToolbarItems)) {
            throw new \UnexpectedValueException(get_class($this) . '->bulkActionsToolbarItems closure must return an array');
        }
        /** @var Tag|string $item */
        /** @var array $bulkActionsToolbarItems */
        foreach ($bulkActionsToolbarItems as &$item) {
            if (is_object($item)) {
                /** @noinspection MissingOrEmptyGroupStatementInspection */
                /** @noinspection PhpStatementHasEmptyBodyInspection */
                if ($item instanceof CmfMenuItem) {
                    // do nothing
                } elseif (method_exists($item, 'build')) {
                    $item = $item->build();
                } elseif (method_exists($item, '__toString')) {
                    $item = (string)$item;
                } else {
                    throw new \UnexpectedValueException(
                        get_class(
                            $this
                        ) . '->bulkActionsToolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } elseif (!is_string($item)) {
                throw new \UnexpectedValueException(
                    get_class($this) . '->bulkActionsToolbarItems: array may contain only strings and objects with build() or __toString() methods'
                );
            }
        }
        return $bulkActionsToolbarItems;
    }
    
    /**
     * @param \Closure $callback - function (ScaffoldSectionConfig $scaffoldSectionConfig) { return []; }
     * Callback must return an array.
     * Array may contain only strings, Tag class instances, or any object with build() or __toString() method
     * Examples:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
     * CmfRequestMenuItem::bulkActionOnSelectedRows(static::getUrlCustomAction('bulk_action_name'), 'delete')
     * // use ':count' in setTitle() to insert selected items count
     * ->setTitle('<span class="label label-primary">:count</span> ' . $this->translate('datagrid.bulk_action.action_name'))
     * ->setTooltip($this->translate('datagrid.bulk_action.action_name_tooltip'))
     * // ask user to confirm action
     * ->setConfirm($this->translate('datagrid.bulk_action.action_name_confirmation'))
     * // use this when you need to receive data from column other then primary key (default: current table's primary key column name)
     * ->setPrimaryKeyColumnName('parent_id')
     * // one of: json, html, xml. Default: 'json' (may be useful for your custom response handler)
     * ->setResponseDataType('json')
     * // set custom response handler function.
     * // callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * // It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setOnSuccess('callbackFuncitonName');
     * or
     * Tag::li(
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert selected items count
     * ->setDataAttr('action', 'bulk-selected')
     * ->setDataAttr('confirm', trans('path.to.translation'))
     * //^ confirm action before sending request to server
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('method', 'delete')
     * //^ can be 'post', 'put', 'delete' depending on action type
     * ->setDataAttr('id-field', 'id')
     * //^ id field name to use to get rows ids, default: 'id'
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setDataAttr('response-type', 'json')
     * //^ one of: json, html, xml. Default: 'json'
     * ->setHref('javascript: void(0)');
     * )
     * Values will be received in the 'ids' key of the request as array
     * - call some url via ajax passing filter conditions and then run "callback(json)"
     * CmfRequestMenuItem::bulkActionOnFilteredRows(static::getUrlCustomAction('bulk_action_name'), 'put')
     * // use ':count' in setTitle() to insert selected items count
     * ->setTitle('<span class="label label-primary">:count</span> ' . $this->translate('datagrid.bulk_action.action_name'))
     * ->setTooltip($this->translate('datagrid.bulk_action.action_name_tooltip'))
     * // ask user to confirm action
     * ->setConfirm($this->translate('datagrid.bulk_action.action_name_confirmation'))
     * // one of: json, html, xml. Default: 'json' (may be useful for your custom response handler)
     * ->setResponseDataType('json')
     * // set custom response handler function.
     * // callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * // It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setOnSuccess('callbackFuncitonName');
     * or
     * Tag::li(
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert filtered items count
     * ->setDataAttr('action', 'bulk-filtered')
     * ->setDataAttr('confirm', trans('path.to.translation'))
     * //^ confirm action before sending request to server
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('method', 'put')
     * //^ can be 'post', 'put', 'delete' depending on action type
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setDataAttr('response-type', 'json')
     * //^ one of: json, html, xml. Default: 'json'
     * ->setHref('javascript: void(0)');
     * )
     * - bulk actions with custom on-click handler
     * Tag::li(
     * Tag::button()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert selected items count or filtered items count
     * //^ depending on 'data-type' attribute
     * ->setClass('btn btn-success')
     * ->setDataAttr('type', 'bulk-selected')
     * //^ 'bulk-selected' or 'bulk-filtered'
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('id-field', 'id')
     * //^ id field name to use to get rows ids, default: 'id'
     * ->setOnClick('someFunction(this)')
     * //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * )
     * Conditions will be received in the 'conditions' key of the request as JSON string
     * @return static
     */
    public function setBulkActionsToolbarItems(\Closure $callback)
    {
        $this->bulkActionsToolbarItems = $callback;
        return $this;
    }
    
    /**
     * @param array $records
     * @param array $virtualColumns - list of columns that are provided in TableStructure but marked as not existing in DB
     * @return array
     */
    public function prepareRecords(array $records, array $virtualColumns = []): array
    {
        foreach ($records as &$record) {
            $record = $this->prepareRecord($record, $virtualColumns);
            $resourceName = $this->getScaffoldConfig()->getResourceName();
            if (!empty($record['___details_allowed'])) {
                $record['___details_url'] = $this->getScaffoldConfig()->getUrlToItemDetails($record['___pk_value']);
            }
            if (!empty($record['___edit_allowed'])) {
                $record['___edit_url'] = $this->getScaffoldConfig()->getUrlToItemEditForm($record['___pk_value']);
            }
            if (!empty($record['___delete_allowed'])) {
                $record['___delete_url'] = $this->getScaffoldConfig()->getUrlToItemDelete($record['___pk_value']);
            }
            if (!empty($record['___cloning_allowed'])) {
                $record['___clone_url'] = $this->getScaffoldConfig()->getUrlToItemCloneForm($record['___pk_value']);
            }
            $record['___max_nesting_depth'] = $this->getNestedViewsDepthLimit();
        }
        return $records;
    }
    
    public function createValueViewer(): DataGridColumn
    {
        return DataGridColumn::create();
    }
    
    /**
     * @return Tag[]|string[]
     */
    public function getRowActions(): array
    {
        return $this->rowActions ? (array)call_user_func($this->rowActions, $this) : [];
    }
    
    /**
     * Note: common actions: 'details', 'edit', 'clone', 'delete' will be added automatically before custom menu items.
     * You can manipulate positioning of common items using actions names as keys (ex: 'details' => null).
     * @param \Closure $rowActionsBuilder - function (ScaffolSectionConfig $scaffoldSectionConfig) { return []; }
     * Examples:
     * 1. RowAction
     * a. Preferred usage:
     * - CmfMenuItem::redirect($this->getCmfConfig()->route('route', [], false))
     * ->setTitle($this->translate('action.details'))
     * ->setIconClasses('fa fa-user text-primary')
     * - CmfMenuItem::request($this->getCmfConfig()->route('route', [], false), 'delete')
     * ->setTitle($this->translate('action.delete'))
     * ->setIconClasses('fa fa-trash text-danger')
     * ->setConfirm($this->translate('message.delete_confirm'));
     * b. Alternative usage:
     * - call some url via ajax blocking data grid while waiting for response and then run "callback(json)"
     * Tag::a()
     * ->setContent('<i class="glyphicon glyphicon-screenshot"></i>')
     * ->setClass('row-action text-success')
     * ->setTitle(cmfTransCustom('.path.to.translation'))
     * ->setDataAttr('toggle', 'tooltip')
     * ->setDataAttr('container', '#section-content .content') //< tooltip container
     * ->setDataAttr('block-datagrid', '1')
     * ->setDataAttr('action', 'request')
     * ->setDataAttr('method', 'put')
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('data', 'id={{= it.id }}')
     * //->setDataAttr('url', $this->getCmfConfig()->routeTpl('route', [], ['id'], false))
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setHref('javascript: void(0)')
     * ->build()
     * - redirect
     * Tag::a()
     * ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
     * ->setClass('row-action text-primary')
     * ->setTitle(cmfTransCustom('.path.to.translation'))
     * ->setDataAttr('toggle', 'tooltip')
     * ->setDataAttr('container', '#section-content .content') //< tooltip container
     * ->setHref($this->getCmfConfig()->route('route', [], false))
     * ->setTarget('_blank')
     * ->build()
     * 2. List of row actions:
     *      [
     *          RowAction1,
     *          RowAction2,
     *          'delete' => null
     *      ]
     * @return static
     */
    public function setRowActions(\Closure $rowActionsBuilder)
    {
        $this->rowActions = $rowActionsBuilder;
        return $this;
    }
    
    /**
     * @return static
     */
    public function setIsRowActionsEnabled(bool $isEnabled)
    {
        $this->isRowActionsEnabled = $isEnabled;
        return $this;
    }
    
    public function isRowActionsEnabled(): bool
    {
        return $this->isRowActionsEnabled;
    }
    
    /**
     * Pass 'true' to fix/stick actions column in data grid so it will not move during
     * horisontal scrolling of data grid
     * @return static
     */
    public function setIsRowActionsColumnFixed(bool $isFixed)
    {
        $this->isRowActionsColumnFixed = $isFixed;
        return $this;
    }
    
    public function isRowActionsColumnFixed(): bool
    {
        return $this->isRowActionsColumnFixed;
    }
    
    /**
     * @return static
     */
    public function setIsContextMenuEnabled(bool $isEnabled)
    {
        $this->isContextMenuEnabled = $isEnabled;
        return $this;
    }
    
    public function isContextMenuEnabled(): bool
    {
        return $this->isContextMenuEnabled;
    }
    
    public function getContextMenuItems(): array
    {
        return $this->contextMenuItems ? (array)call_user_func($this->contextMenuItems, $this) : [];
    }
    
    /**
     * Note: common actions: 'details', 'edit', 'clone', 'delete' will be added automatically before custom
     * menu items. You can manipulate positioning of common items using action name as key and null as value
     * (ex: 'details' => null) instead of CmfMenuItem or Tag.
     * @param \Closure $contextMenuItems - function (ScaffolSectionConfig $scaffoldSectionConfig) { return []; }
     * Format:
     * 1. MenuItem
     * - CmfMenuItem::redirect($this->getCmfConfig()->route('route', [], false))
     * ->setTitle($this->translate('action.details'))
     * ->setIconClasses('fa fa-user text-primary')
     * - CmfMenuItem::request($this->getCmfConfig()->route('route', [], false), 'delete')
     * ->setTitle($this->translate('action.delete'))
     * ->setIconClasses('fa fa-trash text-danger')
     * ->setConfirm($this->translate('message.delete_confirm'));
     * - Tag::li(Tag::a()) (For Tag::a() format see setRowActions() docs)
     * 2. List of menu items:
     * - No grouping:
     * [
     * MenuItem1,
     * MenuItem2,
     * 'edit' => null
     * ...
     * ]
     * - With groups:
     * [
     * [
     * MenuItem1,
     * MenuItem2,
     * ...
     * ],
     * [
     * MenuItem3,
     * 'delete' => null
     * ]
     * ]
     * @return static
     */
    public function setContextMenuItems(\Closure $contextMenuItems)
    {
        $this->setIsContextMenuEnabled(true);
        $this->contextMenuItems = $contextMenuItems;
        return $this;
    }
    
    public function getAdditionalDataTablesConfig(): array
    {
        return $this->additionalDataTablesConfig;
    }
    
    /**
     * @return static
     */
    public function setAdditionalDataTablesConfig(array $additionalDataTablesConfig)
    {
        $this->additionalDataTablesConfig = $additionalDataTablesConfig;
        return $this;
    }
    
    /**
     * @param string $name
     * @param null|DataGridColumn|AbstractValueViewer $tableCell
     * @param bool $autodetectIfLinkedToDbColumn
     * @return static
     */
    public function addValueViewer(string $name, AbstractValueViewer &$tableCell = null, bool $autodetectIfLinkedToDbColumn = false)
    {
        $tableCell = !$tableCell && $name === static::ROW_ACTIONS_COLUMN_NAME
            ? $this->getTableCellForForRowActions()
            : $tableCell;
        return parent::addValueViewer($name, $tableCell, $autodetectIfLinkedToDbColumn);
    }
    
    /**
     * @return DataGridColumn|RenderableValueViewer
     */
    protected function getTableCellForForRowActions(): RenderableValueViewer
    {
        return DataGridColumn::create()
            ->setIsLinkedToDbColumn(false)
            ->setName(static::ROW_ACTIONS_COLUMN_NAME)
            ->setLabel($this->translateGeneral('actions.column_label'))
            ->setType(DataGridColumn::TYPE_STRING);
    }
    
    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \UnexpectedValueException
     */
    public function finish(): void
    {
        parent::finish();
        if ($this->isRowActionsEnabled() && !$this->hasValueViewer(static::ROW_ACTIONS_COLUMN_NAME)) {
            $this->addValueViewer(static::ROW_ACTIONS_COLUMN_NAME);
        }
        if ($this->isNestedViewEnabled() && !$this->hasValueViewer($this->getColumnNameForNestedView())) {
            $valueViewer = DataGridColumn::create()->setIsVisible(false);
            $this->addValueViewer($this->getColumnNameForNestedView(), $valueViewer);
        }
        if ($this->isRowsReorderingEnabled()) {
            $reorderingColumns = $this->getRowsPositioningColumns();
            $allowedColumnTypes = [Column::TYPE_INT, Column::TYPE_FLOAT, Column::TYPE_UNIX_TIMESTAMP];
            foreach ($reorderingColumns as $columnName) {
                if (!$this->hasValueViewer($columnName)) {
                    throw new \UnexpectedValueException(
                        "Column '$columnName' provided for reordering was not found within declared data grid columns"
                    );
                }
                $valueViewer = $this->getValueViewer($columnName);
                if (!$valueViewer->isLinkedToDbColumn() && $valueViewer->getTableColumn()->isItExistsInDb()) {
                    throw new \UnexpectedValueException(
                        "Column '$columnName' provided for reordering must be linked to a column that exists in database"
                    );
                }
                $colType = $valueViewer->getTableColumn()->getType();
                if (!in_array($colType, $allowedColumnTypes, true)) {
                    throw new \UnexpectedValueException(
                        "Column '$columnName' provided for reordering should be of a numeric type (int, float, unix ts)."
                        . "'{$colType}' type is not acceptable'"
                    );
                }
                $valueViewer->setIsSortable(true);
            }
        }
    }
    
    protected function getSectionTranslationsPrefix(?string $subtype = null): string
    {
        return $subtype === 'value_viewer' ? 'datagrid.column' : 'datagrid';
    }
    
    /**
     * @param string $parentIdColumnName
     * @param int $limitNestingDepthTo - number of nested data grids. <= 0 - no limit; 1 = 1 subview only;
     * @return static
     */
    public function enableNestedView(string $parentIdColumnName = 'parent_id', int $limitNestingDepthTo = -1)
    {
        $this->getTable()->getTableStructure()->getColumn($parentIdColumnName); //< validates column existence
        $this->enableNestedViewBasedOnColumn = $parentIdColumnName;
        $this->setNestedViewsDepthLimit($limitNestingDepthTo);
        $this->setIsRowActionsColumnFixed(false);
        return $this;
    }
    
    public function isNestedViewEnabled(): bool
    {
        return !empty($this->enableNestedViewBasedOnColumn);
    }
    
    public function getColumnNameForNestedView(): ?string
    {
        return $this->enableNestedViewBasedOnColumn;
    }
    
    /**
     * @return static
     */
    public function setNestedViewsDepthLimit(int $maxDepth)
    {
        $this->nestedViewsDepthLimit = (int)$maxDepth;
        return $this;
    }
    
    public function getNestedViewsDepthLimit(): int
    {
        return $this->nestedViewsDepthLimit;
    }
    
    /**
     * @param array $rowsPositioningColumns - column that is used to define rows order
     * @return static
     */
    public function enableRowsReorderingOn(...$rowsPositioningColumns)
    {
        $this->rowsPositioningColumns = $rowsPositioningColumns;
        return $this;
    }
    
    public function getRowsPositioningColumns(): array
    {
        return $this->rowsPositioningColumns;
    }
    
    public function isRowsReorderingEnabled(): bool
    {
        return !empty($this->rowsPositioningColumns);
    }
    
    /**
     * Provide additional Laravel views to be inserted after data grid.
     * This will solve almost any problem with complex data grid cells that need to be rendered separately.
     * Use this method in couple with $this->setJsInitiator('jsFunctionName') to have full control over data grid
     * initialization and configuration.
     *
     * Each view will be rendered using view($viewPath, $generalData, $customData) calls.
     *
     * $generalData contains:
     *      'idSuffix' => string
     *      'table' => TableInterface
     *      'dataGridConfig' => DataGridConfig
     * $customData may be provided for each view separately via $views argument
     *
     * @param array|\Closure $views -
     *      - array: list of Laravel views in format
     *          [
     *              'folder.view',
     *              'folder.view2' => $customData,
     *              'ns::folder.view',
     *          ]
     *      - \Closure: function (DataGridConfig $dataGridConfig) { reurn [] } returned value must
     *          fit same format as array (see above)
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setAdditionalViewsForTemplate($views)
    {
        if (!is_array($views) && !($views instanceof \Closure)) {
            throw new \InvalidArgumentException('$views argument must be an array or \Closure');
        }
        $this->additionalViewsForTemplate = $views;
        return $this;
    }
    
    public function getAdditionalViewsForTemplate(): array
    {
        return $this->additionalViewsForTemplate instanceof \Closure
            ? call_user_func($this->additionalViewsForTemplate, $this)
            : $this->additionalViewsForTemplate;
    }
    
    public function getRendererHelper(): DataGridRendererHelper
    {
        if (empty($this->rendererHelper)) {
            $this->rendererHelper = new DataGridRendererHelper($this);
        }
        return $this->rendererHelper;
    }
    
    public function prepareRecord(array $record, array $virtualColumns = []): array
    {
        $data = parent::prepareRecord($record, $virtualColumns);
        /** @var DataGridColumn $valueViewer */
        foreach ($this->getValueViewers() as $valueViewer) {
            $relation = $valueViewer->getRelation();
            if ($relation) {
                // add special key for relation column that is not nested into sub object and datatables can find it
                $path = $relation->getName() . '.' . $valueViewer->getRelationColumn();
                $key = $valueViewer::convertNameForDataTables($valueViewer->getName());
                if (!isset($data[$key])) {
                    $data[$key] = Arr::get($data, $path);
                }
            }
        }
        return $data;
    }
}
