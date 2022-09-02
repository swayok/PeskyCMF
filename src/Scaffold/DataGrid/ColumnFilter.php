<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\DataGrid;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\Core\DbExpr;
use Swayok\Utils\NormalizeValue;

class ColumnFilter
{
    
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'double';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';
    public const TYPE_TIMESTAMP = 'datetime';
    public const TYPE_BOOL = 'boolean';
    
    protected static array $dataTypeDefaultOperatorsGroup = [
        self::TYPE_STRING => self::OPERATOR_GROUP_STRINGS,
        self::TYPE_INTEGER => self::OPERATOR_GROUP_NUMBERS,
        self::TYPE_FLOAT => self::OPERATOR_GROUP_NUMBERS,
        self::TYPE_DATE => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_TIME => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_TIMESTAMP => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_BOOL => self::OPERATOR_GROUP_BOOL,
    ];
    
    protected static array $dataTypeToDefaultInputType = [
        self::TYPE_STRING => self::INPUT_TYPE_STRING,
        self::TYPE_INTEGER => self::INPUT_TYPE_STRING,
        self::TYPE_FLOAT => self::INPUT_TYPE_STRING,
        self::TYPE_DATE => self::INPUT_TYPE_STRING,
        self::TYPE_TIME => self::INPUT_TYPE_STRING,
        self::TYPE_TIMESTAMP => self::INPUT_TYPE_STRING,
        self::TYPE_BOOL => self::INPUT_TYPE_RADIO,
    ];
    
    public const OPERATOR_GROUP_NUMBERS = 'numbers';
    public const OPERATOR_GROUP_STRINGS = 'strings';
    public const OPERATOR_GROUP_NULLS = 'nulls';
    public const OPERATOR_GROUP_IN_ARRAY = 'in';
    public const OPERATOR_GROUP_ONLY_EQUALS = 'equals';
    public const OPERATOR_GROUP_TIMESTAMP = 'timestamp';
    public const OPERATOR_GROUP_BOOL = 'boolean';
    public const OPERATOR_GROUP_ALL = 'all';
    
    public const OPERATOR_EQUAL = 'equal';
    public const OPERATOR_NOT_EQUAL = 'not_equal';
    public const OPERATOR_IN_ARRAY = 'in';
    public const OPERATOR_NOT_IN_ARRAY = 'not_in';
    public const OPERATOR_LESS = 'less';
    public const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    public const OPERATOR_GREATER = 'greater';
    public const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';
    public const OPERATOR_BETWEEN = 'between';
    public const OPERATOR_NOT_BETWEEN = 'not_between';
    public const OPERATOR_BEGINS_WITH = 'begins_with';
    public const OPERATOR_NOT_BEGINS_WITH = 'not_begins_with';
    public const OPERATOR_CONTAINS = 'contains';
    public const OPERATOR_NOT_CONTAINS = 'not_contains';
    public const OPERATOR_ENDS_WITH = 'ends_with';
    public const OPERATOR_NOT_ENDS_WITH = 'not_ends_with';
    public const OPERATOR_IS_EMPTY = 'is_empty';
    public const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    public const OPERATOR_IS_NULL = 'is_null';
    public const OPERATOR_IS_NOT_NULL = 'is_not_null';
    
    protected static array $operatorGroups = [
        self::OPERATOR_GROUP_NULLS => [
            self::OPERATOR_IS_NULL,
            self::OPERATOR_IS_NOT_NULL,
        ],
        self::OPERATOR_GROUP_IN_ARRAY => [
            self::OPERATOR_IN_ARRAY,
            self::OPERATOR_NOT_IN_ARRAY,
        ],
        self::OPERATOR_GROUP_ONLY_EQUALS => [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ],
        self::OPERATOR_GROUP_NUMBERS => [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_IN_ARRAY,
            self::OPERATOR_NOT_IN_ARRAY,
            self::OPERATOR_LESS,
            self::OPERATOR_LESS_OR_EQUAL,
            self::OPERATOR_GREATER,
            self::OPERATOR_GREATER_OR_EQUAL,
            self::OPERATOR_BETWEEN,
            self::OPERATOR_NOT_BETWEEN,
        ],
        self::OPERATOR_GROUP_STRINGS => [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_BEGINS_WITH,
            self::OPERATOR_NOT_BEGINS_WITH,
            self::OPERATOR_ENDS_WITH,
            self::OPERATOR_NOT_ENDS_WITH,
            self::OPERATOR_IS_EMPTY,
            self::OPERATOR_IS_NOT_EMPTY,
        ],
        self::OPERATOR_GROUP_TIMESTAMP => [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_LESS,
            self::OPERATOR_LESS_OR_EQUAL,
            self::OPERATOR_GREATER,
            self::OPERATOR_GREATER_OR_EQUAL,
            self::OPERATOR_BETWEEN,
            self::OPERATOR_NOT_BETWEEN,
        ],
        self::OPERATOR_GROUP_BOOL => [
            self::OPERATOR_EQUAL,
        ],
        self::OPERATOR_GROUP_ALL => [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_IN_ARRAY,
            self::OPERATOR_NOT_IN_ARRAY,
            self::OPERATOR_LESS,
            self::OPERATOR_LESS_OR_EQUAL,
            self::OPERATOR_GREATER,
            self::OPERATOR_GREATER_OR_EQUAL,
            self::OPERATOR_BETWEEN,
            self::OPERATOR_NOT_BETWEEN,
            self::OPERATOR_BEGINS_WITH,
            self::OPERATOR_NOT_BEGINS_WITH,
            self::OPERATOR_ENDS_WITH,
            self::OPERATOR_NOT_ENDS_WITH,
            self::OPERATOR_IS_EMPTY,
            self::OPERATOR_IS_NOT_EMPTY,
            self::OPERATOR_IS_NULL,
            self::OPERATOR_IS_NOT_NULL,
        ],
    ];
    
    protected static array $ruleOperatorToDbOperator = [
        self::OPERATOR_EQUAL => '=',
        self::OPERATOR_NOT_EQUAL => '!=',
        self::OPERATOR_IN_ARRAY => 'IN',
        self::OPERATOR_NOT_IN_ARRAY => 'NOT IN',
        self::OPERATOR_LESS => '<',
        self::OPERATOR_LESS_OR_EQUAL => '<=',
        self::OPERATOR_GREATER => '>',
        self::OPERATOR_GREATER_OR_EQUAL => '>=',
        self::OPERATOR_BETWEEN => 'BETWEEN',
        self::OPERATOR_NOT_BETWEEN => 'NOT BETWEEN',
        self::OPERATOR_CONTAINS => '::text ~*',
        self::OPERATOR_NOT_CONTAINS => '::text !~*',
        self::OPERATOR_BEGINS_WITH => '::text ~*',
        self::OPERATOR_NOT_BEGINS_WITH => '::text !~*',
        self::OPERATOR_ENDS_WITH => '::text ~*',
        self::OPERATOR_NOT_ENDS_WITH => '::text !~*',
        self::OPERATOR_IS_EMPTY => '=',
        self::OPERATOR_IS_NOT_EMPTY => '!=',
        self::OPERATOR_IS_NULL => 'IS',
        self::OPERATOR_IS_NOT_NULL => 'IS NOT',
    ];
    
    public const INPUT_TYPE_STRING = 'text';
    public const INPUT_TYPE_TEXT = 'textarea';
    public const INPUT_TYPE_RADIO = 'radio';
    public const INPUT_TYPE_CHECKBOX = 'checkbox';
    public const INPUT_TYPE_SELECT = 'select';
    public const INPUT_TYPE_MULTISELECT = 'multselect';
    
    protected static array $inputTypes = [
        self::INPUT_TYPE_STRING,
        self::INPUT_TYPE_TEXT,
        self::INPUT_TYPE_SELECT,
        self::INPUT_TYPE_MULTISELECT,
        self::INPUT_TYPE_CHECKBOX,
        self::INPUT_TYPE_RADIO,
    ];
    protected ?string $columnName = null;
    protected ?string $columnNameForTranslation = null;
    protected ?string $filterLabel = null;
    protected ?string $dataType = null;
    protected ?string $inputType = null;
    protected bool $multiselect = false;
    protected ?array $operators = null;
    protected array $allowedValues = [
        //'value' => 'label'
    ];
    protected ?string $plugin = null;
    protected array $pluginConfig = [];
    // details: http://querybuilder.js.org/index.html#filters
    protected array $otherSettings = [];
    // details: http://querybuilder.js.org/index.html#validation
    protected array $validators = [
        //'min' => 0,       //< numbers - min value, strings - min length, timestamps - min date/time/datetime in correct 'format'
        //'max' => 0,       //< numbers - max value, strings - max length, timestamps - max date/time/datetime in correct 'format'
        //'step' => 1,      //< for numbers
        //'format' => '',   //< regexp for strings or datetime format for timestamps (http://momentjs.com/docs/#/parsing/string-format/)
        //'allow_empty_value' => true,
    ];
    protected string|null|DbExpr $columnNameReplacementForCondition = null;
    protected bool $nullable = false;
    protected ?FilterConfig $filterConfig = null;
    protected ?\Closure $incomingValueModifier = null;
    
    public static function create(string $dataType = self::TYPE_STRING, bool $canBeNull = false, ?string $columnName = null): static
    {
        return new static($dataType, $canBeNull, $columnName);
    }
    
    /**
     * Configure for ID column (primary or foreign keys)
     */
    public static function forPositiveInteger(bool $excludeZero = false, bool $canBeNull = false, ?string $columnName = null): static
    {
        return static::create(static::TYPE_INTEGER, $canBeNull, $columnName)->setMin($excludeZero ? 1 : 0);
    }
    
    public function __construct(string $dataType = self::TYPE_STRING, bool $canBeNull = false, ?string $columnName = null)
    {
        if (!empty($columnName)) {
            $this->setColumnName($columnName);
        }
        $this->setDataType($dataType, $canBeNull);
    }
    
    public function setFilterConfig(FilterConfig $config): static
    {
        $this->filterConfig = $config;
        return $this;
    }
    
    public function getFilterConfig(): FilterConfig
    {
        if (!$this->filterConfig) {
            throw new \BadMethodCallException('FilterConfig not set');
        }
        return $this->filterConfig;
    }
    
    protected function getCmfConfig(): CmfConfig
    {
        return $this->getFilterConfig()->getCmfConfig();
    }
    
    protected function getValidator(): ValidationFactoryContract
    {
        return $this->getCmfConfig()->getLaravelApp()->make('validator');
    }
    
    public static function hasOperator(string $operator): bool
    {
        return in_array($operator, static::$operatorGroups[static::OPERATOR_GROUP_ALL], true);
    }
    
    public function hasColumnName(): bool
    {
        return !empty($this->columnName);
    }
    
    public function setColumnName(string $columnName): static
    {
        $this->columnName = $columnName;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getColumnName(): string
    {
        if (empty($this->columnName)) {
            throw new \UnexpectedValueException('Column name is empty for this filter');
        }
        return $this->columnName;
    }
    
    public function setColumnNameForTranslation(string $name): static
    {
        $this->columnNameForTranslation = $name;
        return $this;
    }
    
    public function getColumnNameForTranslation(): string
    {
        if ($this->columnNameForTranslation === null) {
            $this->columnNameForTranslation = strtolower(
                trim(
                    preg_replace(
                        ['%([A-Z])%', '%[^a-zA-Z0-9.-]+%', '%\._%'],
                        ['_$1', '_', '.'],
                        $this->columnName
                    ),
                    '_-.'
                )
            );
        }
        return $this->columnNameForTranslation;
    }
    
    public function getDataType(): string
    {
        return $this->dataType;
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function setDataType(string $type, bool $canBeNull = false): static
    {
        if (!array_key_exists($type, static::$dataTypeDefaultOperatorsGroup)) {
            throw new \InvalidArgumentException("Unknown filter type: $type");
        }
        $this->dataType = $type;
        if ($canBeNull) {
            $this->canBeNull();
        }
        $this->setInputType(static::$dataTypeToDefaultInputType[$type]);
        switch ($type) {
            case static::TYPE_BOOL:
                $this->setAllowedValues([
                    't' => $this->getCmfConfig()->transGeneral('.datagrid.filter.bool.yes'),
                    'f' => $this->getCmfConfig()->transGeneral('.datagrid.filter.bool.no'),
                ]);
                break;
            case static::TYPE_TIME:
                $this->setFormat('HH:mm');
                break;
            case static::TYPE_DATE:
            case static::TYPE_TIMESTAMP:
                $pluginConfig = [
                    'locale' => $this->getCmfConfig()->getLaravelApp()->getLocale(),
                    'sideBySide' => false,
                    'useCurrent' => false,
                    'toolbarPlacement' => 'bottom',
                    'showTodayButton' => true,
                    'showClear' => false,
                    'showClose' => true,
                    'keepOpen' => false,
                ];
                if ($type === static::TYPE_DATE) {
                    $this->setFormat('YYYY-MM-DD');
                } else {
                    $this->setFormat('YYYY-MM-DD HH:mm');
                    $pluginConfig['sideBySide'] = true;
                }
                $pluginConfig['format'] = $this->getFormat();
                $this->setPlugin('datetimepicker')
                    ->setPluginConfig($pluginConfig)
                    ->setOtherSettings(['input_event' => 'dp.change']);
                break;
        }
        return $this;
    }
    
    /**
     * Add [is null] and [is not null] operators
     */
    public function canBeNull(): static
    {
        $this->nullable = true;
        return $this;
    }
    
    public function getOperators(): array
    {
        if ($this->operators === null) {
            if ($this->getInputType() === static::INPUT_TYPE_SELECT) {
                $this->operators = $this->multiselect
                    ? static::$operatorGroups[static::OPERATOR_GROUP_IN_ARRAY]
                    : static::$operatorGroups[static::OPERATOR_GROUP_ONLY_EQUALS];
            } else {
                $this->operators = static::$operatorGroups[static::$dataTypeDefaultOperatorsGroup[$this->getDataType()]];
            }
            if ($this->nullable) {
                $this->operators = array_merge($this->operators, static::$operatorGroups[static::OPERATOR_GROUP_NULLS]);
            }
        }
        return $this->operators;
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function setOperators(array $operators): static
    {
        foreach ($operators as $operator) {
            if (!in_array($operator, static::$operatorGroups[static::OPERATOR_GROUP_ALL], true)) {
                throw new \InvalidArgumentException("Unknown filter operator: $operator");
            }
        }
        $this->operators = $operators;
        return $this;
    }
    
    /**
     * @param string $presetName - one of static::OPERATOR_GROUP_*
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setOperatorsFromPreset(string $presetName): static
    {
        if (!array_key_exists($presetName, static::$operatorGroups)) {
            throw new \InvalidArgumentException("Unknown filter operators preset: $presetName");
        }
        $this->operators = static::$operatorGroups[$presetName];
        return $this;
    }
    
    public function getFilterLabel(): string
    {
        if (empty($this->filterLabel)) {
            $this->filterLabel = $this->getFilterConfig()->translate($this);
        }
        return $this->filterLabel;
    }
    
    public function setFilterLabel(string $filterLabel): static
    {
        $this->filterLabel = $filterLabel;
        return $this;
    }
    
    public function getInputType(): string
    {
        return $this->inputType;
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function setInputType(string $inputType): static
    {
        if (!in_array($inputType, static::$inputTypes, true)) {
            throw new \InvalidArgumentException("Unknown filter input type: $inputType");
        }
        if ($inputType === static::INPUT_TYPE_MULTISELECT) {
            $inputType = static::INPUT_TYPE_SELECT;
            $this->multiselect = true;
        }
        $this->inputType = $inputType;
        return $this;
    }
    
    public function getOtherSettings(): array
    {
        return $this->otherSettings;
    }
    
    /**
     * Other filter rule settings
     * Details: http://querybuilder.js.org/index.html#filters
     */
    public function setOtherSettings(array $otherSettings): static
    {
        $this->otherSettings = $otherSettings;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getAllowedValues(): array
    {
        if (empty($this->allowedValues) && $this->isItRequiresAllowedValues()) {
            throw new \UnexpectedValueException('List of allowed values is empty');
        }
        return value($this->allowedValues);
    }
    
    /**
     * This filter has one of selection types (select, radio, checkbox) and require $this->allowedValues to be set
     */
    protected function isItRequiresAllowedValues(): bool
    {
        return in_array(
            $this->inputType,
            [static::INPUT_TYPE_SELECT, static::INPUT_TYPE_RADIO, static::INPUT_TYPE_CHECKBOX],
            true
        );
    }
    
    /**
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function setAllowedValues(array|\Closure $allowedValues): static
    {
        if (!$this->isItRequiresAllowedValues()) {
            throw new \BadMethodCallException("Cannot set allowed values list to a filter input type: {$this->inputType}");
        } elseif (empty($allowedValues)) {
            throw new \InvalidArgumentException('List of allowed values is empty');
        }
        $this->allowedValues = $allowedValues;
        return $this;
    }
    
    /**
     * Alias for setAllowedValues()
     */
    public function setOptions(array|\Closure $allowedValues): static
    {
        return $this->setAllowedValues($allowedValues);
    }
    
    public function getPlugin(): ?string
    {
        return $this->plugin;
    }
    
    public function setPlugin(string $plugin): static
    {
        $this->plugin = $plugin;
        return $this;
    }
    
    public function getPluginConfig(): array
    {
        return $this->pluginConfig;
    }
    
    public function setPluginConfig(array $pluginConfig): static
    {
        $this->pluginConfig = $pluginConfig;
        return $this;
    }
    
    public function getValidators(): array
    {
        return $this->validators;
    }
    
    /**
     * @param float|int|string $min -
     *      numbers: min value
     *      strings: min length
     *      timestamps: min date/time/datetime in correct 'format'
     * @return static
     */
    public function setMin(float|int|string $min): static
    {
        $this->validators['min'] = $min;
        return $this;
    }
    
    /**
     * @param float|int|string $max -
     *      numbers: max value
     *      strings: max length
     *      timestamps: max date/time/datetime in correct 'format'
     * @return static
     */
    public function setMax(float|int|string $max): static
    {
        $this->validators['max'] = $max;
        return $this;
    }
    
    /**
     * @param float|int $step - for numbers only
     * @return static
     */
    public function setStep(float|int $step): static
    {
        $this->validators['step'] = $step;
        return $this;
    }
    
    /**
     * @param string $format -
     *      strings: regexp
     *      timestamps: datetime format for timestamps in js (http://momentjs.com/docs/#/parsing/string-format/)
     * @return static
     */
    public function setFormat(string $format): static
    {
        $this->validators['format'] = $format;
        return $this;
    }
    
    public function getFormat(): ?string
    {
        return empty($this->validators['format']) ? null : $this->validators['format'];
    }
    
    /**
     * @return string|null|DbExpr
     */
    public function getColumnNameReplacementForCondition(): DbExpr|string|null
    {
        return $this->columnNameReplacementForCondition;
    }
    
    public function hasColumnNameReplacementForCondition(): bool
    {
        return !empty($this->columnNameReplacementForCondition);
    }
    
    /**
     * Replace column's name when building a condition for DB
     * Example:
     *  without replacement: for column_name = 'count', operation = "equals", value = "1" conditon will be: "count" = '1'
     *  with replacement: expression = DbExpr('COALESCE(`count`, ``0``)') condition will be COALESCE("count", 0) = '1'
     */
    public function setColumnNameReplacementForCondition(DbExpr|string $columnNameReplacementForCondition): static
    {
        $this->columnNameReplacementForCondition = $columnNameReplacementForCondition;
        return $this;
    }
    
    /**
     * @param \Closure $modifier - function ($value, $operator, ColumnFilter $columnFilter) { return $value; }
     * @return static
     */
    public function setIncomingValueModifier(\Closure $modifier): static
    {
        $this->incomingValueModifier = $modifier;
        return $this;
    }
    
    protected function modifyIncomingValue(mixed $value, string $operator): mixed
    {
        if ($this->incomingValueModifier) {
            return call_user_func($this->incomingValueModifier, $value, $operator, $this);
        }
        return $value;
    }
    
    public function buildConfig(): array
    {
        return array_merge([
            'id' => static::buildFilterId($this->getColumnName()),
            'field' => $this->getColumnName(),
            'label' => $this->getFilterLabel(),
            'type' => $this->getDataType(),
            'input' => $this->getInputType(),
            'values' => $this->getAllowedValues(),
            'multiple' => $this->multiselect,
            'validation' => $this->getValidators(),
            'operators' => $this->getOperators(),
            'plugin' => $this->getPlugin(),
            'plugin_config' => $this->getPluginConfig(),
        ], $this->otherSettings);
    }
    
    public static function buildFilterId(string $columnName): string
    {
        return 'filter-for-' . strtolower(preg_replace('%[^a-zA-Z0-9]+%i', '-', $columnName));
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function buildConditionFromSearchRule(string $operator, mixed $value): array
    {
        if (!in_array($operator, $this->getOperators(), true)) {
            throw new \InvalidArgumentException("Operator [$operator] is forbidden for filter [{$this->getColumnName()}]");
        }
        if (!is_array($value)) {
            $value = trim($value);
        }
        // resolve multivalues
        switch ($operator) {
            case static::OPERATOR_IN_ARRAY:
            case static::OPERATOR_NOT_IN_ARRAY:
                if (is_string($value)) {
                    $value = preg_split('%\s*,\s*%', $value);
                }
                break;
        }
        $value = $this->modifyIncomingValue($value, $operator);
        $this->validateValue($value, $operator);
        $value = $this->convertRuleValueToConditionValue($value, $operator);
        $dbOperator = $this->convertRuleOperatorToDbOperator($operator);
        $dataTypeConverter = $this->getValueDataTypeConverterForDb();
        // resolve column name replacement (it could be a DbExpr that concatenates many columns)
        if ($this->hasColumnNameReplacementForCondition()) {
            $colReplacement = $this->getColumnNameReplacementForCondition();
            if ($colReplacement instanceof DbExpr) {
                $value = match ($operator) {
                    static::OPERATOR_IN_ARRAY,
                    static::OPERATOR_NOT_IN_ARRAY => '(``' . implode('``,``', $value) . '``)',
                    
                    static::OPERATOR_BETWEEN,
                    static::OPERATOR_NOT_BETWEEN => "``{$value[0]}`` AND ``{$value[1]}``",
                    
                    static::OPERATOR_IS_NULL,
                    static::OPERATOR_IS_NOT_NULL => 'NULL',
                    
                    default => "``{$value}``",
                };
                return [DbExpr::create($colReplacement->get() . " {$dbOperator} {$value}")];
            } else {
                $columnName = $colReplacement;
            }
        } else {
            $columnName = $this->getColumnName();
        }
        return [trim($columnName . $dataTypeConverter . ' ' . $dbOperator) => $value];
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    protected function validateValue(mixed $value, string $operator): void
    {
        if (
            ($value === null || $value === '')
            && in_array(
                $operator,
                [static::OPERATOR_IS_NULL, static::OPERATOR_IS_NOT_NULL, static::OPERATOR_IS_NOT_EMPTY, static::OPERATOR_IS_EMPTY],
                true
            )
        ) {
            return; //< no value needed
        }
        if (is_array($value)) {
            foreach ($value as $i => &$val) {
                if (empty($val)) {
                    unset($value[$i]);
                } else {
                    $this->validateValue(trim($val), $operator);
                }
            }
            unset($val);
            if (empty($value)) {
                throw new \InvalidArgumentException("Empty filter value is not allowed for [$operator] operator");
            }
            if (count($value) !== 2 && in_array($operator, [static::OPERATOR_BETWEEN, static::OPERATOR_NOT_BETWEEN], true)) {
                throw new \InvalidArgumentException("There should be exactly 2 filter values for [$operator] operator");
            }
            return;
        }
        $validatorRule = 'required';
        $validators = $this->getValidators();
        if (!empty($validators['min'])) {
            $validatorRule .= '|min:' . $validators['min'];
        }
        if (!empty($validators['max'])) {
            $validatorRule .= '|max:' . $validators['max'];
        }
        switch ($this->getDataType()) {
            case static::TYPE_INTEGER:
                $validatorRule .= '|integer';
                break;
            case static::TYPE_BOOL:
                $validatorRule .= '|in:t,f,0,1';
                break;
            case static::TYPE_FLOAT:
                $validatorRule .= '|numeric';
                break;
            case static::TYPE_DATE:
            case static::TYPE_TIME:
            case static::TYPE_TIMESTAMP:
                $validatorRule .= '|date';
                break;
            case static::TYPE_STRING:
                if (!empty($validators['format'])) {
                    $validatorRule .= '|regex:' . $validators['format'];
                }
                break;
        }
        $validator = $this->getValidator()->make(['value' => $value], ['value' => $validatorRule]);
        if ($validator->fails()) {
            throw new \InvalidArgumentException("Invalid value [$value] passed for filter column [{$this->getColumnName()}]");
        }
    }
    
    protected function convertRuleValueToConditionValue(mixed $value, string $operator): mixed
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = trim($this->convertRuleValueToConditionValue($val, $operator));
            }
            return array_values($value);
        }
        switch ($this->getDataType()) {
            case static::TYPE_TIME:
                $value = NormalizeValue::normalizeTime($value);
                break;
            case static::TYPE_DATE:
                $value = NormalizeValue::normalizeDate($value);
                break;
            case static::TYPE_TIMESTAMP:
                $value = NormalizeValue::normalizeDateTime($value);
                break;
            case static::TYPE_INTEGER:
                $value = NormalizeValue::normalizeInteger($value);
                break;
            case static::TYPE_FLOAT:
                $value = NormalizeValue::normalizeFloat($value);
                break;
            case static::TYPE_BOOL:
                $value = NormalizeValue::normalizeBooleanExtended($value, ['f']);
                break;
        }
        switch ($operator) {
            case static::OPERATOR_BEGINS_WITH:
            case static::OPERATOR_NOT_BEGINS_WITH:
                return '^' . preg_quote($value, null);
            case static::OPERATOR_ENDS_WITH:
            case static::OPERATOR_NOT_ENDS_WITH:
                return preg_quote($value, null) . '$';
            case static::OPERATOR_CONTAINS:
            case static::OPERATOR_NOT_CONTAINS:
                return preg_quote($value, null);
            case static::OPERATOR_EQUAL:
            case static::OPERATOR_NOT_EQUAL:
                if ($this->getDataType() === static::TYPE_STRING && $this->getInputType() !== static::INPUT_TYPE_SELECT) {
                    return '^' . preg_quote($value, null) . '$';
                }
                break;
            case static::OPERATOR_IS_NULL:
            case static::OPERATOR_IS_NOT_NULL:
                return null;
            case static::OPERATOR_IS_EMPTY:
            case static::OPERATOR_IS_NOT_EMPTY:
                return '';
        }
        return $value;
    }
    
    protected function convertRuleOperatorToDbOperator(string $operator): string
    {
        switch ($operator) {
            case static::OPERATOR_EQUAL:
            case static::OPERATOR_IN_ARRAY:
                if ($this->getDataType() === static::TYPE_STRING && $this->getInputType() !== static::INPUT_TYPE_SELECT) {
                    return static::$ruleOperatorToDbOperator[static::OPERATOR_CONTAINS]; //< for case-insensitive search
                }
                break;
            case static::OPERATOR_NOT_EQUAL:
            case static::OPERATOR_NOT_IN_ARRAY:
                if ($this->getDataType() === static::TYPE_STRING && $this->getInputType() !== static::INPUT_TYPE_SELECT) {
                    return static::$ruleOperatorToDbOperator[static::OPERATOR_NOT_CONTAINS]; //< for case-insensitive search
                }
                break;
        }
        return static::$ruleOperatorToDbOperator[$operator];
    }
    
    /**
     * Get forced data type converter for column and value in DB. For example for dates it will return '::date'
     * @return string
     */
    protected function getValueDataTypeConverterForDb(): string
    {
        return match ($this->getDataType()) {
            static::TYPE_TIME => '::time',
            
            static::TYPE_DATE,
            static::TYPE_TIMESTAMP => '::date',
            
            default => '',
        };
    }
    
}