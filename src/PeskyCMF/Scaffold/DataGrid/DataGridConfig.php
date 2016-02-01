<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
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
    /** @var Tag[]|callable */
    protected $rowActions = [];

    public function __construct(CmfDbModel $model, ScaffoldSectionConfig $scaffoldSectionConfig) {
        parent::__construct($model, $scaffoldSectionConfig);
        $this->limit = CmfConfig::getInstance()->rows_per_page();
        if ($model->getOrderField()) {
            $this->setOrderBy($model->getOrderField(), $model->getOrderDirection());
        }
    }

    /**
     * @param array $fieldNames
     * @return $this
     */
    public function setInvisibleFields(array $fieldNames) {
        foreach ($fieldNames as $fieldName) {
            $this->addField($fieldName, DataGridFieldConfig::create()->setIsVisible(false));
        }
        return $this;
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
     * @throws ScaffoldActionException
     */
    public function setOrderBy($orderBy, $direction = null) {
        if (!$this->model->hasTableColumn($orderBy)) {
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
        if (!in_array(strtolower($orderDirection), array(self::ORDER_ASC, self::ORDER_DESC))) {
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
        $this->allowMultiRowSelection = !!$allowMultiRowSelection;
        return $this;
    }

    /**
     * @param array $records
     * @return array
     */
    public function prepareRecords(array &$records) {
        foreach ($records as &$record) {
            $this->prepareRecord($record);
        }
    }

    /**
     * @inheritdoc
     */
    static public function createFieldConfig($fieldName) {
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
            ->setDataAttr('block-datagrid', '1')
            ->setDataAttr('action', 'request')
            ->setDataAttr('method', 'put')
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('data', 'id=:id:')
            ->setDataAttr('on-success', 'callback(json);')
            ->setHref('#')
     * - redirect
        Tag::a()
            ->setContent('<i class="glyphicon glyphicon-log-in"></i>')
            ->setClass('row-action text-primary')
            ->setTitle(trans('path.to.translation'))
            ->setDataAttr('toggle', 'tooltip')
            ->setHref(route('route', [], false))
            ->setTarget('_blank')
     *
     * @return $this
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


}