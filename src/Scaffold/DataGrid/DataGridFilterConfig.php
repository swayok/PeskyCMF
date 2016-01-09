<?php


namespace PeskyCMF\Scaffold\DataGrid;

use App\Db\BaseDbModel;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyORM\DbColumnConfig;

class DataGridFilterConfig {
    /** @var BaseDbModel */
    protected $model;
    /** @var array|DataGridColumnFilterConfig[] */
    protected $filters = [];
    protected $defaultConditions = ['condition' => 'AND', 'rules' => []];
    
    static $dbTypeToFilterType = [
        DbColumnConfig::DB_TYPE_VARCHAR => DataGridColumnFilterConfig::TYPE_STRING,
        DbColumnConfig::DB_TYPE_TEXT => DataGridColumnFilterConfig::TYPE_STRING,
        DbColumnConfig::DB_TYPE_INT => DataGridColumnFilterConfig::TYPE_INTEGER,
        DbColumnConfig::DB_TYPE_FLOAT => DataGridColumnFilterConfig::TYPE_FLOAT,
        DbColumnConfig::DB_TYPE_BOOL => DataGridColumnFilterConfig::TYPE_BOOL,
        DbColumnConfig::DB_TYPE_JSONB => DataGridColumnFilterConfig::TYPE_STRING,
        DbColumnConfig::DB_TYPE_TIMESTAMP => DataGridColumnFilterConfig::TYPE_TIMESTAMP,
        DbColumnConfig::DB_TYPE_DATE => DataGridColumnFilterConfig::TYPE_DATE,
        DbColumnConfig::DB_TYPE_TIME => DataGridColumnFilterConfig::TYPE_TIME,
        DbColumnConfig::DB_TYPE_IP_ADDRESS => DataGridColumnFilterConfig::TYPE_STRING,
    ];

    /**
     * @param BaseDbModel $model
     * @return $this
     */
    static public function create(BaseDbModel $model) {
        $class = get_called_class();
        return new $class($model);
    }

    /**
     * ScaffoldActionConfig constructor.
     * @param BaseDbModel $model
     */
    public function __construct(BaseDbModel $model) {
        $this->model = $model;
    }

    /**
     * @param DataGridColumnFilterConfig[] $filters
     * @return $this
     * @throws ScaffoldException
     */
    public function setFilters(array $filters) {
        $this->filters = [];
        /** @var DataGridColumnFilterConfig $config */
        foreach ($filters as $columnName => $config) {
            if (is_integer($columnName)) {
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
     */
    public function createColumnFilterConfig($columnName) {
        $model = $this->getModel();
        $columnConfig = $this->findColumnConfig($columnName);
        if (
            $columnConfig->getDbTableConfig()->getName() === $model->getTableName()
            && $model->getPkColumnName() === $columnConfig->getName()
            && $columnConfig->getDbType() === DbColumnConfig::DB_TYPE_INT
        ) {
            // Primary key integer column
            return DataGridColumnFilterConfig::forPositiveInteger()
                ->setColumnName($this->getColumnNameWithAlias($columnName));
        } else {
            return DataGridColumnFilterConfig::create(
                self::$dbTypeToFilterType[$columnConfig->getDbType()],
                $columnConfig->isNullable(),
                $this->getColumnNameWithAlias($columnName)
            );
        }
    }

    /**
     * @param $columnName
     * @return DbColumnConfig
     * @throws ScaffoldException
     */
    public function findColumnConfig($columnName) {
        $model = $this->getModel();
        $colNameParts = explode('.', $columnName, 2);
        if (count($colNameParts) === 2) {
            $columnName = $colNameParts[1];
            if ($colNameParts[0] !== $model->getAlias()) {
                // recursively find related model
                $model = $this->findRelatedModel($model, $colNameParts[0]);
            }
        }
        return $model->getTableColumn($columnName);
    }

    /**
     * @param BaseDbModel $model
     * @param string $relationAlias
     * @param array $scannedModels
     * @param int $depth
     * @return bool|\PeskyORM\DbModel
     * @throws ScaffoldException
     * @throws \PeskyORM\Exception\DbModelException
     */
    protected function findRelatedModel(BaseDbModel $model, $relationAlias, &$scannedModels = [], $depth = 0) {
        if ($model->hasTableRelation($relationAlias)) {
            return $model->getRelatedModel($relationAlias);
        }
        $scannedModels[] = $model->getTableName();
        foreach ($model->getTableRealtaions() as $alias => $relationConfig) {
            /** @var BaseDbModel $relModel */
            $relModel = $model->getRelatedModel($alias);
            if (!empty($scannedModels[$relModel->getTableName()])) {
                continue;
            }
            $modelFound = $this->findRelatedModel($relModel, $relationAlias, $scannedModels, $depth + 1);
            if ($modelFound) {
                return $modelFound;
            }
            $scannedModels[] = $relModel->getTableName();
        }
        if (!$depth === 0 && empty($modelFound)) {
            throw new ScaffoldException("Cannot find relation [$relationAlias] in model [{$model->getAlias()}] or among its relations");
        }
        return false;
    }

    /**
     * @param string $columnName
     * @return string
     */
    public function getColumnNameWithAlias($columnName) {
        if (strstr($columnName, '.') === false) {
            return $this->getModel()->getAlias() . '.' . $columnName;
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
     * @return BaseDbModel
     */
    public function getModel() {
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
     * @throws ScaffoldException
     */
    public function addDefaultCondition($columnName, $operator, $value) {
        if (!DataGridColumnFilterConfig::hasOperator($operator)) {
            throw new ScaffoldException("Unknown filter operator: $operator");
        }
        $columnName = $this->getColumnNameWithAlias($columnName);
        $this->defaultConditions['rules'][] = [
            'field' => $columnName,
            'id' => DataGridColumnFilterConfig::buildFilterId($columnName),
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    /**
     * @return DataGridFilterConfig
     * @throws ScaffoldException
     */
    public function addDefaultConditionForPk() {
        return $this->addDefaultCondition(
            $this->getModel()->getPkColumnName(),
            DataGridColumnFilterConfig::OPERATOR_GREATER,
            0
        );
    }

    /**
     * @param array $rulesGroup
     * @return array
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
     * @throws ScaffoldException
     */
    protected function buildConditionFromSearchRule(array $searchRule) {
        if (empty($searchRule['f']) || empty($searchRule['o']) || !array_key_exists('v', $searchRule)) {
            throw new ScaffoldException('Invalid search rule passed: ' . json_encode($searchRule));
        }
        $filterColumnConfig = $this->getFilter($searchRule['f']);
        if (is_array($filterColumnConfig)) {
            throw new ScaffoldException('Building condition from filter config provided as array is not implemented');
        }
        return $filterColumnConfig->buildConditionFromSearchRule($searchRule['o'], $searchRule['v']);
    }

}