<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use Swayok\Html\Tag;

abstract class AbstractValueViewer {

    /** @var null|ScaffoldSectionConfig */
    protected $scaffoldSectionConfig;

    /** @var string|null */
    protected $name;

    /** @var string */
    protected $type;
    public const TYPE_STRING = Column::TYPE_STRING;
    public const TYPE_DATE = Column::TYPE_DATE;
    public const TYPE_TIME = Column::TYPE_TIME;
    public const TYPE_DATETIME = Column::TYPE_TIMESTAMP;
    public const TYPE_BOOL = Column::TYPE_BOOL;
    public const TYPE_TEXT = Column::TYPE_TEXT;
    public const TYPE_MULTILINE = 'multiline'; //< for non-html multiline text
    public const TYPE_IMAGE = 'image';
    public const TYPE_JSON = Column::TYPE_JSON;
    public const TYPE_JSONB = Column::TYPE_JSONB;
    public const TYPE_LINK = 'link';
    /**
     * @var null|string
     */
    protected $label;
    /**
     * Position
     * @var null|int
     */
    protected $position;

    /**
     * @var null|\Closure
     */
    protected $valueConverter;
    public const FORMAT_DATE = 'Y-m-d';
    public const FORMAT_TIME = 'H:i:s';
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';
    /** @var bool */
    protected $isLinkedToDbColumn = true;
    /** @var Relation */
    protected $relation;
    /** @var string */
    protected $relationColumn;
    /** @var string|null */
    protected $nameForTranslation;
    /** @var string|null */
    protected $tableNameForRouteToRelatedRecord;

    /**
     * @return $this
     */
    public static function create() {
        $classname = static::class;
        return new $classname();
    }

    /**
     * @return ScaffoldSectionConfig|DataGridConfig|ItemDetailsConfig|FormConfig
     */
    public function getScaffoldSectionConfig() {
        return $this->scaffoldSectionConfig;
    }

    /**
     * @param ScaffoldSectionConfig|null $scaffoldSectionConfig
     * @return $this
     */
    public function setScaffoldSectionConfig($scaffoldSectionConfig) {
        $this->scaffoldSectionConfig = $scaffoldSectionConfig;
        return $this;
    }

    /**
     * @return Column
     */
    public function getTableColumn() {
        if ($this->relation) {
            return $this->relation->getForeignTable()->getTableStructure()->getColumn($this->relationColumn);
        } else {
            $parts = static::splitComplexViewerName($this->getName());
            return $this->getScaffoldSectionConfig()->getTable()->getTableStructure()->getColumn($parts[0]);
        }
    }

    /**
     * @param Relation $relation
     * @param string $columnName
     * @return $this
     */
    public function setRelation(Relation $relation, $columnName) {
        $this->relation = $relation;
        $this->relationColumn = $columnName;
        return $this;
    }

    /**
     * @return Relation
     */
    public function getRelation() {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function hasRelation() {
        return !empty($this->relation);
    }

    /**
     * @return string
     */
    public function getRelationColumn() {
        return $this->relationColumn;
    }

    /**
     * Used only for value cells that make <a> tags to generate valid urls to related records
     * @param string $tableName
     * @return $this
     */
    public function setTableNameForRouteToRelatedRecord($tableName) {
        $this->tableNameForRouteToRelatedRecord = $tableName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTableNameForRouteToRelatedRecord() {
        return $this->tableNameForRouteToRelatedRecord;
    }

    /**
     * @param bool $isDbColumn
     * @return $this
     */
    public function setIsLinkedToDbColumn($isDbColumn) {
        $this->isLinkedToDbColumn = $isDbColumn;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLinkedToDbColumn() {
        return $this->isLinkedToDbColumn;
    }

    /**
     * @return null|string
     * @throws ValueViewerConfigException
     */
    public function getName() {
        if (empty($this->name)) {
            throw new ValueViewerConfigException($this, 'Value viewer name not provided');
        }
        return $this->name;
    }

    /**
     * Check if name is something like "column_name:key_name"
     * @param string $name
     * @return bool
     */
    final public static function isComplexViewerName($name) {
        return (bool)preg_match('%^[^:]+?:[^:]+?$%', $name);
    }

    /**
     * @param string $name - something like "column_name:key_name"
     * @return array - 0 - column name; 1 = key name or null
     */
    final public static function splitComplexViewerName($name) {
        $parts = explode(':', $name, 2);
        if (count($parts) === 1) {
            $parts[1] = null;
        }
        return $parts;
    }

    /**
     * @param null|string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        if ($this->nameForTranslation === null) {
            $this->nameForTranslation = rtrim($name, '[]');
        }
        return $this;
    }

    public function getNameForTranslation() {
        return $this->nameForTranslation;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setNameForTranslation($name) {
        $this->nameForTranslation = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        if (empty($this->type)) {
            if ($this->isLinkedToDbColumn() && !static::isComplexViewerName($this->getName())) {
                $this->setType($this->getTableColumn()->getType());
            } else {
                $this->setType(static::TYPE_STRING);
            }
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
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getLabel() {
        if ($this->label === null) {
            $this->label = $this->getScaffoldSectionConfig()->translate($this);
            if (!is_string($this->label)) {
                throw new \UnexpectedValueException(
                    "Label for value viewer '{$this->getName()}' must be a string. " . ucfirst(gettype($this->label)) . 'received'
                );
            }
        }
        return $this->label;
    }

    /**
     * @param null|string $label
     * @return $this
     */
    public function setLabel($label) {
        $this->label = $label;
        return $this;
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
     * @return \Closure|null
     */
    public function getValueConverter() {
        return $this->valueConverter;
    }

    /**
     * @param \Closure $valueConverter
     *      - when $this->isDbField() === true: function ($value, Column $columnConfig, array $record, AbstractValueViewer $valueViewer) { return 'value' }
     *      - when $this->isDbField() === false: function (array $record, AbstractValueViewer $valueViewer, ScaffoldSectionConfig $scaffoldSectionConfig) { return 'value' }
     * @return $this
     */
    public function setValueConverter(\Closure $valueConverter) {
        $this->valueConverter = $valueConverter;
        return $this;
    }

    /**
     * @param mixed $value
     * @param array $record
     * @param bool $ignoreValueConverter
     * @param null|string $relationKey
     * @return mixed
     */
    public function convertValue($value, array $record, $ignoreValueConverter = false, $relationKey = null) {
        $valueConverter = !$ignoreValueConverter ? $this->getValueConverter() : null;
        if (!empty($valueConverter)) {
            if ($this->isLinkedToDbColumn()) {
                $value = $valueConverter($value, $this->getTableColumn(), $record, $this);
            } else {
                $value = $valueConverter($record, $this, $this->getScaffoldSectionConfig());
            }
        } else if (!empty($value) || is_bool($value)) {
            if (is_resource($value)) {
                return '[resource]';
            } else if (
                $this->isLinkedToDbColumn()
                && (
                    $this->getTableColumn()->getType() === Column::TYPE_PASSWORD
                    || $this->getTableColumn()->isValuePrivate()
                )
            ) {
                // Protect passwords and private values (Column::isValuePrivate())
                // by default (only when no value converter provided)
                return '';
            } else if ($this->getType() === static::TYPE_LINK && $this->isLinkedToDbColumn()) {
                return $this->buildLinkToExternalRecord($this->getTableColumn(), $relationKey ? $record[$relationKey] : $record);
            } else {
                return $this->doDefaultValueConversionByType($value, $this->type, $relationKey ? $record[$relationKey] : $record);
            }
        }
        return $value;
    }

    /**
     * Default value converter by value type
     * @param mixed $value
     * @param string $type - one of static::TYPE_*
     * @param array $record
     * @return mixed
     */
    public function doDefaultValueConversionByType($value, $type, array $record) {
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
                return '<pre class="json-text">'
                        . htmlentities(stripslashes(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)))
                    . '</pre>';
                break;
        }
        return $value;
    }

    public function buildLinkToExternalRecord(
        Column $columnConfig,
        array $record,
        ?string $linkLabel = null,
        ?string $fallbackLabel = null
    ): string {
        if (empty($record[$columnConfig->getName()])) {
            return $fallbackLabel ?: '-';
        }
        $relationConfig = null;
        $relationAlias = null;
        $relationColumn = null;
        $relationData = [];
        if ($this->hasRelation()) {
            $relationConfig = $this->getRelation();
            $relationColumn = $relationConfig->getDisplayColumnName();
            $relationData = array_get($record, $relationConfig->getName(), $record);
        } else {
            foreach ($columnConfig->getRelations() as $relation) {
                if (in_array($relation->getType(), [Relation::BELONGS_TO, Relation::HAS_ONE], true)) {
                    $relationConfig = $relation;
                    $relationColumn = $relationConfig->getDisplayColumnName();
                    $relationData = array_get($record, $relationConfig->getName());
                    break;
                }
            }
        }
        if (!$relationConfig) {
            throw new ValueViewerConfigException($this, "Column [{$columnConfig->getName()}] has no fitting relation");
        }
        $relationPkColumn = $relationConfig->getForeignTable()->getPkColumnName();
        if (empty($relationData) || empty($relationData[$relationPkColumn])) {
            return $fallbackLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('field.no_relation');
        } else {
            if (empty($linkLabel)) {
                if ($relationColumn instanceof \Closure) {
                    $linkLabel = $relationColumn($relationData);
                } else {
                    if (empty($relationData[$relationColumn])) {
                        if ($relationConfig->getForeignTable()->getTableStructure()->hasColumn($relationColumn)) {
                            $linkLabel = $relationConfig
                                ->getForeignTable()
                                ->newRecord()
                                    ->enableTrustModeForDbData()
                                    ->fromData($relationData, true, false)
                                    ->getValue($relationColumn);
                        } else {
                            $relationColumn = $relationPkColumn;
                        }
                    }
                    /** @noinspection NotOptimalIfConditionsInspection */
                    if (empty($linkLabel)) {
                        $linkLabel = $relationData[$relationColumn];
                    }
                }
            }
            return Tag::a($linkLabel)
                ->setHref(routeToCmfItemDetails(
                    $this->getTableNameForRouteToRelatedRecord() ?: $relationConfig->getForeignTable()->getName(),
                    $relationData[$relationPkColumn]
                ))
                ->build();
        }
    }

}
