<?php


namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Controllers\CmfGeneralController;
use PeskyCMF\Scaffold\ScaffoldException;
use PeskyORM\DbExpr;
use Swayok\Utils\NormalizeValue;

class DataGridColumnFilterConfig {

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'double';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_TIMESTAMP = 'datetime';
    const TYPE_BOOL = 'boolean';

    static protected $dataTypeDefaultOperatorsGroup = [
        self::TYPE_STRING => self::OPERATOR_GROUP_STRINGS,
        self::TYPE_INTEGER => self::OPERATOR_GROUP_NUMBERS,
        self::TYPE_FLOAT => self::OPERATOR_GROUP_NUMBERS,
        self::TYPE_DATE => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_TIME => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_TIMESTAMP => self::OPERATOR_GROUP_TIMESTAMP,
        self::TYPE_BOOL => self::OPERATOR_GROUP_BOOL,
    ];

    static protected $dataTypeToDefaultInputType = [
        self::TYPE_STRING => self::INPUT_TYPE_STRING,
        self::TYPE_INTEGER => self::INPUT_TYPE_STRING,
        self::TYPE_FLOAT => self::INPUT_TYPE_STRING,
        self::TYPE_DATE => self::INPUT_TYPE_STRING,
        self::TYPE_TIME => self::INPUT_TYPE_STRING,
        self::TYPE_TIMESTAMP => self::INPUT_TYPE_STRING,
        self::TYPE_BOOL => self::INPUT_TYPE_RADIO,
    ];

    const OPERATOR_GROUP_NUMBERS = 'numbers';
    const OPERATOR_GROUP_STRINGS = 'strings';
    const OPERATOR_GROUP_NULLS = 'nulls';
    const OPERATOR_GROUP_IN_ARRAY = 'in';
    const OPERATOR_GROUP_TIMESTAMP = 'timestamp';
    const OPERATOR_GROUP_BOOL = 'boolean';
    const OPERATOR_GROUP_ALL = 'all';

    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_NOT_EQUAL = 'not_equal';
    const OPERATOR_IN_ARRAY = 'in';
    const OPERATOR_NOT_IN_ARRAY = 'not_in';
    const OPERATOR_LESS = 'less';
    const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_NOT_BETWEEN = 'not_between';
    const OPERATOR_BEGINS_WITH = 'begins_with';
    const OPERATOR_NOT_BEGINS_WITH = 'not_begins_with';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_NOT_CONTAINS = 'not_contains';
    const OPERATOR_ENDS_WITH = 'ends_with';
    const OPERATOR_NOT_ENDS_WITH = 'not_ends_with';
    const OPERATOR_IS_EMPTY = 'is_empty';
    const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    const OPERATOR_IS_NULL = 'is_null';
    const OPERATOR_IS_NOT_NULL = 'is_not_null';

    static protected $operatorGroups = [
        self::OPERATOR_GROUP_NULLS => [
            self::OPERATOR_IS_NULL,
            self::OPERATOR_IS_NOT_NULL,
        ],
        self::OPERATOR_GROUP_IN_ARRAY => [
            self::OPERATOR_IN_ARRAY,
            self::OPERATOR_NOT_IN_ARRAY,
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
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_IN_ARRAY,
            self::OPERATOR_NOT_IN_ARRAY,
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
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
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
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

    static protected $ruleOperatorToDbOperator = [
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
        self::OPERATOR_CONTAINS => '~*',
        self::OPERATOR_NOT_CONTAINS => '!~*',
        self::OPERATOR_BEGINS_WITH => '~*',
        self::OPERATOR_NOT_BEGINS_WITH => '!~*',
        self::OPERATOR_ENDS_WITH => '~*',
        self::OPERATOR_NOT_ENDS_WITH => '!~*',
        self::OPERATOR_IS_EMPTY => '=',
        self::OPERATOR_IS_NOT_EMPTY => '!=',
        self::OPERATOR_IS_NULL => 'IS',
        self::OPERATOR_IS_NOT_NULL => 'IS NOT',
    ];

    const INPUT_TYPE_STRING = 'text';
    const INPUT_TYPE_TEXT = 'textarea';
    const INPUT_TYPE_RADIO = 'radio';
    const INPUT_TYPE_CHECKBOX = 'checkbox';
    const INPUT_TYPE_SELECT = 'select';
    const INPUT_TYPE_MULTISELECT = 'multselect';

    static protected $inputTypes = [
        self::INPUT_TYPE_STRING,
        self::INPUT_TYPE_TEXT,
        self::INPUT_TYPE_SELECT,
        self::INPUT_TYPE_MULTISELECT,
        self::INPUT_TYPE_CHECKBOX,
        self::INPUT_TYPE_RADIO,
    ];

    protected $columnName = null;
    protected $filterLabel = null;
    protected $dataType = null;
    protected $inputType = null;
    protected $multiselect = false;
    protected $operators = [];
    protected $allowedValues = [
        //'value' => 'label'
    ];
    protected $plugin = null;
    protected $pluginConfig = [];
    // details: http://mistic100.github.io/jQuery-QueryBuilder/index.html#usage Filters
    protected $otherSettings = [

    ];
    // details: http://mistic100.github.io/jQuery-QueryBuilder/index.html#usage Validation
    protected $validators = [
        //'min' => 0,       //< numbers - min value, strings - min length, timestamps - min date/time/datetime in correct 'format'
        //'max' => 0,       //< numbers - max value, strings - max length, timestamps - max date/time/datetime in correct 'format'
        //'step' => 1,      //< for numbers
        //'format' => '',   //< regexp for strings or datetime format for timestamps (http://momentjs.com/docs/#/parsing/string-format/)
    ];
    /** @var null|string|DbExpr */
    protected $columnNameReplacementForCondition = null;

    /**
     * @param string $dataType
     * @param bool $canBeNull
     * @param null|string $columnName
     * @return DataGridColumnFilterConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    static public function create($dataType = self::TYPE_STRING, $canBeNull = false, $columnName = null) {
        return new static($dataType, $canBeNull, $columnName);
    }

    /**
     * Configure for ID column (primary or foreign keys)
     * @param bool $excludeZero
     * @param bool $canBeNull
     * @param null|string $columnName
     * @return DataGridColumnFilterConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    static public function forPositiveInteger($excludeZero = false, $canBeNull = false, $columnName = null) {
        return static::create(static::TYPE_INTEGER, $canBeNull, $columnName)->setMin($excludeZero ? 1 : 0);
    }

    /**
     * DataGridColumnFilterConfig constructor.
     * @param string $dataType
     * @param bool $canBeNull
     * @param null $columnName
     * @throws ScaffoldException
     */
    public function __construct($dataType = self::TYPE_STRING, $canBeNull = false, $columnName = null) {
        if (!empty($columnName)) {
            $this->setColumnName($columnName);
        }
        $this->setDataType($dataType, $canBeNull);
    }

    /**
     * @param $operator
     * @return bool
     */
    static public function hasOperator($operator) {
        return in_array($operator, static::$operatorGroups[static::OPERATOR_GROUP_ALL], true);
    }

    /**
     * @return bool
     */
    public function hasColumnName() {
        return !empty($this->columnName);
    }

    /**
     * @param null $columnName
     * @return $this
     */
    public function setColumnName($columnName) {
        $this->columnName = $columnName;
        return $this;
    }

    /**
     * @return string
     * @throws ScaffoldException
     */
    public function getColumnName() {
        if (empty($this->columnName)) {
            throw new ScaffoldException('Column name is empty for this filter');
        }
        return $this->columnName;
    }

    /**
     * @return string
     */
    public function getDataType() {
        return $this->dataType;
    }

    /**
     * @param string $type
     * @param bool $canBeNull
     * @return $this
     * @throws ScaffoldException
     */
    public function setDataType($type, $canBeNull = false) {
        if (!array_key_exists($type, static::$dataTypeDefaultOperatorsGroup)) {
            throw new ScaffoldException("Unknown filter type: $type");
        }
        $this->dataType = $type;
        $this->operators = static::$operatorGroups[static::$dataTypeDefaultOperatorsGroup[$type]];
        if ($canBeNull) {
            $this->canBeNull();
        }
        $this->setInputType(static::$dataTypeToDefaultInputType[$type]);
        switch ($type) {
            case static::TYPE_BOOL:
                $this->setAllowedValues([
                    't' => CmfConfig::transBase('.datagrid.filter.bool.yes'),
                    'f' => CmfConfig::transBase('.datagrid.filter.bool.no'),
                ]);
                break;
            case static::TYPE_TIME:
                $this->setFormat('HH:mm');
                break;
            case static::TYPE_DATE:
            case static::TYPE_TIMESTAMP:
                $pluginConfig = [
                    'locale' => app()->getLocale(),
                    'sideBySide' => false,
                    'useCurrent' => true,
                    'toolbarPlacement' => 'bottom',
                    'showTodayButton' => true,
                    'showClear' => false,
                    'showClose' => true,
                    'keepOpen' => false
                ];
                if ($type === static::TYPE_DATE) {
                    $this->setFormat('YYYY-MM-DD');
                } else {
                    $this->setFormat('YYYY-MM-DD HH:mm');
                    $pluginConfig['sideBySide'] = true;
                }
                $pluginConfig['format'] = $this->getFormat();
                $this->setPlugin('datetimepicker')
                    ->setPluginConfig($pluginConfig);
                break;
        }
        return $this;
    }

    /**
     * Add [is null] and [is not null] operators
     * @return $this
     */
    public function canBeNull() {
        $this->operators = array_merge($this->operators, static::$operatorGroups[static::OPERATOR_GROUP_NULLS]);
        return $this;
    }

    /**
     * @return string
     */
    public function getOperators() {
        return $this->operators;
    }

    /**
     * @param array $operators
     * @return $this
     * @throws ScaffoldException
     */
    public function setOperators(array $operators) {
        foreach ($operators as $operator) {
            if (!in_array($operator, static::$operatorGroups[static::OPERATOR_GROUP_ALL], true)) {
                throw new ScaffoldException("Unknown filter operator: $operator");
            }
        }
        $this->operators = $operators;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFilterLabel() {
        return !empty($this->filterLabel);
    }

    /**
     * @return string
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function getFilterLabel() {
        return empty($this->filterLabel) ? $this->getColumnName() : $this->filterLabel;
    }

    /**
     * @param null $filterLabel
     * @return $this
     */
    public function setFilterLabel($filterLabel) {
        $this->filterLabel = $filterLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputType() {
        return $this->inputType;
    }

    /**
     * @param string $inputType
     * @return $this
     * @throws ScaffoldException
     */
    public function setInputType($inputType) {
        if (!in_array($inputType, static::$inputTypes, true)) {
            throw new ScaffoldException("Unknown filter input type: $inputType");
        }
        switch ($inputType) {
            case static::INPUT_TYPE_MULTISELECT:
                $inputType = static::INPUT_TYPE_SELECT;
                $this->multiselect = true;
                break;
        }
        $this->inputType = $inputType;
        return $this;
    }

    /**
     * @return array
     */
    public function getOtherSettings() {
        return $this->otherSettings;
    }

    /**
     * @param array $otherSettings
     * @return $this
     */
    public function setOtherSettings(array $otherSettings) {
        $this->otherSettings = $otherSettings;
        return $this;
    }

    /**
     * @return array
     * @throws ScaffoldException
     */
    public function getAllowedValues() {
        if (empty($this->allowedValues) && $this->isItRequireAllowedValues()) {
            throw new ScaffoldException('List of allowed values is empty');
        }
        return is_callable($this->allowedValues) ? call_user_func($this->allowedValues) : $this->allowedValues;
    }

    /**
     * This filter has one of selection types (select, radio, checkbox) and require $this->allowedValues to be set
     * @return bool
     */
    protected function isItRequireAllowedValues() {
        return in_array(
            $this->inputType,
            [static::INPUT_TYPE_SELECT, static::INPUT_TYPE_RADIO, static::INPUT_TYPE_CHECKBOX],
            true
        );
    }

    /**
     * @param array|callable $allowedValues
     * @return $this
     * @throws ScaffoldException
     */
    public function setAllowedValues($allowedValues) {
        if (!$this->isItRequireAllowedValues()) {
            throw new ScaffoldException("Cannot set allowed values list to a filter input type: {$this->inputType}");
        } else if (empty($allowedValues)) {
            throw new ScaffoldException('List of allowed values is empty');
        } else if (!is_array($allowedValues) && !is_callable($allowedValues)) {
            throw new ScaffoldException('List of allowed values should be array or callable');
        }
        $this->allowedValues = $allowedValues;
        return $this;
    }

    /**
     * @return null
     */
    public function getPlugin() {
        return $this->plugin;
    }

    /**
     * @param null $plugin
     * @return $this
     */
    public function setPlugin($plugin) {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @return array
     */
    public function getPluginConfig() {
        return $this->pluginConfig;
    }

    /**
     * @param array $pluginConfig
     * @return $this
     */
    public function setPluginConfig(array $pluginConfig) {
        $this->pluginConfig = $pluginConfig;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidators() {
        return $this->validators;
    }

    /**
     * @param int|string $min -
     *      numbers: min value
     *      strings: min length
     *      timestamps: min date/time/datetime in correct 'format'
     * @return $this
     */
    public function setMin($min) {
        $this->validators['min'] = $min;
        return $this;
    }

    /**
     * @param int|string $max -
     *      numbers: max value
     *      strings: max length
     *      timestamps: max date/time/datetime in correct 'format'
     * @return $this
     */
    public function setMax($max) {
        $this->validators['max'] = $max;
        return $this;
    }

    /**
     * @param int|float $step - for numbers only
     * @return $this
     */
    public function setStep($step) {
        $this->validators['step'] = $step;
        return $this;
    }

    /**
     * @param string $format -
     *      strings: regexp
     *      timestamps: datetime format for timestamps in js (http://momentjs.com/docs/#/parsing/string-format/)
     * @return $this
     * @throws ScaffoldException
     */
    public function setFormat($format) {
        $this->validators['format'] = $format;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormat() {
        return empty($this->validators['format']) ? null : $this->validators['format'];
    }

    /**
     * @return string
     */
    public function getColumnNameReplacementForCondition() {
        return $this->columnNameReplacementForCondition;
    }

    /**
     * @return string
     */
    public function hasColumnNameReplacementForCondition() {
        return !empty($this->columnNameReplacementForCondition);
    }

    /**
     * @param null $columnNameReplacementForCondition
     * @return $this
     */
    public function setColumnNameReplacementForCondition($columnNameReplacementForCondition) {
        $this->columnNameReplacementForCondition = $columnNameReplacementForCondition;
        return $this;
    }

    /**
     * @return array
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function buildConfig() {
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
            'plugin_config' => $this->getPluginConfig()
        ], $this->otherSettings);
    }

    /**
     * @param $columnName
     * @return string
     */
    static public function buildFilterId($columnName) {
        return 'filter-for-' . strtolower(preg_replace('%[^a-zA-Z0-9]+%i', '-', $columnName));
    }

    /**
     * @param string $operator
     * @param mixed $value
     * @return array
     * @throws ScaffoldException
     */
    public function buildConditionFromSearchRule($operator, $value) {
        if (!in_array($operator, $this->getOperators(), true)) {
            throw new ScaffoldException("Operator [$operator] is forbidden for filter [{$this->getColumnName()}]");
        }
        if (!is_array($value)) {
            $value = trim($value);
        }
         // resolve multivalues
        switch ($operator) {
            case static::OPERATOR_IN_ARRAY:
            case static::OPERATOR_NOT_IN_ARRAY:
                $value = preg_split('%\s*,\s*%', $value);
                break;
        }
        $this->validateValue($value, $operator);
        $value = $this->convertRuleValueToConditionValue($value, $operator);
        $dbOperator = $this->convertRuleOperatorToDbOperator($operator);
        $dataTypeConverter = $this->getValueDataTypeConverterForDb();
        // resolve column name replacement (it could be a DbExpr that concatenates many columns)
        if ($this->hasColumnNameReplacementForCondition()) {
            $colReplacement = $this->getColumnNameReplacementForCondition();
            if ($colReplacement instanceof DbExpr) {
                switch ($operator) {
                    case static::OPERATOR_IN_ARRAY:
                    case static::OPERATOR_NOT_IN_ARRAY:
                        $value = '(``' . implode('``,``', $value) . '``)';
                        break;
                    case static::OPERATOR_BETWEEN:
                    case static::OPERATOR_NOT_BETWEEN:
                        $value = "``{$value[0]}`` AND ``{$value[1]}``";
                        break;
                    case static::OPERATOR_IS_NULL:
                    case static::OPERATOR_IS_NOT_NULL:
                        $value = 'NULL';
                        break;
                    default:
                        $value = "``{$value}``";
                }
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
     * @param mixed $value
     * @param $operator
     * @throws ScaffoldException
     */
    protected function validateValue($value, $operator) {
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
                    $this->validateValue(trim($val), null);
                }
            }
            unset($val);
            if (empty($value)) {
                throw new ScaffoldException("Empty filter value is not allowed for [$operator] operator");
            }
            if (count($value) !== 2 && in_array($operator, [static::OPERATOR_BETWEEN, static::OPERATOR_NOT_BETWEEN], true)) {
                throw new ScaffoldException("There should be exactly 2 filter values for [$operator] operator");
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
        $validator = \Validator::make(['value' => $value], ['value' => $validatorRule]);
        if ($validator->fails()) {
            throw new ScaffoldException("Invalid value [$value] passed for filter column [{$this->getColumnName()}]");
        }
    }

    /**
     * @param mixed $value
     * @param string $operator
     * @return mixed
     */
    protected function convertRuleValueToConditionValue($value, $operator) {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = trim($this->convertRuleValueToConditionValue($val, null));
            }
            unset($val);
            if (
                $this->getDataType() === static::TYPE_STRING
                && in_array($operator, [static::OPERATOR_IN_ARRAY, static::OPERATOR_NOT_IN_ARRAY], true)
            ) {
                $value = '^(' . implode('|', array_map('preg_quote', $value)) . ')$';
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
                return '^' . preg_quote($value);
                break;
            case static::OPERATOR_ENDS_WITH:
            case static::OPERATOR_NOT_ENDS_WITH:
                return preg_quote($value) . '$';
                break;
            case static::OPERATOR_CONTAINS:
            case static::OPERATOR_NOT_CONTAINS:
                return preg_quote($value);
                break;
            case static::OPERATOR_EQUAL:
            case static::OPERATOR_NOT_EQUAL:
                if ($this->getDataType() === static::TYPE_STRING) {
                    return '^' . preg_quote($value) . '$';
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

    /**
     * @param string $operator
     * @return string
     */
    protected function convertRuleOperatorToDbOperator($operator) {
        switch ($operator) {
            case static::OPERATOR_EQUAL:
            case static::OPERATOR_IN_ARRAY:
                if ($this->getDataType() === static::TYPE_STRING) {
                    return static::$ruleOperatorToDbOperator[static::OPERATOR_CONTAINS]; //< for case-insensitive search
                }
                break;
            case static::OPERATOR_NOT_EQUAL:
            case static::OPERATOR_NOT_IN_ARRAY:
                if ($this->getDataType() === static::TYPE_STRING) {
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
    protected function getValueDataTypeConverterForDb() {
        switch ($this->getDataType()) {
            case static::TYPE_TIME:
                return '::time';
            case static::TYPE_DATE:
            case static::TYPE_TIMESTAMP:
                return '::date';
        }
        return '';
    }

}