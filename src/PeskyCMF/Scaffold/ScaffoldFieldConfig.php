<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbRelationConfig;
use Swayok\Html\Tag;

abstract class ScaffoldFieldConfig {

    /** @var null|ScaffoldActionConfig */
    protected $scaffoldActionConfig = null;

    /** @var string|null */
    protected $name = null;

    /** @var string */
    protected $type = null;
    const TYPE_STRING = DbColumnConfig::TYPE_STRING;
    const TYPE_DATE = DbColumnConfig::TYPE_DATE;
    const TYPE_TIME = DbColumnConfig::TYPE_TIME;
    const TYPE_DATETIME = DbColumnConfig::TYPE_TIMESTAMP;
    const TYPE_BOOL = DbColumnConfig::TYPE_BOOL;
    const TYPE_TEXT = DbColumnConfig::TYPE_TEXT;
    const TYPE_MULTILINE = 'multiline'; //< for non-html multiline text
    const TYPE_IMAGE = 'image';
    const TYPE_JSON = DbColumnConfig::TYPE_JSON;
    const TYPE_JSONB = DbColumnConfig::TYPE_JSONB;
    const TYPE_LINK = 'link';
    /**
     * @var null|string
     */
    protected $label = null;
    /**
     * Position
     * @var null|int
     */
    protected $position = null;

    /**
     * @var null|callable
     */
    protected $valueConverter = null;
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_TIME = 'H:i:s';
    const FORMAT_DATETIME = 'Y-m-d H:i:s';
    /**
     * @var bool
     */
    protected $isDbField = true;

    /**
     * @return $this
     */
    static public function create() {
        $classname = get_called_class();
        return new $classname();
    }

    /**
     * @return ScaffoldActionConfig|DataGridConfig|ItemDetailsConfig|FormConfig|null
     */
    public function getScaffoldActionConfig() {
        return $this->scaffoldActionConfig;
    }

    /**
     * @param ScaffoldActionConfig|null $scaffoldActionConfig
     * @return $this
     */
    public function setScaffoldActionConfig($scaffoldActionConfig) {
        $this->scaffoldActionConfig = $scaffoldActionConfig;
        return $this;
    }

    /**
     * @return DbColumnConfig
     * @throws ScaffoldFieldException
     */
    public function getTableColumnConfig() {
        return $this->getScaffoldActionConfig()->getModel()->getTableColumn($this->getName());
    }

    /**
     * @param $isDbField
     * @return $this
     */
    public function setIsDbField($isDbField) {
        $this->isDbField = $isDbField;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDbField() {
        return $this->isDbField;
    }

    /**
     * @return null|string
     * @throws ScaffoldFieldException
     */
    public function getName() {
        if (empty($this->name)) {
            throw new ScaffoldFieldException($this, 'Field name not provided');
        }
        return $this->name;
    }

    /**
     * @param null|string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        if (empty($this->type)) {
            $this->setType($this->getTableColumnConfig()->getType());
        }
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $default
     * @return string
     */
    public function getLabel($default = '') {
        return empty($this->label) ? $default : $this->label;
    }

    /**
     * @param null|string $label
     * @return $this
     */
    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function hasLabel() {
        return !empty($this->label);
    }

    /**
     * @return int|null
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return $this
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasValueConverter() {
        return !empty($this->valueConverter);
    }

    /**
     * @return callable|null
     */
    public function getValueConverter() {
        return $this->valueConverter;
    }

    /**
     * @param callable $valueConverter 
     *      - when $this->isDbField() === true: function ($value, DbColumnConfig $columnConfig, array $record, ScaffoldFieldConfig $fieldConfig) {}
     *      - when $this->isDbField() === false: function (array $record, ScaffoldFieldConfig $fieldConfig, ScaffolActionConfig $scaffoldActionConfig) {}
     * @return $this
     */
    public function setValueConverter(callable $valueConverter) {
        $this->valueConverter = $valueConverter;
        return $this;
    }

    /**
     * @param mixed $value
     * @param array $record
     * @param bool $ignoreValueConverter
     * @return mixed
     * @throws ScaffoldFieldException
     * @throws \PeskyORM\Exception\DbColumnConfigException
     */
    public function convertValue($value, array $record, $ignoreValueConverter = false) {
        $valueConverter = !$ignoreValueConverter ? $this->getValueConverter() : null;
        if (!empty($valueConverter)) {
            if ($this->isDbField()) {
                $value = call_user_func($valueConverter, $value, $this->getTableColumnConfig(), $record, $this);
            } else {
                $value = call_user_func($valueConverter, $record, $this, $this->getScaffoldActionConfig());
            }
        } else if (!empty($value) || is_bool($value)) {
            if ($this->getType() === static::TYPE_LINK) {
                return $this->buildLinkToExternalRecord($this->getTableColumnConfig(), $record);
            } else {
                return static::doDefaultValueConversionByType($value, $this->type);
            }
        }
        return $value;
    }

    /**
     * Default value converter by value type
     * @param mixed $value
     * @param string $type - one of static::TYPE_*
     * @return mixed
     */
    static public function doDefaultValueConversionByType($value, $type) {
        switch ($type) {
            case static::TYPE_DATETIME:
                return date(static::FORMAT_DATETIME, is_numeric($value) ? $value : strtotime($value));
            case static::TYPE_DATE:
                return date(static::FORMAT_DATE, is_numeric($value) ? $value : strtotime($value));
            case static::TYPE_TIME:
                return date(static::FORMAT_TIME, is_numeric($value) ? $value : strtotime($value));
            case static::TYPE_MULTILINE:
                return '<pre class="multiline-text">' . $value . '</pre>';
            case static::TYPE_JSON:
            case static::TYPE_JSONB:
                if (!is_array($value) && $value !== null) {
                    if (is_string($value) || is_numeric($value) || is_bool($value)) {
                        $value = json_decode($value, true);
                        if ($value === null && strtolower($value) !== 'null') {
                            $value = 'Failed to decode JSON: ' . print_r($value, true);
                        }
                    } else {
                        $value = 'Invalid value for JSON: ' . print_r($value, true);
                    }
                }
                return '<pre class="json-text">' . htmlentities(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                break;
        }
        return $value;
    }

    public function buildLinkToExternalRecord(DbColumnConfig $columnConfig, array $record, $linkLabel = null) {
        if (empty($record[$columnConfig->getName()])) {
            return '-';
        }
        $relationConfig = null;
        $relationAlias = null;
        foreach ($columnConfig->getRelations() as $alias => $relation) {
            if (in_array($relation->getType(), [DbRelationConfig::BELONGS_TO, DbRelationConfig::HAS_ONE], true)) {
                $relationConfig = $relation;
                $relationAlias = $alias;
                break;
            }
        }
        if (empty($relationConfig)) {
            throw new ScaffoldFieldException($this, "Column [{$columnConfig->getName()}] has no fitting relation");
        }
        if (empty($record[$relationAlias]) || empty($record[$relationAlias][$relationConfig->getForeignColumn()])) {
            return cmfTransGeneral('.item_details.field.no_relation');
        } else {
            if (empty($linkLabel)) {
                $displayField = $relationConfig->getDisplayField();
                if (empty($record[$relationAlias][$displayField])) {
                    $displayField = $relationConfig->getForeignColumn();
                }
                $linkLabel = $record[$relationAlias][$displayField];
            }
            return Tag::a($linkLabel)
                ->setHref(routeToCmfItemDetails($relationConfig->getForeignTable(), $record[$columnConfig->getName()]))
                ->build();
        }
    }

}