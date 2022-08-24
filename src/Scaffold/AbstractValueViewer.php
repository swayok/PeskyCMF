<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Support\Arr;
use PeskyCMF\CmfUrl;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use Swayok\Html\Tag;

abstract class AbstractValueViewer
{
    
    public const TYPE_STRING = Column::TYPE_STRING;
    public const TYPE_DATE = Column::TYPE_DATE;
    public const TYPE_TIME = Column::TYPE_TIME;
    public const TYPE_DATETIME = Column::TYPE_TIMESTAMP;
    public const TYPE_BOOL = Column::TYPE_BOOL;
    public const TYPE_TEXT = Column::TYPE_TEXT;
    public const TYPE_MULTILINE = 'multiline'; //< for non-html multiline text
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_JSON = Column::TYPE_JSON;
    public const TYPE_JSONB = Column::TYPE_JSONB;
    public const TYPE_LINK = 'link';
    
    public const FORMAT_DATE = 'Y-m-d';
    public const FORMAT_TIME = 'H:i:s';
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';
    
    protected ?ScaffoldSectionConfig $scaffoldSectionConfig = null;
    
    protected ?string $name = null;
    protected ?string $type = null;
    protected ?string $label = null;
    protected ?int $position = null;
    protected ?string $nameForTranslation = null;
    protected bool $isLinkedToDbColumn = true;
    
    protected ?\Closure $valueConverter = null;
    
    protected ?Relation $relation = null;
    protected ?string $relationColumn = null;
    protected ?string $tableNameForRouteToRelatedRecord = null;
    
    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }
    
    /**
     * @return ScaffoldSectionConfig|DataGridConfig|ItemDetailsConfig|FormConfig
     */
    public function getScaffoldSectionConfig(): ScaffoldSectionConfig
    {
        return $this->scaffoldSectionConfig;
    }
    
    /**
     * @return static
     */
    public function setScaffoldSectionConfig(ScaffoldSectionConfig $scaffoldSectionConfig)
    {
        $this->scaffoldSectionConfig = $scaffoldSectionConfig;
        return $this;
    }
    
    public function getCmfConfig(): CmfConfig
    {
        return $this->getScaffoldSectionConfig()->getCmfConfig();
    }
    
    public function getTableColumn(): Column
    {
        if ($this->relation) {
            return $this->relation->getForeignTable()->getTableStructure()->getColumn($this->relationColumn);
        } else {
            $parts = static::splitComplexViewerName($this->getName());
            return $this->getScaffoldSectionConfig()->getTable()->getTableStructure()->getColumn($parts[0]);
        }
    }
    
    /**
     * @return static
     */
    public function setRelation(Relation $relation, string $columnName)
    {
        $this->relation = $relation;
        $this->relationColumn = $columnName;
        return $this;
    }
    
    public function getRelation(): ?Relation
    {
        return $this->relation;
    }
    
    public function hasRelation(): bool
    {
        return !empty($this->relation);
    }
    
    public function getRelationColumn(): ?string
    {
        return $this->relationColumn;
    }
    
    /**
     * Used only for value cells that make <a> tags to generate valid urls to related records
     * @return static
     */
    public function setTableNameForRouteToRelatedRecord(string $tableName)
    {
        $this->tableNameForRouteToRelatedRecord = $tableName;
        return $this;
    }
    
    public function getTableNameForRouteToRelatedRecord(): ?string
    {
        return $this->tableNameForRouteToRelatedRecord;
    }
    
    /**
     * @return static
     */
    public function setIsLinkedToDbColumn(bool $isDbColumn)
    {
        $this->isLinkedToDbColumn = $isDbColumn;
        return $this;
    }
    
    public function isLinkedToDbColumn(): bool
    {
        return $this->isLinkedToDbColumn;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            throw new \UnexpectedValueException(static::class . '->name not provided');
        }
        return $this->name;
    }
    
    /**
     * Check if name is something like "column_name:key_name"
     */
    final public static function isComplexViewerName(string $name): bool
    {
        return (bool)preg_match('%^[^:]+?:[^:]+?$%', $name);
    }
    
    /**
     * @param string $name - something like "column_name:key_name"
     * @return array - 0 - column name; 1 = key name or null
     */
    final public static function splitComplexViewerName(string $name): array
    {
        $parts = explode(':', $name, 2);
        if (count($parts) === 1) {
            $parts[1] = null;
        }
        return $parts;
    }
    
    /**
     * @return static
     */
    public function setName(string $name)
    {
        $this->name = $name;
        if ($this->nameForTranslation === null) {
            $this->nameForTranslation = rtrim($name, '[]');
        }
        return $this;
    }
    
    public function getNameForTranslation(): ?string
    {
        return $this->nameForTranslation;
    }
    
    /**
     * @return static
     */
    public function setNameForTranslation(string $name)
    {
        $this->nameForTranslation = $name;
        return $this;
    }
    
    public function getType(): string
    {
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
     * @return static
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getLabel(): string
    {
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
     * @return static
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }
    
    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    /**
     * @return static
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
        return $this;
    }
    
    public function hasValueConverter(): bool
    {
        return !empty($this->valueConverter);
    }
    
    public function getValueConverter(): ?\Closure
    {
        return $this->valueConverter;
    }
    
    /**
     * @param \Closure $valueConverter
     *      - when $this->isDbField() === true: function ($value, Column $columnConfig, array $record, AbstractValueViewer $valueViewer) { return 'value' }
     *      - when $this->isDbField() === false: function (array $record, AbstractValueViewer $valueViewer, ScaffoldSectionConfig $scaffoldSectionConfig) { return 'value' }
     * @return static
     */
    public function setValueConverter(\Closure $valueConverter)
    {
        $this->valueConverter = $valueConverter;
        return $this;
    }
    
    /**
     * @param mixed $value
     * @param array $record
     * @param bool $ignoreValueConverter
     * @param string|null $relationKey
     * @return mixed
     */
    public function convertValue(
        $value,
        array $record,
        bool $ignoreValueConverter = false,
        ?string $relationKey = null
    ) {
        $valueConverter = !$ignoreValueConverter ? $this->getValueConverter() : null;
        if (!empty($valueConverter)) {
            if ($this->isLinkedToDbColumn()) {
                $value = $valueConverter($value, $this->getTableColumn(), $record, $this);
            } else {
                $value = $valueConverter($record, $this, $this->getScaffoldSectionConfig());
            }
        } elseif (!empty($value) || is_bool($value)) {
            if (is_resource($value)) {
                return '[resource]';
            } elseif (
                $this->isLinkedToDbColumn()
                && (
                    $this->getTableColumn()->getType() === Column::TYPE_PASSWORD
                    || $this->getTableColumn()->isValuePrivate()
                )
            ) {
                // Protect passwords and private values (Column::isValuePrivate())
                // by default (only when no value converter provided)
                return '';
            } elseif ($this->getType() === static::TYPE_LINK && $this->isLinkedToDbColumn()) {
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
    public function doDefaultValueConversionByType($value, string $type, array $record)
    {
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
                        $json = json_decode($value, true);
                        if ($json === null && strtolower($value) !== 'null') {
                            $value = 'Failed to decode JSON: ' . print_r($value, true);
                        } else {
                            $value = $json;
                        }
                    } else {
                        $value = 'Invalid value for JSON: ' . print_r($value, true);
                    }
                }
                return '<pre class="json-text">'
                    . htmlentities(stripslashes(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)))
                    . '</pre>';
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
        $relationColumn = null;
        $relationData = [];
        if ($this->hasRelation()) {
            $relationConfig = $this->getRelation();
            $relationColumn = $relationConfig->getDisplayColumnName();
            $relationData = Arr::get($record, $relationConfig->getName(), $record);
        } else {
            foreach ($columnConfig->getRelations() as $relation) {
                if (in_array($relation->getType(), [Relation::BELONGS_TO, Relation::HAS_ONE], true)) {
                    $relationConfig = $relation;
                    $relationColumn = $relationConfig->getDisplayColumnName();
                    $relationData = Arr::get($record, $relationConfig->getName());
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
                    if (empty($linkLabel)) {
                        $linkLabel = $relationData[$relationColumn];
                    }
                }
            }
            return Tag::a($linkLabel)
                ->setHref(
                    CmfUrl::toItemDetails(
                        $this->getTableNameForRouteToRelatedRecord() ?: $relationConfig->getForeignTable()->getName(),
                        $relationData[$relationPkColumn],
                        false,
                        $this->getCmfConfig()
                    )
                )
                ->build();
        }
    }
    
}
