<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
use PeskyCMF\Scaffold\ScaffoldFieldConfig;
use PeskyCMF\Scaffold\ScaffoldFieldRendererConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyORM\DbExpr;
use Swayok\Html\Tag;
use Swayok\Utils\ValidateValue;

class DataGridConfig extends ScaffoldActionConfig {

    protected $view = 'cmf::scaffold/datagrid';
    /**
     * @var int
     */
    protected $limit = 25;
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
    /** @var callable|null */
    protected $bulkActionsToolbarItems = null;
    /** @var Tag[]|callable */
    protected $rowActions = [];
    /** @var array */
    protected $additionalDataTablesConfig = [];
    /** @var bool */
    protected $isRowActionsFloating = true;
    /** @var bool */
    protected $isFilterShown = true;

    const ROW_ACTIONS_COLUMN_NAME = '__actions';

    public function __construct(CmfDbModel $model, ScaffoldSectionConfig $scaffoldSectionConfig) {
        parent::__construct($model, $scaffoldSectionConfig);
        $this->limit = CmfConfig::getInstance()->rows_per_page();
        if ($model->getOrderField()) {
            $this->setOrderBy($model->getOrderField(), $model->getOrderDirection());
        }
    }

    protected function createFieldRendererConfig() {
        return CellRendererConfig::create();
    }

    /**
     * @param CellRendererConfig|ScaffoldFieldRendererConfig $rendererConfig
     * @param DataGridFieldConfig|ScaffoldFieldConfig $fieldConfig
     */
    protected function configureDefaultRenderer(
        ScaffoldFieldRendererConfig $rendererConfig,
        ScaffoldFieldConfig $fieldConfig
    ) {
        switch ($fieldConfig->getType()) {
            case $fieldConfig::TYPE_IMAGE:
                $rendererConfig->setView('cmf::details/image');
                break;
        }
    }

    /**
     * @param array $fieldNames
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function setInvisibleFields(array $fieldNames) {
        foreach ($fieldNames as $fieldName) {
            $this->addField($fieldName, DataGridFieldConfig::create()->setIsVisible(false));
        }
        return $this;
    }

    /**
     * @param ScaffoldFieldConfig|DataGridFieldConfig $fieldConfig
     * @return int
     */
    protected function getNextFieldPosition(ScaffoldFieldConfig $fieldConfig) {
        if ($fieldConfig->isVisible()) {
            /** @var DataGridFieldConfig $otherFieldConfig */
            $count = 0;
            foreach ($this->fields as $otherFieldConfig) {
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
    public function setFilterIsShownByDefault($shown = true) {
        $this->isFilterShown = $shown;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFilterShownByDefault() {
        return $this->isFilterShown;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setLimit($limit) {
        if (!ValidateValue::isInteger($limit, true)) {
            throw new ScaffoldActionException($this, 'Integer value expected');
        }
        $this->limit = min($this->maxLimit, $limit);
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
     * @throws ScaffoldActionException
     */
    public function setOffset($offset) {
        if (!ValidateValue::isInteger($offset, true)) {
            throw new ScaffoldActionException($this, 'Integer value expected');
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
     * @throws \PeskyORM\Exception\DbModelException
     * @throws ScaffoldActionException
     */
    public function setOrderBy($orderBy, $direction = null) {
        if (!($orderBy instanceof DbExpr) && !$this->model->hasTableColumn($orderBy)) {
            throw new ScaffoldActionException($this, "Unknown column [$orderBy]");
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
     * @throws ScaffoldActionException
     */
    public function setOrderDirection($orderDirection) {
        if (!in_array(strtolower($orderDirection), array(self::ORDER_ASC, self::ORDER_DESC), true)) {
            throw new ScaffoldActionException($this, "Invalid order direction [$orderDirection]. Expected 'asc' or 'desc'");
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
     * @param \Closure $callback - function (ScaffoldActionConfig $scaffoldAction) { return []; }
     * Callback must return an array.
     * Array may contain only strings, Tag class instances, or any object with build() or __toString() method
     * Examples:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
        Tag::a()
            ->setContent(trans('path.to.translation'))
            //^ you can use ':count' in label to insert selected items count
            ->setDataAttr('action', 'bulk-selected')
            ->setDataAttr('confirm', trans('path.to.translation'))
            //^ confirm action before sending request to server
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('method', 'delete')
            //^ can be 'post', 'put', 'delete' depending on action type
            ->setDataAttr('id-field', 'id')
            //^ id field name to use to get rows ids, default: 'id'
            ->setDataAttr('on-success', 'callbackFuncitonName')
            //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            ->setDataAttr('response-type', 'json')
            //^ one of: json, html, xml. Default: 'json'
            ->setHref('javascript: void(0)');
     * Values will be received in the 'ids' key of the request as array
     * - call some url via ajax passing filter conditions and then run "callback(json)"
        Tag::a()
            ->setContent(trans('path.to.translation'))
            //^ you can use ':count' in label to insert filtered items count
            ->setDataAttr('action', 'bulk-filtered')
            ->setDataAttr('confirm', trans('path.to.translation'))
            //^ confirm action before sending request to server
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('method', 'put')
            //^ can be 'post', 'put', 'delete' depending on action type
            ->setDataAttr('on-success', 'callbackFuncitonName')
            //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            ->setDataAttr('response-type', 'json')
            //^ one of: json, html, xml. Default: 'json'
            ->setHref('javascript: void(0)');
     * - bulk actions with custom on-click handler
        Tag::button()
            ->setContent(trans('path.to.translation'))
            //^ you can use ':count' in label to insert selected items count or filtered items count
            //^ depending on 'data-type' attribute
            ->setClass('btn btn-success')
            ->setDataAttr('type', 'bulk-selected')
            //^ 'bulk-selected' or 'bulk-filtered'
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('id-field', 'id')
            //^ id field name to use to get rows ids, default: 'id'
            ->setOnClick('someFunction(this)')
            //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * Conditions will be received in the 'conditions' key of the request as JSON string
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setBulkActionsToolbarItems(\Closure $callback) {
        $this->bulkActionsToolbarItems = $callback;
        return $this;
    }

    /**
     * @param array $records
     * @return array
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyORM\Exception\DbColumnConfigException
     * @throws \PeskyORM\Exception\DbTableConfigException
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
    public function createFieldConfig() {
        return DataGridFieldConfig::create();
    }

    /**
     * @return Tag[]
     */
    public function getRowActions() {
        return is_callable($this->rowActions) ? call_user_func($this->rowActions, $this) : $this->rowActions;
    }

    /**
     * @param Tag[]|callable $arrayOrCallable - callable: function (ScaffolActionConfig $scaffoldAction) { return []; }
     * Examples:
     * - call some url via ajax blocking data grid while waiting for response and then run "callback(json)"
        Tag::a()
            ->setContent('<i class="glyphicon glyphicon-screenshot"></i>')
            ->setClass('row-action text-success')
            ->setTitle(trans('path.to.translation'))
            ->setDataAttr('toggle', 'tooltip')
            ->setDataAttr('container', '#section-content .content') //< tooltip container
            ->setDataAttr('block-datagrid', '1')
            ->setDataAttr('action', 'request')
            ->setDataAttr('method', 'put')
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('data', 'id=:id:')
            ->setDataAttr('on-success', 'callbackFuncitonName')
            //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            ->setHref('javascript: void(0)')
     * - redirect
        Tag::a()
            ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
            ->setClass('row-action text-primary')
            ->setTitle(trans('path.to.translation'))
            ->setDataAttr('toggle', 'tooltip')
            ->setDataAttr('container', '#section-content .content') //< tooltip container
            ->setHref(route('route', [], false))
            ->setTarget('_blank')
     *
     * @return $this
     * @throws \Swayok\Html\HtmlTagException
     * @throws ScaffoldActionException
     */
    public function setRowActions($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !is_callable($arrayOrCallable)) {
            throw new ScaffoldActionException($this, 'setRowActions($arrayOrCallable) accepts only array or callable');
        }
        if (!is_callable($arrayOrCallable)) {
            foreach ($arrayOrCallable as &$rowAction) {
                if (is_object($rowAction)) {
                    if (method_exists($rowAction, 'build')) {
                        $rowAction = $rowAction->build();
                    } else if (method_exists($rowAction, '__toString')) {
                        $rowAction = $rowAction->__toString();
                    } else {
                        throw new ScaffoldActionException($this, 'Row action is an object without possibility to convert it to string');
                    }
                }
            }
        }
        $this->rowActions = $arrayOrCallable;
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
     * @param null|DataGridFieldConfig $config
     * @return ScaffoldActionConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \PeskyORM\Exception\DbModelException
     */
    public function addField($name, $config = null) {
        $config = !$config && $name === static::ROW_ACTIONS_COLUMN_NAME
            ? $this->getDataGridFieldConfigForRowActions()
            : $config;
        return parent::addField($name, $config);
    }

    /**
     * @return DataGridFieldConfig
     */
    protected function getDataGridFieldConfigForRowActions() {
        return DataGridFieldConfig::create()
            ->setIsDbField(false)
            ->setName(static::ROW_ACTIONS_COLUMN_NAME)
            ->setLabel(CmfConfig::transBase('.datagrid.actions.column_label'))
            ->setType(DataGridFieldConfig::TYPE_STRING);
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \PeskyORM\Exception\DbModelException
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function finish() {
        if (!$this->isRowActionsFloating()) {
            $fields = $this->getFields();
            if (!isset($fields[static::ROW_ACTIONS_COLUMN_NAME])) {
                $this->addField(static::ROW_ACTIONS_COLUMN_NAME, null);
            }
        }
    }

}