<?php

namespace PeskyCMF\Scaffold\DataGrid;

use Exceptions\Data\NotFoundException;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\MenuItem\CmfMenuItem;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;
use Swayok\Utils\ValidateValue;

class DataGridConfig extends ScaffoldSectionConfig {

    const ROW_ACTIONS_COLUMN_NAME = '__actions';

    protected $allowRelationsInValueViewers = true;

    protected $allowComplexValueViewerNames = true;

    protected $template = 'cmf::scaffold.datagrid';
    /**
     * @var int
     */
    protected $recordsPerPage = 25;
    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var int
     */
    protected $maxLimit = 100;
    /**
     * @var string
     */
    protected $orderBy = null;
    /**
     * @var string
     */
    protected $orderDirection = self::ORDER_ASC;
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';
    const ORDER_ASC_NULLS_FIRST = 'asc nulls first';
    const ORDER_ASC_NULLS_LAST = 'asc nulls last';
    const ORDER_DESC_NULLS_FIRST = 'desc nulls first';
    const ORDER_DESC_NULLS_LAST = 'desc nulls last';

    static protected $orderOptions = [
        self::ORDER_ASC,
        self::ORDER_DESC,
        self::ORDER_ASC_NULLS_FIRST,
        self::ORDER_ASC_NULLS_LAST,
        self::ORDER_DESC_NULLS_FIRST,
        self::ORDER_DESC_NULLS_LAST,
    ];
    /**
     * Add a checkboxes column to datagrid so user can select several rows and perform bulk-actions
     * @var bool
     */
    protected $allowMultiRowSelection = false;
    /** @var bool */
    protected $allowBulkItemsEditing = false;
    /** @var bool */
    protected $allowBulkItemsDelete = true;
    /** @var bool */
    protected $allowFilteredItemsEditing = false;
    /** @var bool */
    protected $allowFilteredItemsDelete = false;
    /** @var \Closure|null */
    protected $bulkActionsToolbarItems;
    /** @var bool */
    protected $isRowActionsEnabled = true;
    /** @var \Closure|null */
    protected $rowActions;
    /** @var array */
    protected $additionalDataTablesConfig = [];
    /** @var bool */
    protected $isRowActionsColumnFixed = true;
    /** @var bool */
    protected $isFilterOpened = false;
    /** @var string|null */
    protected $enableNestedViewBasedOnColumn;
    /** @var int */
    protected $nestedViewsDepthLimit = -1;
    /** @var array */
    protected $rowsPositioningColumns = [];
    /** @var int|float */
    protected $rowsPosittioningStep = 100;
    /** @var \Closure|null */
    protected $contextMenuItems;
    /** @var bool */
    protected $isContextMenuEnabled = true;
    /** @var bool */
    protected $isMultiRowSelectionColumnFixed = true;
    /** @var array */
    protected $additionalViewsForTemplate = [];
    /** @var DataGridRendererHelper|null */
    protected $rendererHelper;

    public function __construct(TableInterface $table, ScaffoldConfig $scaffoldConfig) {
        parent::__construct($table, $scaffoldConfig);
        $this->recordsPerPage = $scaffoldConfig::getCmfConfig()->rows_per_page();
        $this->setOrderBy($table->getPkColumnName());
    }

    /**
     * Alias for setValueViewers
     * @param array $datagridColumns
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     */
    public function setColumns(array $datagridColumns) {
        return $this->setValueViewers($datagridColumns);
    }

    /**
     * @return DataGridColumn[]|AbstractValueViewer[]
     */
    public function getDataGridColumns() {
        return $this->getValueViewers();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasDataGridColumn($name) {
        return $this->hasValueViewer($name);
    }

    protected function createValueRenderer() {
        return DataGridCellRenderer::create();
    }

    /**
     * @param array $columnNames
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     */
    public function setInvisibleColumns(...$columnNames) {
        return call_user_func_array(array($this, 'setAdditionalColumnsToSelect'), $columnNames);
    }

    /**
     * Mimics setInvisibleColumns()
     * @param array $columnNames
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setAdditionalColumnsToSelect(...$columnNames) {
        parent::setAdditionalColumnsToSelect($columnNames);
        foreach ($this->getAdditionalColumnsToSelect() as $name) {
            $this->addValueViewer($name, DataGridColumn::create()->setIsVisible(false));
        }
        return $this;
    }

    /**
     * @param AbstractValueViewer|DataGridColumn $viewer
     * @return int
     */
    protected function getNextValueViewerPosition(AbstractValueViewer $viewer) {
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
     * @return $this
     */
    public function setFilterIsOpenedByDefault($shown = true) {
        $this->isFilterOpened = $shown;
        return $this;
    }

    /**
     * @return $this
     */
    public function openFilterByDefault() {
        $this->isFilterOpened = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function closeFilterByDefault() {
        $this->isFilterOpened = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFilterOpenedByDefault() {
        return $this->isFilterOpened;
    }

    /**
     * @return int
     */
    public function getRecordsPerPage() {
        return $this->recordsPerPage;
    }

    /**
     * @param int $recordsPerPage
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRecordsPerPage($recordsPerPage) {
        if (!ValidateValue::isInteger($recordsPerPage, true)) {
            throw new \InvalidArgumentException('$recordsPerPage argument must be an integer value');
        }
        $this->recordsPerPage = min($this->maxLimit, $recordsPerPage);
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOffset($offset) {
        if (!ValidateValue::isInteger($offset, true)) {
            throw new \InvalidArgumentException('$offset argument must be an integer value');
        }
        $this->offset = max($offset, 0);
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderBy() {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     * @param null $direction - 'asc', 'desc', 'asc nulls first' or 'desc nulls last' in any case
     * @return $this
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function setOrderBy($orderBy, $direction = null) {
        if (!($orderBy instanceof DbExpr) && !$this->getTable()->getTableStructure()->hasColumn($orderBy)) {
            throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column [$orderBy]");
        }
        if ($orderBy instanceof DbExpr) {
            $orderBy->setWrapInBrackets(false);
            if (empty($direction)) {
                $this->orderDirection = null;
            }
        }
        if (!empty($direction)) {
            $this->setOrderDirection($direction);
        }
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderDirection() {
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOrderDirection($orderDirection) {
        $orderDirection = strtolower($orderDirection);
        if (!in_array($orderDirection, static::$orderOptions, true)) {
            throw new \InvalidArgumentException(
                "Invalid order direction [$orderDirection]. Expected one of: " . implode(', ', static::$orderOptions)
            );
        }
        $this->orderDirection = $orderDirection;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowedMultiRowSelection() {
        return $this->allowMultiRowSelection;
    }

    /**
     * @param bool $allowMultiRowSelection
     * @return $this
     */
    public function setMultiRowSelection($allowMultiRowSelection) {
        $this->allowMultiRowSelection = (bool)$allowMultiRowSelection;
        return $this;
    }

    /**
     * Pass 'true' to fix/stick multi-row selection column in data grid so it will not move during
     * horisontal scrolling of data grid
     * @param bool $isFixed
     * @return $this
     */
    public function setIsMultiRowSelectionColumnFixed($isFixed) {
        $this->isMultiRowSelectionColumnFixed = (bool)$isFixed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiRowSelectionColumnFixed() {
        return $this->isMultiRowSelectionColumnFixed;
    }

    /**
     * @param bool $isAllowed - default: true
     * @return $this
     */
    public function setIsBulkItemsDeleteAllowed($isAllowed) {
        $this->allowBulkItemsDelete = (bool)$isAllowed;
        $this->setMultiRowSelection(true);
        return $this;
    }

    /**
     * @return bool
     */
    public function isBulkItemsDeleteAllowed() {
        return $this->allowBulkItemsDelete;
    }

    /**
     * Bulk editable columns provided via FormConfig->setBulkEditableColumns() or FormConfig->addBulkEditableColumns()
     * @param bool $isAllowed - default: false
     * @return $this
     */
    public function setIsBulkItemsEditingAllowed($isAllowed) {
        $this->allowBulkItemsEditing = (bool)$isAllowed;
        $this->setMultiRowSelection(true);
        return $this;
    }

     /**
     * @return bool
     */
    public function isBulkItemsEditingAllowed() {
        return $this->allowBulkItemsEditing;
    }

    /**
     * @param bool $isAllowed - default: false
     * @return $this
     */
    public function setIsFilteredItemsDeleteAllowed($isAllowed) {
        $this->allowFilteredItemsDelete = (bool)$isAllowed;
        return $this;
    }

     /**
     * @return bool
     */
    public function isFilteredItemsDeleteAllowed() {
        return $this->allowFilteredItemsDelete;
    }

    /**
     * @param bool $isAllowed - default: false
     * @return $this
     */
    public function setIsFilteredItemsEditingAllowed($isAllowed) {
        $this->allowFilteredItemsEditing = (bool)$isAllowed;
        return $this;
    }

     /**
     * @return bool
     */
    public function isFilteredItemsEditingAllowed() {
        return $this->allowFilteredItemsEditing;
    }

    /**
     * @return string[]
     * @throws \UnexpectedValueException
     */
    public function getBulkActionsToolbarItems() {
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
                if ($item instanceof CmfMenuItem) {
                    // do nothing
                } else if (method_exists($item, 'build')) {
                    $item = $item->build();
                } else if (method_exists($item, '__toString')) {
                    $item = (string) $item;
                } else {
                    throw new \UnexpectedValueException(
                        get_class($this) . '->bulkActionsToolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } else if (!is_string($item)) {
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
        * Tag::a()
            * ->setContent(trans('path.to.translation'))
            * //^ you can use ':count' in label to insert selected items count
            * ->setDataAttr('action', 'bulk-selected')
            * ->setDataAttr('confirm', trans('path.to.translation'))
            * //^ confirm action before sending request to server
            * ->setDataAttr('url', cmfRoute('route', [], false))
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
     * Values will be received in the 'ids' key of the request as array
     * - call some url via ajax passing filter conditions and then run "callback(json)"
        * Tag::a()
            * ->setContent(trans('path.to.translation'))
            * //^ you can use ':count' in label to insert filtered items count
            * ->setDataAttr('action', 'bulk-filtered')
            * ->setDataAttr('confirm', trans('path.to.translation'))
            * //^ confirm action before sending request to server
            * ->setDataAttr('url', cmfRoute('route', [], false))
            * ->setDataAttr('method', 'put')
            * //^ can be 'post', 'put', 'delete' depending on action type
            * ->setDataAttr('on-success', 'callbackFuncitonName')
            * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            * ->setDataAttr('response-type', 'json')
            * //^ one of: json, html, xml. Default: 'json'
            * ->setHref('javascript: void(0)');
     * - bulk actions with custom on-click handler
        * Tag::button()
            * ->setContent(trans('path.to.translation'))
            * //^ you can use ':count' in label to insert selected items count or filtered items count
            * //^ depending on 'data-type' attribute
            * ->setClass('btn btn-success')
            * ->setDataAttr('type', 'bulk-selected')
            * //^ 'bulk-selected' or 'bulk-filtered'
            * ->setDataAttr('url', cmfRoute('route', [], false))
            * ->setDataAttr('id-field', 'id')
            * //^ id field name to use to get rows ids, default: 'id'
            * ->setOnClick('someFunction(this)')
            * //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * Conditions will be received in the 'conditions' key of the request as JSON string
     * @return $this
     */
    public function setBulkActionsToolbarItems(\Closure $callback) {
        $this->bulkActionsToolbarItems = $callback;
        return $this;
    }

    /**
     * @param array $records
     * @param array $virtualColumns - list of columns that are provided in TableStructure but marked as not existing in DB
     * @return array
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function prepareRecords(array $records, array $virtualColumns = []) {
        foreach ($records as $idx => &$record) {
            $record = $this->prepareRecord($record, $virtualColumns);
            $resourceName = $this->getScaffoldConfig()->getResourceName();
            if (array_get($record, '___details_allowed', false)) {
                $record['___details_url'] = routeToCmfItemDetails($resourceName, $record['___pk_value']);
            }
            if (array_get($record, '___edit_allowed', false)) {
                $record['___edit_url'] = routeToCmfItemEditForm($resourceName, $record['___pk_value']);
            }
            if (array_get($record, '___delete_allowed', false)) {
                $record['___delete_url'] = routeToCmfItemDelete($resourceName, $record['___pk_value']);
            }
            if (array_get($record, '___cloning_allowed', false)) {
                $record['___clone_url'] = routeToCmfItemCloneForm($resourceName, $record['___pk_value']);
            }
            $record['___max_nesting_depth'] = $this->getNestedViewsDepthLimit();
        }
        return $records;
    }

    /**
     * @inheritdoc
     */
    public function createValueViewer() {
        return DataGridColumn::create();
    }

    /**
     * @return Tag[]|string[]
     */
    public function getRowActions() {
        return $this->rowActions ? (array)call_user_func($this->rowActions, $this) : [];
    }

    /**
     * Note: common actions: 'details', 'edit', 'clone', 'delete' will be added automatically before custom menu items.
     * You can manipulate positioning of common items using actions names as keys (ex: 'details' => null).
     * @param \Closure $rowActionsBuilder - function (ScaffolSectionConfig $scaffoldSectionConfig) { return []; }
     * Examples:
     * 1. RowAction
     * a. Preferred usage:
     * - CmfMenuItem::redirect(cmfRoute('route', [], false))
            ->setTitle($this->translate('action.details'))
            ->setIconClasses('fa fa-user text-primary')
     * - CmfMenuItem::request(cmfRoute('route', [], false), 'delete')
            ->setTitle($this->translate('action.delete'))
            ->setIconClasses('fa fa-trash text-danger')
            ->setConfirm($this->translate('message.delete_confirm'));
     * b. Alternative usage:
     * - call some url via ajax blocking data grid while waiting for response and then run "callback(json)"
        * Tag::a()
            ->setContent('<i class="glyphicon glyphicon-screenshot"></i>')
            ->setClass('row-action text-success')
            ->setTitle(cmfTransCustom('.path.to.translation'))
            ->setDataAttr('toggle', 'tooltip')
            ->setDataAttr('container', '#section-content .content') //< tooltip container
            ->setDataAttr('block-datagrid', '1')
            ->setDataAttr('action', 'request')
            ->setDataAttr('method', 'put')
            ->setDataAttr('url', cmfRoute('route', [], false))
            ->setDataAttr('data', 'id={{= it.id }}')
            //->setDataAttr('url', cmfRouteTpl('route', [], ['id'], false))
            ->setDataAttr('on-success', 'callbackFuncitonName')
            //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            ->setHref('javascript: void(0)')
            ->build()
     * - redirect
        * Tag::a()
            ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
            ->setClass('row-action text-primary')
            ->setTitle(cmfTransCustom('.path.to.translation'))
            ->setDataAttr('toggle', 'tooltip')
            ->setDataAttr('container', '#section-content .content') //< tooltip container
            ->setHref(cmfRoute('route', [], false))
            ->setTarget('_blank')
            ->build()
     * 2. List of row actions:
     *      [
     *          RowAction1,
     *          RowAction2,
     *          'delete' => null
     *      ]
     * @return $this
     */
    public function setRowActions(\Closure $rowActionsBuilder) {
        $this->rowActions = $rowActionsBuilder;
        return $this;
    }

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsRowActionsEnabled($isEnabled) {
        $this->isRowActionsEnabled = (bool)$isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRowActionsEnabled() {
        return $this->isRowActionsEnabled;
    }

    /**
     * @return bool
     */
    public function isRowActionsColumnFixed() {
        return $this->isRowActionsColumnFixed;
    }

    /**
     * Pass 'true' to fix/stick actions column in data grid so it will not move during
     * horisontal scrolling of data grid
     * @param bool $isFixed
     * @return $this
     */
    public function setIsRowActionsColumnFixed($isFixed) {
        $this->isRowActionsColumnFixed = (bool)$isFixed;
        return $this;
    }

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsContextMenuEnabled($isEnabled) {
        $this->isContextMenuEnabled = (bool)$isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isContextMenuEnabled() {
        return $this->isContextMenuEnabled;
    }

    /**
     * @return array
     */
    public function getContextMenuItems() {
        return $this->contextMenuItems ? (array)call_user_func($this->contextMenuItems, $this) : [];
    }

    /**
     * Note: common actions: 'details', 'edit', 'clone', 'delete' will be added automatically before custom
     * menu items. You can manipulate positioning of common items using action name as key and null as value
     * (ex: 'details' => null) instead of CmfMenuItem or Tag.
     * @param \Closure $contextMenuItems - function (ScaffolSectionConfig $scaffoldSectionConfig) { return []; }
     * Format:
     * 1. MenuItem
     * - CmfMenuItem::redirect(cmfRoute('route', [], false))
            ->setTitle($this->translate('action.details'))
            ->setIconClasses('fa fa-user text-primary')
     * - CmfMenuItem::request(cmfRoute('route', [], false), 'delete')
            ->setTitle($this->translate('action.delete'))
            ->setIconClasses('fa fa-trash text-danger')
            ->setConfirm($this->translate('message.delete_confirm'));
     * - Tag::li(Tag::a()) (For Tag::a() format see setRowActions() docs)
     * 2. List of menu items:
     * - No grouping:
            [
                MenuItem1,
                MenuItem2,
                'edit' => null
                ...
            ]
     * - With groups:
            [
                [
                    MenuItem1,
                    MenuItem2,
                    ...
                ],
                [
                    MenuItem3,
                    'delete' => null
                ]
            ]
     * @return $this
     */
    public function setContextMenuItems(\Closure $contextMenuItems) {
        $this->setIsContextMenuEnabled(true);
        $this->contextMenuItems = $contextMenuItems;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalDataTablesConfig() {
        return $this->additionalDataTablesConfig;
    }

    /**
     * @param array $additionalDataTablesConfig
     * @return $this
     */
    public function setAdditionalDataTablesConfig(array $additionalDataTablesConfig) {
        $this->additionalDataTablesConfig = $additionalDataTablesConfig;
        return $this;
    }

    /**
     * @param string $name
     * @param null|DataGridColumn|AbstractValueViewer $tableCell
     * @return ScaffoldSectionConfig
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     */
    public function addValueViewer($name, AbstractValueViewer $tableCell = null) {
        $tableCell = !$tableCell && $name === static::ROW_ACTIONS_COLUMN_NAME
            ? $this->getTableCellForForRowActions()
            : $tableCell;
        return parent::addValueViewer($name, $tableCell);
    }

    /**
     * @return DataGridColumn
     */
    protected function getTableCellForForRowActions() {
        return DataGridColumn::create()
            ->setIsLinkedToDbColumn(false)
            ->setName(static::ROW_ACTIONS_COLUMN_NAME)
            ->setLabel($this->translateGeneral('actions.column_label'))
            ->setType(DataGridColumn::TYPE_STRING);
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \Exceptions\Data\NotFoundException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function finish() {
        parent::finish();
        if ($this->isRowActionsEnabled() && !$this->hasValueViewer(static::ROW_ACTIONS_COLUMN_NAME)) {
            $this->addValueViewer(static::ROW_ACTIONS_COLUMN_NAME, null);
        }
        if ($this->isNestedViewEnabled() && !$this->hasValueViewer($this->getColumnNameForNestedView())) {
            $this->addValueViewer($this->getColumnNameForNestedView(), DataGridColumn::create()->setIsVisible(false));
        }
        if ($this->isRowsReorderingEnabled()) {
            $reorderingColumns = $this->getRowsPositioningColumns();
            $allowedColumnTypes = [Column::TYPE_INT, Column::TYPE_FLOAT, Column::TYPE_UNIX_TIMESTAMP];
            foreach ($reorderingColumns as $columnName) {
                if (!$this->hasValueViewer($columnName)) {
                    throw new NotFoundException(
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

    protected function getSectionTranslationsPrefix($subtype = null) {
        return $subtype === 'value_viewer' ? 'datagrid.column' : 'datagrid';
    }

    /**
     * @param string $parentIdColumnName
     * @param int $limitNestingDepthTo - number of nested data grids. <= 0 - no limit; 1 = 1 subview only;
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function enableNestedView($parentIdColumnName = 'parent_id', $limitNestingDepthTo = -1) {
        $this->getTable()->getTableStructure()->getColumn($parentIdColumnName); //< validates column existence
        $this->enableNestedViewBasedOnColumn = $parentIdColumnName;
        $this->setNestedViewsDepthLimit($limitNestingDepthTo);
        $this->setIsRowActionsColumnFixed(false);
        return $this;
    }

    /**
     * @return bool
     */
    public function isNestedViewEnabled() {
        return !empty($this->enableNestedViewBasedOnColumn);
    }

    /**
     * @return null|string
     */
    public function getColumnNameForNestedView() {
        return $this->enableNestedViewBasedOnColumn;
    }

    /**
     * @param int $maxDepth
     * @return $this
     */
    public function setNestedViewsDepthLimit($maxDepth) {
        $this->nestedViewsDepthLimit = (int)$maxDepth;
        return $this;
    }

    /**
     * @return int
     */
    public function getNestedViewsDepthLimit() {
        return $this->nestedViewsDepthLimit;
    }

    /**
     * @param array $rowsPositioningColumns - column that is used to define rows order
     * @return $this
     */
    public function enableRowsReorderingOn(...$rowsPositioningColumns) {
        $this->rowsPositioningColumns = $rowsPositioningColumns;
        return $this;
    }

    /**
     * @return array
     */
    public function getRowsPositioningColumns() {
        return $this->rowsPositioningColumns;
    }

    /**
     * @return bool
     */
    public function isRowsReorderingEnabled() {
        return !empty($this->rowsPositioningColumns);
    }

    /**
     * @return array
     */
    public function getAdditionalViewsForTemplate() {
        return $this->additionalViewsForTemplate instanceof \Closure
            ? call_user_func($this->additionalViewsForTemplate, $this)
            : $this->additionalViewsForTemplate;
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAdditionalViewsForTemplate($views) {
        if (!is_array($views) && !($views instanceof \Closure)) {
            throw new \InvalidArgumentException('$views argument must be an array or \Closure');
        }
        $this->additionalViewsForTemplate = $views;
        return $this;
    }

    /**
     * @return DataGridRendererHelper
     */
    public function getRendererHelper() {
        if (empty($this->rendererHelper)) {
            $this->rendererHelper = new DataGridRendererHelper($this);
        }
        return $this->rendererHelper;
    }

    /**
     * @param array $record
     * @param array $virtualColumns
     * @return array
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\OrmException
     */
    public function prepareRecord(array $record, array $virtualColumns = []) {
        $data = parent::prepareRecord($record, $virtualColumns);
        /** @var DataGridColumn $valueViewer */
        foreach ($this->getValueViewers() as $valueViewer) {
            if ($valueViewer->hasRelation()) {
                // add special key for relation column that is not nested into sub object and datatables can find it
                $path = $valueViewer->getRelation()->getName() . '.' . $valueViewer->getRelationColumn();
                $data[$valueViewer::convertNameForDataTables($valueViewer->getName())] = array_get($data, $path);
            }
        }
        return $data;
    }
}