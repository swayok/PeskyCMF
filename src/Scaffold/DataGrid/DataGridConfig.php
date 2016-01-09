<?php

namespace PeskyCMF\Scaffold\DataGrid;

use App\Db\BaseDbModel;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
use Swayok\Html\Tag;
use Swayok\Utils\ValidateValue;

class DataGridConfig extends ScaffoldActionConfig {

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
    /** @var Tag[] */
    protected $rowActions = [];

    /**
     * ScaffoldActionConfig constructor.
     * @param BaseDbModel $model
     */
    public function __construct(BaseDbModel $model) {
        parent::__construct($model);
        $this->limit = CmfConfig::getInstance()->rows_per_page();
        if ($model->getOrderField()) {
            $this->setOrderBy($model->getOrderField(), $model->getOrderDirection());
        }
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
    public function createFieldConfig($fieldName) {
        $columnConfig = $this->getModel()->getTableColumn($fieldName);
        $config = DataGridFieldConfig::create()
            ->setType($columnConfig->getType());
        return $config;
    }

    /**
     * @return Tag[]
     */
    public function getRowActions() {
        return $this->rowActions;
    }

    /**
     * @param Tag[] $rowActions
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setRowActions(array $rowActions) {
        foreach ($rowActions as &$rowAction) {
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
        $this->rowActions = $rowActions;
        return $this;
    }


}