<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;
use Swayok\Utils\ValidateValue;

class DataGridConfig extends ScaffoldSectionConfig {

    protected $allowRelationsInValueViewers = true;

    protected $template = 'cmf::scaffold/datagrid';
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
    protected $bulkActionsToolbarItems = null;
    /** @var \Closure */
    protected $rowActions = [];
    /** @var array */
    protected $additionalDataTablesConfig = [];
    /** @var bool */
    protected $isRowActionsFloating = false;
    /** @var bool */
    protected $isRowActionsColumnFixed = true;
    /** @var bool */
    protected $isFilterOpened = true;

    const ROW_ACTIONS_COLUMN_NAME = '__actions';

    public function __construct(TableInterface $table, ScaffoldConfig $scaffoldConfigConfig) {
        parent::__construct($table, $scaffoldConfigConfig);
        $this->recordsPerPage = CmfConfig::getPrimary()->rows_per_page();
        $this->setOrderBy($table->getPkColumnName());
    }

    /**
     * Alias for setValueViewers
     * @param array $formInputs
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     */
    public function setColumns(array $formInputs) {
        return $this->setValueViewers($formInputs);
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
     * @param DataGridCellRenderer|ValueRenderer $renderer
     * @param DataGridColumn|AbstractValueViewer $tableCell
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \UnexpectedValueException
     */
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        AbstractValueViewer $tableCell
    ) {
        switch ($tableCell->getType()) {
            case $tableCell::TYPE_IMAGE:
                $renderer->setTemplate('cmf::details/image');
                break;
        }
    }

    /**
     * @param array $columnNames
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     */
    public function setInvisibleColumns(array $columnNames) {
        foreach ($columnNames as $name) {
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
            /** @var DataGridColumn $otherFieldConfig */
            $count = 0;
            foreach ($this->valueViewers as $otherFieldConfig) {
                if ($otherFieldConfig->isVisible()) {
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
     * @param null $direction
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOrderBy($orderBy, $direction = null) {
        if (!($orderBy instanceof DbExpr) && !$this->getTable()->getTableStructure()->hasColumn($orderBy)) {
            throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column [$orderBy]");
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
        if (!in_array(strtolower($orderDirection), array(self::ORDER_ASC, self::ORDER_DESC), true)) {
            throw new \InvalidArgumentException("Invalid order direction [$orderDirection]. Expected 'asc' or 'desc'");
        }
        $this->orderDirection = strtolower($orderDirection);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAllowedMultiRowSelection() {
        return $this->allowMultiRowSelection;
    }

    /**
     * @param boolean $allowMultiRowSelection
     * @return $this
     */
    public function setMultiRowSelection($allowMultiRowSelection) {
        $this->allowMultiRowSelection = (bool)$allowMultiRowSelection;
        return $this;
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
     * @throws \LogicException
     */
    public function getBulkActionsToolbarItems() {
        if (empty($this->bulkActionsToolbarItems)) {
            return [];
        }
        $bulkActionsToolbarItems = call_user_func($this->bulkActionsToolbarItems, $this);
        if (!is_array($bulkActionsToolbarItems)) {
            throw new \LogicException(get_class($this) . '->bulkActionsToolbarItems closure must return an array');
        }
        /** @var Tag|string $item */
        /** @var array $bulkActionsToolbarItems */
        foreach ($bulkActionsToolbarItems as &$item) {
            if (is_object($item)) {
                if (method_exists($item, 'build')) {
                    $item = $item->build();
                } else if (method_exists($item, '__toString')) {
                    $item = (string) $item;
                } else {
                    throw new \LogicException(
                        get_class($this) . '->bulkActionsToolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } else if (!is_string($item)) {
                throw new \LogicException(
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
            * ->setDataAttr('url', route('route', [], false))
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
            * ->setDataAttr('url', route('route', [], false))
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
            * ->setDataAttr('url', route('route', [], false))
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
     * @return array
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function prepareRecords(array $records) {
        foreach ($records as $idx => &$record) {
            $record = $this->prepareRecord($record);
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
     * @return Tag[]
     */
    public function getRowActions() {
        return is_callable($this->rowActions) ? call_user_func($this->rowActions, $this) : $this->rowActions;
    }

    /**
     * @param \Closure $rowActionsBuilder - function (ScaffolSectionConfig $scaffoldSectionConfig) { return []; }
     * Examples:
     * - call some url via ajax blocking data grid while waiting for response and then run "callback(json)"
        * Tag::a()
            * ->setContent('<i class="glyphicon glyphicon-screenshot"></i>')
            * ->setClass('row-action text-success')
            * ->setTitle(trans('path.to.translation'))
            * ->setDataAttr('toggle', 'tooltip')
            * ->setDataAttr('container', '#section-content .content') //< tooltip container
            * ->setDataAttr('block-datagrid', '1')
            * ->setDataAttr('action', 'request')
            * ->setDataAttr('method', 'put')
            * ->setDataAttr('url', route('route', [], false))
            * ->setDataAttr('data', 'id=:id:')
            * ->setDataAttr('on-success', 'callbackFuncitonName')
            * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            * ->setHref('javascript: void(0)')
     * - redirect
        * Tag::a()
            * ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
            * ->setClass('row-action text-primary')
            * ->setTitle(trans('path.to.translation'))
            * ->setDataAttr('toggle', 'tooltip')
            * ->setDataAttr('container', '#section-content .content') //< tooltip container
            * ->setHref(route('route', [], false))
            * ->setTarget('_blank')
     *
     * @return $this
     * @throws \Swayok\Html\HtmlTagException
     */
    public function setRowActions(\Closure $rowActionsBuilder) {
        $this->rowActions = $rowActionsBuilder;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRowActionsFloating() {
        return $this->isRowActionsFloating;
    }

    /**
     * @return $this
     */
    public function displayRowActionsInActionsColumn() {
        $this->isRowActionsFloating = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function displayRowActionsInFloatingPanel() {
        $this->isRowActionsFloating = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRowActionsColumnFixed() {
        return $this->isRowActionsColumnFixed;
    }

    /**
     * Pass 'true' to fix actions column in data grid so it will not move during horisontal scrolling of data grid
     * @param bool $isFixed
     * @return $this
     */
    public function setIsRowActionsColumnFixed($isFixed) {
        $this->isRowActionsColumnFixed = (bool)$isFixed;
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
            ->setLabel(cmfTransGeneral('.datagrid.actions.column_label'))
            ->setType(DataGridColumn::TYPE_STRING);
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \BadMethodCallException
     */
    public function finish() {
        parent::finish();
        if (!$this->isRowActionsFloating() && !$this->hasValueViewer(static::ROW_ACTIONS_COLUMN_NAME)) {
            $this->addValueViewer(static::ROW_ACTIONS_COLUMN_NAME, null);
        }
    }

}