<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\ORM\Table\TableInterface;
use PeskyORM\ORM\TableStructure\TableColumn\TableColumnDataType;
use PeskyORM\ORM\TableStructure\TableColumn\TableColumnInterface;

class FilterConfig
{
    protected ScaffoldConfig $scaffoldConfig;
    protected TableInterface $table;

    /** @var ColumnFilter[] */
    protected array $filters = [];
    protected array $defaultConditions = ['condition' => 'AND', 'rules' => []];

    public static array $columnTypeToFilterType = [
        TableColumnDataType::INT => ColumnFilter::TYPE_INTEGER,
        TableColumnDataType::FLOAT => ColumnFilter::TYPE_FLOAT,
        TableColumnDataType::BOOL => ColumnFilter::TYPE_BOOL,
        // it is rarely needed to use date-time filter, so it is better to use date filter instead
        TableColumnDataType::TIMESTAMP => ColumnFilter::TYPE_DATE,
        TableColumnDataType::DATE => ColumnFilter::TYPE_DATE,
        TableColumnDataType::TIME => ColumnFilter::TYPE_TIME,
    ];
    protected string $defaultDataGridColumnFilterConfigClass = ColumnFilter::class;

    public static function create(TableInterface $table, ScaffoldConfig $scaffoldConfig): static
    {
        return new static($table, $scaffoldConfig);
    }

    public function __construct(TableInterface $table, ScaffoldConfig $scaffoldConfig)
    {
        $this->table = $table;
        $this->scaffoldConfig = $scaffoldConfig;
    }

    public function getScaffoldConfig(): ScaffoldConfig
    {
        return $this->scaffoldConfig;
    }

    public function getCmfConfig(): CmfConfig
    {
        return $this->getScaffoldConfig()->getCmfConfig();
    }

    /**
     * @param ColumnFilter[]|string[] $filters
     */
    public function setFilters(array $filters): static
    {
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
     * @param string            $columnName - 'col_name' or 'RelationAlias.col_name' or 'RelationAlias.SubRelation.col_name'
     * @param null|ColumnFilter $config
     * @return static
     */
    public function addFilter(string $columnName, ?ColumnFilter $config = null): static
    {
        if (!$config) {
            $this->findTableColumn($columnName); //< needed to validate column existense
            $config = $this->createColumnFilterConfig($columnName);
        } elseif (!$config->hasColumnNameReplacementForCondition()) {
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
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string $columnName
     * @return ColumnFilter
     * @throws \InvalidArgumentException
     */
    public function getFilter(string $columnName): ColumnFilter
    {
        $columnName = $this->getColumnNameWithAlias($columnName);
        if (empty($this->filters[$columnName])) {
            throw new \InvalidArgumentException("Unknown filter column name: $columnName");
        }
        return $this->filters[$columnName];
    }

    public function createColumnFilterConfig(string $columnName): ColumnFilter
    {
        $table = $this->getTable();
        $columnConfig = $this->findTableColumn($columnName);
        /** @var ColumnFilter $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        if (
            $columnConfig->getDataType() === TableColumnDataType::INT
            && $table::getPkColumnName() === $columnConfig->getName()
            && $columnConfig->getTableStructure()?->getTableName() === $table->getTableName()
        ) {
            // Primary key integer column
            return $configClass::forPositiveInteger()
                ->setColumnName($this->getColumnNameWithAlias($columnName));
        }

        return $configClass::create(
            array_key_exists($columnConfig->getDataType(), self::$columnTypeToFilterType)
                ? self::$columnTypeToFilterType[$columnConfig->getDataType()]
                : ColumnFilter::TYPE_STRING,
            $columnConfig->isNullableValues(),
            $this->getColumnNameWithAlias($columnName)
        );
    }

    public function findTableColumn(string $columnName): TableColumnInterface
    {
        $table = $this->getTable();
        $colNameParts = explode('.', $columnName);
        if (count($colNameParts) > 1) {
            $columnName = $colNameParts[count($colNameParts) - 1];
            array_pop($colNameParts);
            if ($colNameParts[0] === $table->getTableAlias()) {
                array_shift($colNameParts);
            }
            foreach ($colNameParts as $relationName) {
                // recursively find related table
                $table = $this->findRelatedTable($table, $relationName);
            }
        }
        return $table->getTableStructure()->getColumn($columnName);
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
        $relatedTable = null;
        foreach ($structure::getRelations() as $relationConfig) {
            $relTable = $relationConfig->getForeignTable();
            if (!empty($scannedTables[$relTable->getTableName()])) {
                continue;
            }
            $relatedTable = $this->findRelatedTable($relTable, $relationAlias, $scannedTables, $depth + 1);
            if ($relatedTable) {
                return $relatedTable;
            }
            $scannedTables[] = $relTable->getTableName();
        }
        if ($depth === 0 && !$relatedTable) {
            $tableStructureClass = get_class($table->getTableStructure());
            throw new \InvalidArgumentException(
                "Cannot find relation [{$relationAlias}] in {$tableStructureClass} or in subrelations"
            );
        }
        return null;
    }

    public function getColumnNameWithAlias(string $columnName): string
    {
        if (!str_contains($columnName, '.')) {
            return $this->getTable()->getTableAlias() . '.' . $columnName;
        }
        return $columnName;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getColumnFilter(string $columnName): ColumnFilter
    {
        if (empty($columnName) || empty($this->filters[$columnName])) {
            throw new \InvalidArgumentException("Unknown filter column [$columnName]");
        }
        return $this->filters[$columnName];
    }

    public function getTable(): TableInterface
    {
        return $this->table;
    }

    public function getDefaultConditions(): array
    {
        return $this->defaultConditions;
    }

    public function setDefaultConditions(array $defaultConditions): static
    {
        $this->defaultConditions = $defaultConditions;
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function addDefaultCondition(string $columnName, string $operator, mixed $value): static
    {
        /** @var ColumnFilter $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        if (!$configClass::hasOperator($operator)) {
            throw new \InvalidArgumentException("Unknown filter operator: $operator");
        }
        $columnName = $this->getColumnNameWithAlias($columnName);
        $this->defaultConditions['rules'][] = [
            'field' => $columnName,
            'id' => $configClass::buildFilterId($columnName),
            'operator' => $operator,
            'value' => $value,
        ];
        return $this;
    }

    public function addDefaultConditionForPk(): static
    {
        /** @var ColumnFilter $configClass */
        $configClass = $this->defaultDataGridColumnFilterConfigClass;
        return $this->addDefaultCondition(
            $this->getTable()->getPkColumnName(),
            $configClass::OPERATOR_GREATER,
            0
        );
    }

    public function buildConditionsFromSearchRules(array $rulesGroup): array
    {
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
     * @throws \InvalidArgumentException
     */
    protected function buildConditionFromSearchRule(array $searchRule): array
    {
        if (!array_key_exists('v', $searchRule) || empty($searchRule['f']) || empty($searchRule['o'])) {
            throw new \InvalidArgumentException('Invalid search rule passed: ' . json_encode($searchRule));
        }
        return $this->getFilter($searchRule['f'])->buildConditionFromSearchRule($searchRule['o'], $searchRule['v']);
    }

    /**
     * Convert $keyValueArray to valid url query args accepted by route()
     * Note: keys - column name in one of formats: 'column_name' or 'Relation.column_name'
     * @return array - modified $otherArgs
     */
    public function makeFilterFromData(array $keyValueArray, array $otherArgs = []): array
    {
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
     */
    public function setDefaultDataGridColumnFilterConfigClass(string $className): static
    {
        $this->defaultDataGridColumnFilterConfigClass = $className;
        return $this;
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     */
    public function finish(): void
    {
    }

    /**
     * Called before scaffold template rendering
     */
    public function beforeRender(): void
    {
    }

    public function translate(ColumnFilter $columnFilter, string $suffix = ''): string
    {
        return $this->getScaffoldConfig()->translate(
            'datagrid.filter.' . $columnFilter->getColumnNameForTranslation(),
            $suffix
        );
    }
}
