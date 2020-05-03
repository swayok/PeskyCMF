<?php


namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\TableInterface;

class FilterConfig {
    /** @var TableInterface */
    protected $table;
    /** @var array|ColumnFilter[] */
    protected $filters = [];
    protected $defaultConditions = ['condition' => 'AND', 'rules' => []];
    
    static public $columnTypeToFilterType = [
        Column::TYPE_INT => ColumnFilter::TYPE_INTEGER,
        Column::TYPE_FLOAT => ColumnFilter::TYPE_FLOAT,
        Column::TYPE_BOOL => ColumnFilter::TYPE_BOOL,
        // it is rarely needed to use date-time filter, so it is better to use date filter instead
        Column::TYPE_TIMESTAMP => ColumnFilter::TYPE_DATE,
        Column::TYPE_TIMESTAMP_WITH_TZ => ColumnFilter::TYPE_DATE,
        Column::TYPE_DATE => ColumnFilter::TYPE_DATE,
        Column::TYPE_TIME => ColumnFilter::TYPE_TIME,
    ];
    protected $defaultDataGridColumnFilterConfigClass = ColumnFilter::class;
    /** @var ScaffoldConfig */
    protected $scaffoldConfig;

    /**
     * @param TableInterface $table
     * @param ScaffoldConfig $scaffoldConfig
     * @return $this
     */
    static public function create(TableInterface $table, ScaffoldConfig $scaffoldConfig) {
        return new static($table, $scaffoldConfig);
    }

    /**
     * ScaffoldSectionConfig constructor.
     * @param TableInterface $table
     * @param ScaffoldConfig $scaffoldConfig
     */
    public function __construct(TableInterface $table, ScaffoldConfig $scaffoldConfig) {
        $this->table = $table;
        $this->scaffoldConfig = $scaffoldConfig;
    }

    /**
     * @return ScaffoldConfig
     */
    public function getScaffoldConfig(): ScaffoldConfig {
        return $this->scaffoldConfig;
    }

    /**
     * @param ColumnFilter[]|string[] $filters
     * @return $this
     */
    public function setFilters(array $filters) {
        $this->filters = [];
        /** @var ColumnFilter $config */
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
     * @param null|ColumnFilter $config
     * @return $this
     */
    public function addFilter(string $columnName, ?ColumnFilter $config = null) {
        if (empty($config)) {
            $this->findTableColumn($columnName); //< needed to validate column existense
            $config = $this->createColumnFilterConfig($columnName);
        } else if (!$config->hasColumnNameReplacementForCondition()) {
            // needed to validate column existense
            $this->findTableColumn($config->hasColumnName() ? $config->getColumnName() : $columnName);
        }
        if (!$config->hasColumnName()) {
            $config->setColumnName($this->getColumnNameWithAlias($columnName));
        }
        $config->setFilterConfig($this);
        $this->filters[$config->getColumnName()] = $config;
        return $this;
    }

    /**
     * @return ColumnFilter[]
     */
    public function getFilters(): array {
        return $this->filters;
    }

    /**
     * @param string $columnName
     * @return ColumnFilter
     * @throws ScaffoldException
     */
    public function getFilter(string $columnName): ColumnFilter {
        $columnName = $this->getColumnNameWithAlias($columnName);
        if (empty($this->filters[$columnName])) {
            throw new ScaffoldException("Unknown filter column name: $columnName");
        }
        return $this->filters[$columnName];
    }

    public function createColumnFilterConfig(string $columnName): ColumnFilter {
        $table = $this->getTable();
        $columnConfig = $this->findTableColumn($columnName);
        /** @var ColumnFilter $configClass */
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
                    : ColumnFilter::TYPE_STRING,
                $columnConfig->isValueCanBeNull(),
                $this->getColumnNameWithAlias($columnName)
            );
        }
    }

    public function findTableColumn(string $columnName): Column {
        $table = $this->getTable();
        $colNameParts = explode('.', $columnName, 2);
        if (count($colNameParts) === 2) {
            $columnName = $colNameParts[1];
            if ($colNameParts[0] !== $table::getAlias()) {
                // recursively find related table
                $table = $this->findRelatedTable($table, $colNameParts[0]);
            }
        }
        return $table::getStructure()->getColumn($columnName);
    }

    protected function findRelatedTable(
        TableInterface $table,
        string $relationAlias,
        array &$scannedTables = [],
        int $depth = 0
    ): ?TableInterface {
        $structure = $table->getTableStructure();
        if ($structure::hasRelation($relationAlias)) {
            return $structure::getRelation($relationAlias)->getForeignTable();
        }
        $scannedTables[] = $structure::getTableName();
        foreach ($structure::getRelations() as $alias => $relationConfig) {
            $relTable = $relationConfig->getForeignTable();
            if (!empty($scannedTables[$relTable::getName()])) {
                continue;
            }
            $relatedTableFound = $this->findRelatedTable($relTable, $relationAlias, $scannedTables, $depth + 1);
            if ($relatedTableFound) {
                return $relatedTableFound;
            }
            $scannedTables[] = $relTable::getName();
        }
        if (!$depth === 0 && empty($relatedTableFound)) {
            throw new ScaffoldException("Cannot find relation [$relationAlias] in table [{$table->getAlias()}] or among its relations");
        }
        return null;
    }

    public function getColumnNameWithAlias(string $columnName): string {
        if (strpos($columnName, '.') === false) {
            return $this->getTable()->getAlias() . '.' . $columnName;
        }
        return $columnName;
    }

    /**
     * @param string $columnName
     * @return ColumnFilter
     * @throws ScaffoldException
     */
    public function getColumnFilter($columnName) {
        if (empty($columnName) || empty($this->filters[$columnName])) {
            throw new ScaffoldException($this, "Unknown filter column [$columnName]");
        }
        return $this->filters[$columnName];
    }

    public function getTable(): TableInterface {
        return $this->table;
    }

    public function getDefaultConditions(): array {
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
     * @param mixed $value
     * @return $this
     * @throws ScaffoldException
     */
    public function addDefaultCondition(string $columnName, string $operator, $value) {
        /** @var ColumnFilter $configClass */
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
     * @return $this
     */
    public function addDefaultConditionForPk() {
        /** @var ColumnFilter $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        return $this->addDefaultCondition(
            $this->getTable()->getPkColumnName(),
            $configClass::OPERATOR_GREATER,
            0
        );
    }

    public function buildConditionsFromSearchRules(array $rulesGroup): array {
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
     * @return array
     * @throws ScaffoldException
     */
    protected function buildConditionFromSearchRule(array $searchRule): array {
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
     * @return array - modified $otherArgs
     */
    public function makeFilterFromData(array $keyValueArray, array $otherArgs = []): array {
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
     * Replace default ColumnFilter class
     * @param string $className
     * @return $this
     */
    public function setDefaultDataGridColumnFilterConfigClass(string $className) {
        $this->defaultDataGridColumnFilterConfigClass = $className;
        return $this;
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @return void
     */
    public function finish() {

    }

    /**
     * Called before scaffold template rendering
     * @return void
     */
    public function beforeRender() {

    }

    /**
     * @param ColumnFilter $columnFilter
     * @param string $suffix
     * @return string
     */
    public function translate(ColumnFilter $columnFilter, string $suffix = ''): string {
        return $this->getScaffoldConfig()->translate('datagrid.filter.' . $columnFilter->getColumnNameForTranslation(), $suffix);
    }

}