<?php


namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Db\CmfDbTable;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;

class DataGridFilterConfig {
    /** @var CmfDbTable */
    protected $model;
    /** @var array|DataGridColumnFilterConfig[] */
    protected $filters = [];
    protected $defaultConditions = ['condition' => 'AND', 'rules' => []];
    
    static public $columnTypeToFilterType = [
        Column::TYPE_INT => DataGridColumnFilterConfig::TYPE_INTEGER,
        Column::TYPE_FLOAT => DataGridColumnFilterConfig::TYPE_FLOAT,
        Column::TYPE_BOOL => DataGridColumnFilterConfig::TYPE_BOOL,
        // it is rarely needed to use date-time filter, so it is better to use date filter instead
        Column::TYPE_TIMESTAMP => DataGridColumnFilterConfig::TYPE_DATE,
        Column::TYPE_DATE => DataGridColumnFilterConfig::TYPE_DATE,
        Column::TYPE_TIME => DataGridColumnFilterConfig::TYPE_TIME,
    ];
    protected $defaultDataGridColumnFilterConfigClass = DataGridColumnFilterConfig::class;

    /**
     * @param CmfDbTable $model
     * @return $this
     */
    static public function create(CmfDbTable $model) {
        $class = get_called_class();
        return new $class($model);
    }

    /**
     * ScaffoldActionConfig constructor.
     * @param CmfDbTable $model
     */
    public function __construct(CmfDbTable $model) {
        $this->model = $model;
    }

    /**
     * @param DataGridColumnFilterConfig[] $filters
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function setFilters(array $filters) {
        $this->filters = [];
        /** @var DataGridColumnFilterConfig $config */
        foreach ($filters as $columnName => $config) {
            if (is_int($columnName)) {
                $columnName = $config;
                $config = null;
            }
            $this->addFilter($columnName, $config);
        }
        return $this;
    }

    /**
     * @param string $columnName - 'col_name' or 'RelationAlias.col_name'
     * @param null|DataGridColumnFilterConfig $config
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function addFilter($columnName, $config = null) {
        $this->findColumnConfig($columnName); //< needed to validate column existense
        if (empty($config)) {
            $config = $this->createColumnFilterConfig($columnName);
        } else if (!($config instanceof DataGridColumnFilterConfig)) {
            throw new ScaffoldException("Filter column config for column [$columnName] should an object of class [DataGridColumnFilterConfig]");
        }
        if (!$config->hasColumnName()) {
            $config->setColumnName($this->getColumnNameWithAlias($columnName));
        }
        $this->filters[$config->getColumnName()] = $config;
        return $this;
    }

    /**
     * @return DataGridColumnFilterConfig[]
     */
    public function getFilters() {
        return $this->filters;
    }

    /**
     * @param string $columnName
     * @return DataGridColumnFilterConfig
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function getFilter($columnName) {
        $columnName = $this->getColumnNameWithAlias($columnName);
        if (empty($this->filters[$columnName])) {
            throw new ScaffoldException("Unknown filter column name: $columnName");
        }
        return $this->filters[$columnName];
    }

    /**
     * @param string $columnName
     * @return DataGridColumnFilterConfig
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function createColumnFilterConfig($columnName) {
        $table = $this->getTable();
        $columnConfig = $this->findColumnConfig($columnName);
        /** @var DataGridColumnFilterConfig $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        if (
            $columnConfig->getType() === Column::TYPE_INT
            && $table::getPkColumnName() === $columnConfig->getName()
            && $columnConfig->getTableStructure()->getTableName() === $table::getName()
        ) {
            // Primary key integer column
            return $configClass::forPositiveInteger()
                ->setColumnName($this->getColumnNameWithAlias($columnName));
        } else {
            return $configClass::create(
                array_key_exists($columnConfig->getType(), self::$columnTypeToFilterType)
                    ? self::$columnTypeToFilterType[$columnConfig->getType()]
                    : DataGridColumnFilterConfig::TYPE_STRING,
                $columnConfig->isValueCanBeNull(),
                $this->getColumnNameWithAlias($columnName)
            );
        }
    }

    /**
     * @param $columnName
     * @return Column
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function findColumnConfig($columnName) {
        $table = $this->getTable();
        $colNameParts = explode('.', $columnName, 2);
        if (count($colNameParts) === 2) {
            $columnName = $colNameParts[1];
            if ($colNameParts[0] !== $table::getAlias()) {
                // recursively find related table
                $table = $this->findRelatedModel($table, $colNameParts[0]);
            }
        }
        return $table::getStructure()->getColumn($columnName);
    }

    /**
     * @param TableInterface $table
     * @param string $relationAlias
     * @param array $scannedTables
     * @param int $depth
     * @return bool|TableInterface
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    protected function findRelatedModel(TableInterface $table, $relationAlias, array &$scannedTables = [], $depth = 0) {
        $structure = $table->getTableStructure();
        if ($structure::hasRelation($relationAlias)) {
            return $structure::getRelation($relationAlias)->getForeignTable();
        }
        $scannedTables[] = $structure::getTableName();
        foreach ($structure::getRelations() as $alias => $relationConfig) {
            /** @var CmfDbTable $relTable */
            $relTable = $relationConfig->getForeignTable();
            if (!empty($scannedTables[$relTable::getName()])) {
                continue;
            }
            $modelFound = $this->findRelatedModel($relTable, $relationAlias, $scannedTables, $depth + 1);
            if ($modelFound) {
                return $modelFound;
            }
            $scannedTables[] = $relTable::getName();
        }
        if (!$depth === 0 && empty($modelFound)) {
            throw new ScaffoldException("Cannot find relation [$relationAlias] in model [{$table->getAlias()}] or among its relations");
        }
        return false;
    }

    /**
     * @param string $columnName
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getColumnNameWithAlias($columnName) {
        if (strpos($columnName, '.') === false) {
            return $this->getTable()->getAlias() . '.' . $columnName;
        }
        return $columnName;
    }

    /**
     * @param string $columnName
     * @return DataGridColumnFilterConfig
     * @throws ScaffoldException
     */
    public function getColumnFilter($columnName) {
        if (empty($columnName) || empty($this->filters[$columnName])) {
            throw new ScaffoldException($this, "Unknown filter column [$columnName]");
        }
        return $this->filters[$columnName];
    }

    /**
     * @return CmfDbTable
     */
    public function getTable() {
        return $this->model;
    }

    /**
     * @return array
     */
    public function getDefaultConditions() {
        return $this->defaultConditions;
    }

    /**
     * @param array $defaultConditions
     * @return $this
     */
    public function setDefaultConditions(array $defaultConditions) {
        $this->defaultConditions = $defaultConditions;
        return $this;
    }

    /**
     * @param string $columnName
     * @param string $operator
     * @param string|int|bool|float $value
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function addDefaultCondition($columnName, $operator, $value) {
        /** @var DataGridColumnFilterConfig $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        if (!$configClass::hasOperator($operator)) {
            throw new ScaffoldException("Unknown filter operator: $operator");
        }
        $columnName = $this->getColumnNameWithAlias($columnName);
        $this->defaultConditions['rules'][] = [
            'field' => $columnName,
            'id' => $configClass::buildFilterId($columnName),
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    /**
     * @return DataGridFilterConfig
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    public function addDefaultConditionForPk() {
        /** @var DataGridColumnFilterConfig $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        return $this->addDefaultCondition(
            $this->getTable()->getPkColumnName(),
            $configClass::OPERATOR_GREATER,
            0
        );
    }

    /**
     * @param array $rulesGroup
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function buildConditionsFromSearchRules(array $rulesGroup) {
        $conditions = [];
        foreach ($rulesGroup['r'] as $rule) {
            if (!empty($rule['c'])) {
                $conditions[] = $this->buildConditionsFromSearchRules($rule);
            } else {
                $conditions[] = $this->buildConditionFromSearchRule($rule);
            }
        }
        return [$rulesGroup['c'] => $conditions];
    }

    /**
     * @param array $searchRule
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ScaffoldException
     */
    protected function buildConditionFromSearchRule(array $searchRule) {
        if (!array_key_exists('v', $searchRule) || empty($searchRule['f']) || empty($searchRule['o'])) {
            throw new ScaffoldException('Invalid search rule passed: ' . json_encode($searchRule));
        }
        $filterColumnConfig = $this->getFilter($searchRule['f']);
        if (is_array($filterColumnConfig)) {
            throw new ScaffoldException('Building condition from filter config provided as array is not implemented');
        }
        return $filterColumnConfig->buildConditionFromSearchRule($searchRule['o'], $searchRule['v']);
    }

    /**
     * Convert $keyValueArray to valid url query args accepted by route()
     * Note: keys - column name in one of formats: 'column_name' or 'Relation.column_name'
     * @param array $keyValueArray
     * @param array $otherArgs
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function makeFilterFromData(array $keyValueArray, array $otherArgs = []) {
        $filters = [];
        foreach ($keyValueArray as $column => $value) {
            $column = $this->getColumnNameWithAlias($column);
            if (!array_key_exists($column, $this->filters) || is_object($value)) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $filters[$column] = $value;
        }
        if (!empty($filters)) {
            $otherArgs['filter'] = json_encode($filters, JSON_UNESCAPED_UNICODE);
        }
        return $otherArgs;
    }

    /**
     * Replace default DataGridColumnFilterConfig class
     * @param string $className
     * @return $this
     */
    public function setDefaultDataGridColumnFilterConfigClass($className) {
        $this->defaultDataGridColumnFilterConfigClass = $className;
        return $this;
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     */
    public function finish() {

    }

}