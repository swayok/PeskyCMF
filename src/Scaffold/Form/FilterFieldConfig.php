<?php


namespace PeskyCMF\Scaffold\Form;

class FilterFieldConfig {

    /**
     * @var null|string
     */
    protected $filterType = null;
    const FILTER_STRING = 'string';
    const FILTER_INTEGER = 'integer';
    const FILTER_FLOAT = 'float';
    const FILTER_MONEY = 'money';
    const FILTER_BOOL = 'bool';
    const FILTER_SELECT = 'select';
    const FILTER_MULTISELECT = 'multiselect';
    const FILTER_CHECKBOXES = 'checkboxes';
    const FILTER_RADIOS = 'radios';
    const FILTER_DATERANGE = 'daterange';
    /**
     * Equation operator for a filter
     * @var null|string
     */
    protected $filterEquationOperator = null;
    const FILTER_OPERATOR_EQUALS = 'equal';
    const FILTER_OPERATOR_CONTAINS = 'like';
    static protected $autoDetectEquationOperators = array(
        self::FILTER_OPERATOR_EQUALS => array(
            self::FILTER_CHECKBOXES,
            self::FILTER_RADIOS,
            self::FILTER_SELECT,
            self::FILTER_MULTISELECT,
            self::FILTER_BOOL,
            self::FILTER_INTEGER,
        ),
        self::FILTER_OPERATOR_CONTAINS => array(
            self::FILTER_STRING,
            self::FILTER_FLOAT,
            self::FILTER_MONEY,
        )
    );
    /**
     * value can be:
     * 1. <array> with keys:
     *      - "display_field" => field name to use as options values and labels
     *      - "conditions" => conditions for db query
     *    This variant will collect all values of model_table.display_field and make options from collected values.
     *    model_table = $this->model->table
     * 2. <array> with keys:
     *      - "relation" => relation alias from $this->relations
     *      - "display_field" => field name to use as options labels, options values are primary
     *      - "conditions" => conditions for db query
     *    This variant will collect all values of related_table.display_field and make options from collected values
     *    using related_table.display_field as lables and related_table.primary_key as values
     * @var null
     */
    protected $options = null;
    /**
     * Input tag attibutes
     * @var array
     */
    protected $attributes = array();
    const ATTR_DATA_TYPE = 'data-type';
    const ATTR_DATA_TYPE_HTML = 'wysiwyg';
    const ATTR_DATA_TYPE_INTEGER = 'integer';
    const ATTR_DATA_TYPE_FLOAT = 'float';
    const ATTR_DATA_TYPE_MONEY = 'money';

    /**
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws ScaffoldFieldException
     */
    public function setAttributes($attributes) {
        if (!is_array($attributes)) {
            throw new ScaffoldFieldException(null, '$attributes should be an array');
        }
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return null
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param null $options
     * @return $this
     * @throws ScaffoldFieldException
     */
    public function setOptions($options) {
        if (!is_array($options)) {
            throw new ScaffoldFieldException(null, '$options should be an array');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFilterType() {
        return $this->filterType;
    }

    /**
     * @param null|string $filterType
     * @return $this
     */
    public function setFilterType($filterType) {
        $this->filterType = $filterType;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFilterEquationOperator() {
        return $this->filterEquationOperator;
    }

    /**
     * @param null|string $filterEquationOperator
     * @return $this
     */
    public function setFilterEquationOperator($filterEquationOperator) {
        $this->filterEquationOperator = $filterEquationOperator;
        return $this;
    }

}