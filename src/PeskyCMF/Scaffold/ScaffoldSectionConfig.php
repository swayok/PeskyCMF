<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\MenuItem\CmfMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRedirectMenuItem;
use PeskyCMF\Scaffold\MenuItem\CmfRequestMenuItem;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

abstract class ScaffoldSectionConfig {
    /**
     * @var TableInterface
     */
    protected $table;
    /**
     * This scaffold config uses no db columns
     * @var bool
     */
    protected $thereIsNoDbColumns = false;
    /**
     * @var array
     */
    protected $fieldsConfigs = [];
    /**
     * Fields list
     * @var array|AbstractValueViewer[]
     */
    protected $valueViewers = [];
    /**
     * @var string
     */
    protected $title;
    /**
     * @var array
     */
    protected $relationsToRead = [];
    /**
     * @var null|\Closure
     */
    protected $defaultFieldRenderer;
    /**
     * @var null|\Closure
     */
    protected $rawRecordDataModifier;
    /**
     * Container width (percents)
     * @var int
     */
    protected $width = 100;
    /**
     * @var bool
     */
    protected $openInModal = true;
    /**
     * @var string|null - 'sm', 'md', 'lg', 'xl' | null - autodetect depending on $this->width
     */
    protected $modalSize;
    /**
     * @var \Closure|null
     */
    protected $toolbarItems;
    /**
     * @var array|\Closure
     */
    protected $specialConditions = [];
    /**
     * @var ScaffoldConfig
     */
    protected $scaffoldConfig;
    /**
     * @var array|\Closure
     */
    protected $dataToAddToRecord;
    /**
     * @var array|\Closure
     */
    protected $dataToSendToTemplate;
    /**
     * @var string
     */
    protected $template;
    /**
     * @var string
     */
    protected $jsInitiator;
    protected $isFinished = false;
    /** @var  bool */
    protected $allowRelationsInValueViewers = false;
    /**
     * Allow viewer names like "column_name:key_name" for json/jsonb DB columns
     * @var bool
     */
    protected $allowComplexValueViewerNames = false;
    /** @var array */
    protected $additionalColumnsToSelect = [];

    /**
     * @param TableInterface $table
     * @param ScaffoldConfig $scaffoldConfig
     * @return $this
     */
    static public function create(TableInterface $table, ScaffoldConfig $scaffoldConfig) {
        $class = static::class;
        return new $class($table, $scaffoldConfig);
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
    public function getScaffoldConfig() {
        return $this->scaffoldConfig;
    }

    public function setTemplate($template) {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     * @throws ScaffoldSectionConfigException
     */
    public function getTemplate() {
        if (empty($this->template)) {
            throw new ScaffoldSectionConfigException($this, 'Scaffold section view file not set');
        }
        return $this->template;
    }

    /**
     * Translate resource-related items (column names, input labels, etc.)
     * @param AbstractValueViewer|null $viewer
     * @param string $suffix
     * @param array $parameters
     * @return string
     */
    public function translate(AbstractValueViewer $viewer = null, $suffix = '', array $parameters = []) {
        if ($viewer) {
            return $this
                ->getScaffoldConfig()
                ->translateForViewer($this->getSectionTranslationsPrefix('value_viewer'), $viewer, $suffix, $parameters);
        } else {
            return $this
                ->getScaffoldConfig()
                ->translate($this->getSectionTranslationsPrefix(), $suffix, $parameters);
        }
    }

    /**
     * Translate general UI elements (button labels, tooltips, messages, etc..)
     * @param $path
     * @param array $parameters
     * @return mixed
     */
    public function translateGeneral($path, array $parameters = []) {
        $prefix = $this->getSectionTranslationsPrefix();
        $text = $this->getScaffoldConfig()->translate($prefix, $path, $parameters);
        if (preg_match('%\.' . preg_quote($prefix . '.' . $path, '%') . '$%', $text)) {
            $text = cmfTransGeneral($this->getSectionTranslationsPrefix('general') . '.' . $path, $parameters);
        }
        return $text;
    }

    /**
     * Prefix for this section's translations (ex: 'datagrid', 'item_details', 'form')
     * Will be used in $this->translate() and $this->translateGeneral()
     * @param bool $subtype - null, 'value_viewer', 'general'.
     *      - null - common translations for section. Path will be like 'resource.{section}.translation'
     *      - 'value_viewer' - translations for value viewers labels. It is preferred to nest
     *          value viewer labels deeper in section (ex: 'datagrid.columns') or outside of section (ex: 'columns').
     *          Path will be like 'resource.{section}.value_viewers.translation'
     *      - 'general' - translations for general UI elements ($this->translateGeneral()). Used only when there is
     *          no custom translation (example path: 'resource.{section}.translation') and system needs to get
     *          translation from cmfTransGeneral(). By default it should be same as for $subtype = null
     * @return string
     */
    abstract protected function getSectionTranslationsPrefix($subtype = null);

    /**
     * Disables any usage of TableStructure (validation, automatic field type guessing, etc)
     * @return $this
     */
    public function thereIsNoDbColumns() {
        $this->thereIsNoDbColumns = true;
        return $this;
    }

    /**
     * @param array $viewers
     * @return $this
     */
    protected function setValueViewers(array $viewers) {
        /** @var AbstractValueViewer|null $config */
        foreach ($viewers as $name => $config) {
            $this->normalizeAndAddValueViewer($name, $config);
        }
        return $this;
    }

    /**
     * @param string|int $name
     * @param \Closure|string|AbstractValueViewer|null $config
     * @return $this
     */
    protected function normalizeAndAddValueViewer($name, $config) {
        $valueConverter = null;
        if (is_int($name)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $name = $config;
            $config = null;
        } else if ($config instanceof \Closure) {
            $valueConverter = $config;
            $config = null;
        }
        $this->addValueViewer($name, $config, (bool)$valueConverter);
        if ($valueConverter) {
            if ($config->getType() === $config::TYPE_LINK) {
                $config->setValueConverter(
                    function ($value, Column $columnConfig, array $record, AbstractValueViewer $valueViewer) use ($valueConverter) {
                        $linkLabel = $valueConverter($value, $columnConfig, $record, $valueViewer);
                        if (!$linkLabel || empty($record[$columnConfig->getName()])) {
                            // there is no related record
                            return $linkLabel;
                        }
                        if (stripos($linkLabel, '<a ') !== false) {
                            // it is a link already
                            return $linkLabel;
                        }
                        return $valueViewer->buildLinkToExternalRecord(
                            $valueViewer->getTableColumn(),
                            $record,
                            $linkLabel,
                            $linkLabel
                        );
                    }
                );
            } else {
                $config->setValueConverter($valueConverter);
            }
        }
        return $this;
    }

    /**
     * @return AbstractValueViewer[]
     */
    public function getValueViewers() {
        return $this->valueViewers;
    }

    /**
     * Get only viewers that are linked to table columns defined in TableConfig class ($valueViewer->isDbColumn() === true)
     * @param bool $includeViewersForRelations - false: only vievers linked to main table's columns will be returned
     * @return AbstractValueViewer[]|DataGridColumn[]|FormInput[]|ValueCell[]
     */
    public function getViewersLinkedToDbColumns($includeViewersForRelations = false) {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if ($viewer->isLinkedToDbColumn() && ($includeViewersForRelations || !$viewer->hasRelation())) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * Get only viewers that are not linked to columns defined in TableConfig class ($valueViewer->isDbColumn() === false)
     * @return AbstractValueViewer[]|DataGridColumn[]|ValueCell[]|FormInput[]
     */
    public function getStandaloneViewers() {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if (!$viewer->isLinkedToDbColumn()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * Get only viewers that are linked to main table's relation
     * @return AbstractValueViewer[]|DataGridColumn[]|ValueCell[]|FormInput[]
     */
    public function getViewersForRelations() {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if ($viewer->isLinkedToDbColumn() && $viewer->hasRelation()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * @return AbstractValueViewer
     */
    abstract public function createValueViewer();

    /**
     * @param string $name
     * @return DataGridColumn|ValueCell|FormInput|AbstractValueViewer
     * @throws \InvalidArgumentException
     */
    public function getValueViewer($name) {
        if (!$this->hasValueViewer($name)) {
            throw new \InvalidArgumentException('Scaffold ' . get_class($this) . " has no viewer with name [$name]");
        }
        return $this->valueViewers[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasValueViewer($name) {
        return !empty($name) && !empty($this->valueViewers[$name]);
    }

    /**
     * @param string $name
     * @param null|AbstractValueViewer $viewer
     * @param bool $autodetectIfLinkedToDbColumn
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addValueViewer($name, ?AbstractValueViewer &$viewer = null, bool $autodetectIfLinkedToDbColumn = false) {
        $usesRelation = false;
        $hasColumnWithViewerName = $this->getTable()->getTableStructure()->hasColumn($name);
        $isAutocreated = !$viewer;
        if (
            !$this->thereIsNoDbColumns
            && !$hasColumnWithViewerName
            && (!$viewer || $viewer->isLinkedToDbColumn())
            && !$this->isValidComplexValueViewerName($name) //< $name is something like "column_name:key_name"
        ) {
            if ($this->allowRelationsInValueViewers) {
                [$relation, $relationColumnName] = $this->validateRelationValueViewerName($name, !$autodetectIfLinkedToDbColumn);
                $usesRelation = $relation && $relationColumnName;
            } else {
                throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column '{$name}'");
            }
        }
        if (!$viewer) {
            $viewer = $this->createValueViewer();
        }
        if ($this->thereIsNoDbColumns || ($autodetectIfLinkedToDbColumn && !$hasColumnWithViewerName)) {
            $viewer->setIsLinkedToDbColumn(false);
        }
        $viewer->setName($name);
        $viewer->setPosition($this->getNextValueViewerPosition($viewer));
        $viewer->setScaffoldSectionConfig($this);
        if ($usesRelation) {
            /** @noinspection PhpUndefinedVariableInspection */
            $viewer->setRelation($relation, $relationColumnName);
        }
        if (
            $isAutocreated
            && !($viewer instanceof FormInput)
            && $viewer->isLinkedToDbColumn()
            && $viewer->getTableColumn()->getForeignKeyRelation()
        ) {
            // this viewer references other record and shpuld be displayed as link
            $viewer->setType($viewer::TYPE_LINK);
        }
        $this->valueViewers[$name] = $viewer;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $throwErrorIfLocalColumnNotFound
     * - true: if $name is local column name (not relation or relation's column) - throw exception
     * - false: return [null, null] instead of throwing an excception
     * @return array - array(Relation, column_name)
     * @throws \InvalidArgumentException
     */
    protected function validateRelationValueViewerName(string $name, bool $throwErrorIfLocalColumnNotFound = true): array {
        $nameParts = explode('.', $name);
        $hasRelation = $this->getTable()->getTableStructure()->hasRelation($nameParts[0]);
        if (!$hasRelation && count($nameParts) === 1) {
            if ($throwErrorIfLocalColumnNotFound) {
                throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column '{$name}'");
            } else {
                return [null, null];
            }
        } else if (!$hasRelation) {
            throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no relation '{$nameParts[0]}'");
        }
        $relation = $this->getTable()->getTableStructure()->getRelation($nameParts[0]);
        if (count($nameParts) === 1) {
            $columnName = $relation->getForeignColumnName();
        } else {
            $columnName = end($nameParts); //< allows something like RelationName.i.column_name
            if (!$relation->getForeignTable()->getTableStructure()->hasColumn($columnName)) {
                throw new \InvalidArgumentException(
                    "Relation {$nameParts[0]} of table {$this->getTable()->getName()} has no column '{$columnName}'"
                );
            }
        }
        return [$relation, $columnName];
    }

    /**
     * Check if $name is complex column name like "column_name:key_name"
     * @param string $name
     * @return bool
     */
    protected function isValidComplexValueViewerName($name) {
        if ($this->allowComplexValueViewerNames && AbstractValueViewer::isComplexViewerName($name)) {
            [$colName, ] = AbstractValueViewer::splitComplexViewerName($name);
            if ($this->getTable()->getTableStructure()->hasColumn($colName)) {
                $type = $this->getTable()->getTableStructure()->getColumn($colName)->getType();
                return in_array($type, [Column::TYPE_JSON, Column::TYPE_JSONB], true);
            }
        }
        return false;
    }

    /**
     * @param AbstractValueViewer $viewer
     * @return int
     */
    protected function getNextValueViewerPosition(AbstractValueViewer $viewer) {
        return count($this->valueViewers);
    }

    /**
     * @return TableInterface
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @param string $default
     * @return string
     */
    public function getTitle($default = '') {
        return empty($this->title) ? $default : $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @param array $record
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function modifyRawRecordData(array $record) {
        if ($this->rawRecordDataModifier !== null) {
            $record = call_user_func($this->rawRecordDataModifier, $record, $this);
            if (!is_array($record)) {
                throw new \UnexpectedValueException('modifyRawDataRecord must return array');
            }
        }
        return $record;
    }

    /**
     * Modify record's data before it is processed by ScaffoldSectionConfig->prepareRecord()
     * Must return array
     * @param \Closure $modifier - function (array $record, ScaffoldSectionConfig $sectionConfig) { return $record }
     * @return $this
     */
    public function setRawRecordDataModifier(\Closure $modifier) {
        $this->rawRecordDataModifier = $modifier;
        return $this;
    }

    /**
     * @param array $record
     * @param array $virtualColumns - list of columns that are provided in TableStructure but marked as not existing in DB
     * @return array
     */
    public function prepareRecord(array $record, array $virtualColumns = []) {
        $pkKey = $this->getTable()->getPkColumnName();
        $permissionsAndServiceData = [
            '___create_allowed' => $this->isCreateAllowed(),
            '___delete_allowed' => (
                $this->isDeleteAllowed()
                && $this->getScaffoldConfig()->isRecordDeleteAllowed($record)
            ),
            '___edit_allowed' => (
                $this->isEditAllowed()
                && $this->getScaffoldConfig()->isRecordEditAllowed($record)
            ),
            '___details_allowed' => (
                $this->isDetailsViewerAllowed()
                && $this->getScaffoldConfig()->isRecordDetailsAllowed($record)
            ),
            '___cloning_allowed' => $this->isCloningAllowed(),
            '__modal' => $this->isUsingModal(),
            'DT_RowId' => 'item-' . preg_replace('%[^a-zA-Z0-9_-]+%', '-', $record[$pkKey]),
            '___pk_value' => $record[$pkKey]
        ];
        if (!empty($virtualColumns)) {
            $recordObj = $this->getTable()->newRecord()->enableTrustModeForDbData()->fromDbData($record);
            foreach ($virtualColumns as $virtualColumn) {
                $record[$virtualColumn] = $recordObj->getValue($virtualColumn);
            }
        }
        $record = $this->modifyRawRecordData($record);
        $customData = $this->getCustomDataForRecord($record);
        $valueViewers = $this->getValueViewers();
        // backup values
        $recordWithBackup = [];
        foreach ($record as $key => $value) {
            $recordWithBackup[$key] = $value;
            if (is_resource($value)) {
                $value = '[resource]';
            }
            $recordWithBackup['__' . $key] = $value;
        }
        foreach ($record as $key => $_) {
            if (!$this->hasValueViewer($key) && $this->getTable()->getTableStructure()->hasRelation($key)) {
                unset($recordWithBackup['__' . $key]);
                if ($this->getTable()->getTableStructure()->getRelation($key)->getType() === Relation::HAS_MANY) {
                    $recordWithBackup[$key] = [];
                    foreach ($record[$key] as $index => $relationData) {
                        $recordWithBackup[$key][$index] = $this->prepareRelatedRecord($key, $record, $index);
                    }
                } else {
                    $recordWithBackup[$key] = $this->prepareRelatedRecord($key, $record);
                }
                continue;
            }
            if (empty($valueViewers[$key])) {
                if ($key !== $pkKey) {
                    unset($recordWithBackup[$key], $recordWithBackup['__' . $key]);
                }
                continue;
            }
            $valueViewer = $valueViewers[$key];
            if (
                is_object($valueViewer)
                && method_exists($valueViewer, 'convertValue')
                && (
                    !method_exists($valueViewer, 'isVisible')
                    || $valueViewer->isVisible()
                )
            ) {
                $convertedValue = $valueViewer->convertValue($recordWithBackup[$key], $record);
                if ($valueViewer instanceof DataGridColumn) {
                    $recordWithBackup[$valueViewer::convertNameForDataTables($key)] = $convertedValue;
                } else {
                    $recordWithBackup[$key] = $convertedValue;
                }
            }
        }
        foreach ($valueViewers as $key => $valueViewer) {
            $safeKey = $valueViewer instanceof DataGridColumn ? $valueViewer::convertNameForDataTables($key) : $key;
            if (
                $valueViewer->isLinkedToDbColumn()
                && $valueViewer::isComplexViewerName($safeKey)
            ) {
                $colName = $valueViewer->getTableColumn()->getName();
                if (!array_has($recordWithBackup, $colName) && array_has($record, $colName)) {
                    $value = array_get($record, $colName);
                    if (is_string($value) && mb_strlen($value) >= 2) {
                        $value = json_decode($value, true);
                    }
                    array_set($recordWithBackup, $colName, $value);
                    array_set($recordWithBackup, '__' . $colName, $value);
                }
                if (
                    method_exists($valueViewer, 'convertValue')
                    && (
                        !method_exists($valueViewer, 'isVisible')
                        || $valueViewer->isVisible()
                    )
                ) {
                    $key = implode('.', $valueViewer::splitComplexViewerName($key));
                    $convertedValue = $valueViewer->convertValue(array_get($recordWithBackup, $key), $record);
                    array_set($recordWithBackup, $safeKey, $convertedValue);
                }
            } else if (
                !$valueViewer->isLinkedToDbColumn()
                && !array_key_exists($key, $recordWithBackup)
                && method_exists($valueViewer, 'convertValue')
                && (
                    !method_exists($valueViewer, 'isVisible')
                    || $valueViewer->isVisible()
                )
            ) {
                $convertedValue = $valueViewer->convertValue(null, $record);
                array_set($recordWithBackup, $safeKey, $convertedValue);
            }
        }
        if (!empty($customData) && is_array($customData)) {
            $recordWithBackup = array_merge($recordWithBackup, $customData);
        }
        $recordWithBackup = array_merge($recordWithBackup, $permissionsAndServiceData);
        return $recordWithBackup;
    }

    /**
     * Process related record's data by viewers attached to it
     * @param string $relationName
     * @param array $relationRecordData
     * @param null|string $index - string: passed for HAS_MANY relation | null: for relations other than HAS_MANY
     * @return array
     */
    protected function prepareRelatedRecord($relationName, array $recordData, $index = null) {
        $recordWithBackup = $recordData[$relationName];
        $valueViewers = $this->getViewersForRelations();
        foreach ($recordData[$relationName] as $columnName => $value) {
            $viewerName = $relationName . '.' . ($index === null ? '' : $index . '.') . $columnName;
            if (
                array_key_exists($viewerName, $valueViewers)
                && $valueViewers[$viewerName]->getRelation()->getName() === $relationName
            ) {
                $recordWithBackup[$columnName] = $value;
                $recordWithBackup['__' . $columnName] = is_resource($value) ? '[resource]' : $value;
                $valueViewer = $valueViewers[$viewerName];
                if (
                    is_object($valueViewer)
                    && method_exists($valueViewer, 'convertValue')
                    && (
                        !method_exists($valueViewer, 'isVisible')
                        || $valueViewer->isVisible()
                    )
                ) {
                    $recordWithBackup[$columnName] = $valueViewer->convertValue(
                        $value,
                        $recordData,
                        false,
                        $relationName
                    );
                } else if (is_resource($value)) {
                    $recordWithBackup[$columnName] = '[resource]';
                }
            } else if (is_resource($value)) {
                $recordWithBackup[$columnName] = '[resource]';
            }
        }
        return $recordWithBackup;
    }

    /**
     * @param array|\Closure $arrayOrClosure
     *      - \Closure: funciton (array $record, ScaffoldSectionConfig $scaffoldSectionConfig) { return []; }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDataToAddToRecord($arrayOrClosure) {
        if (!is_array($arrayOrClosure) && !($arrayOrClosure instanceof \Closure)) {
            throw new \InvalidArgumentException('$arrayOrClosure argument must be an array or \Closure');
        }
        $this->dataToAddToRecord = $arrayOrClosure;
        return $this;
    }

    /**
     * @param array $record
     * @return array|mixed
     */
    public function getCustomDataForRecord(array $record) {
        if (empty($this->dataToAddToRecord)) {
            return [];
        } else if ($this->dataToAddToRecord instanceof \Closure) {
            return call_user_func($this->dataToAddToRecord, $record, $this);
        } else {
            return $this->dataToAddToRecord;
        }
    }

    /**
     * @param array|\Closure $arrayOrClosure - function (ScaffoldSectionConfig $sectionConfig) { return [] }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function sendDataToTemplate($arrayOrClosure) {
        if (!is_array($arrayOrClosure) && !($arrayOrClosure instanceof \Closure)) {
            throw new \InvalidArgumentException('$arrayOrClosure argument must be an array or \Closure');
        }
        $this->dataToSendToTemplate = $arrayOrClosure;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getAdditionalDataForTemplate() {
        if (empty($this->dataToSendToTemplate)) {
            return [];
        } else if ($this->dataToSendToTemplate instanceof \Closure) {
            return call_user_func($this->dataToSendToTemplate, $this);
        } else {
            return $this->dataToSendToTemplate;
        }
    }

    /**
     * @return array
     */
    public function getRelationsToRead() {
        return $this->relationsToRead;
    }

    /**
     * @return bool
     */
    public function hasRelationsToRead() {
        return !empty($this->relationsToRead);
    }

    /**
     * Set relations to join.
     * Notes:
     * - For data grid you need to provide key-value pairs in same format as for columns selection:
     *   ['Relation' => ['col1', 'col2', ...]]. More info: @see AbstractSelect::columns()
     *   HAS MANY relations are forbidden.
     * - For item edit form and item details view you need to provide only names of the relations you need to read
     *   with the item. All types of relations allowed but there is no automatic possibility to get deeper relations
     * @param array $relationNames
     * @return $this
     */
    public function readRelations(array $relationNames) {
        $this->relationsToRead = $relationNames;
        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getDefaultValueRenderer() {
        if (!empty($this->defaultFieldRenderer)) {
            return $this->defaultFieldRenderer;
        } else {
            $this->setDefaultValueRenderer(function (RenderableValueViewer $valueViewer, $sectionConfig, array $dataForView) {
                $rendererConfig = $this->createValueRenderer()->setData($dataForView);
                $this->configureDefaultValueRenderer($rendererConfig, $valueViewer);
                return $rendererConfig;
            });
            return $this->defaultFieldRenderer;
        }
    }

    /**
     * @return ValueRenderer
     */
    abstract protected function createValueRenderer();

    /**
     * @param ValueRenderer $renderer
     * @param RenderableValueViewer $valueViewer
     */
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        RenderableValueViewer $valueViewer
    ) {
        $valueViewer->configureDefaultRenderer($renderer);
    }

    /**
     * @param \Closure $defaultFieldRenderer
     * @return $this
     */
    public function setDefaultValueRenderer(\Closure $defaultFieldRenderer) {
        $this->defaultFieldRenderer = $defaultFieldRenderer;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDefaultValueRenderer() {
        return !empty($this->defaultFieldRenderer);
    }

    /**
     * @return string[]
     * @throws \UnexpectedValueException
     */
    public function getToolbarItems() {
        if (empty($this->toolbarItems)) {
            return [];
        }
        $toolbarItems = call_user_func($this->toolbarItems, $this);
        if (!is_array($toolbarItems)) {
            throw new \UnexpectedValueException(get_class($this) . '->toolbarItems closure must return an array');
        }
        /**
         * @var array $toolbarItems
         * @var Tag|string $item
         */
        foreach ($toolbarItems as &$item) {
            if (is_object($item)) {
                /** @noinspection MissingOrEmptyGroupStatementInspection */
                /** @noinspection PhpStatementHasEmptyBodyInspection */
                if ($item instanceof CmfMenuItem) {
                    // do nothing
                } else if (method_exists($item, 'build')) {
                    $item = $item->build();
                } else if (method_exists($item, '__toString')) {
                    $item = (string) $item;
                } else {
                    throw new \UnexpectedValueException(
                        get_class($this) . '->toolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } else if (!is_string($item)) {
                throw new \UnexpectedValueException(
                    get_class($this) . '->toolbarItems: array may contain only strings and objects with build() or __toString() methods'
                );
            }
        }
        return $toolbarItems;
    }

    /**
     * Note: common actions: 'details', 'edit', 'clone', 'delete', 'create' will be added automatically
     * before custom menu items. You can manipulate positioning of common items using actions names as keys
     * Example: 'details' => null.
     * @param \Closure $callback - function (ScaffoldSectionConfig $scaffoldSectionConfig) { return []; }
     * Callback must return an array.
     * Array may contain only strings, Tag class instances, or any object with build() or __toString() method
     * Note: you can change positions of default toolbar items ('create', 'bulk_actions') using item names as a key in
     * resulting array of items while leaving value to be null:
     * function ($scaffoldSectionConfig) { return [$myItem, 'create' => null, $otherItem, 'bulk_actions' => 'null'] }
     * Examples:
     * 1. ToolbarItem:
     * a. Preferred usage:
     * - CmfMenuItem::redirect(cmfRoute('route', [], false))
            ->setTitle($this->translate('action.details'))
     * - CmfMenuItem::request(cmfRoute('route', [], false), 'delete')
            ->setTitle($this->translate('action.delete'))
            ->setConfirm($this->translate('message.delete_confirm'));
     * - CmfMenuItem::bulkActionOnSelectedRows(cmfRoute('route', [], false), 'delete')
            ->setTitle($this->translate('action.delete'))
            ->setPrimaryKeyColumnName('id')
            ->setConfirm($this->translate('message.delete_confirm'));
     * - CmfMenuItem::bulkActionOnFilteredRows(cmfRoute('route', [], false), 'delete')
            ->setTitle($this->translate('action.delete'))
            ->setConfirm($this->translate('message.delete_confirm'));
     * b. Alternative usage:
     * - call some url via ajax and then run "callback(json)"
         Tag::a()
             ->setContent(trans('path.to.translation'))
             ->setClass('btn btn-warning')
             ->setDataAttr('action', 'request')
             ->setDataAttr('url', cmfRoute('route', [], false))
             ->setDataAttr('method', 'put')
             ->setDataAttr('data', 'id={{= it.id }}')
             ->setDataAttr('on-success', 'callbackFuncitonName')
             //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
             //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
             ->setHref('javascript: void(0)');
     * - redirect to other url
         Tag::a()
             ->setContent(trans('path.to.translation'))
             ->setClass('btn btn-warning')
             ->setHref(cmfRoute('route', [], false))
             ->setTarget('_blank')
     * c. ONLY FOR DATA GRIDS:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
         Tag::a()
             ->setContent(trans('path.to.translation'))
             //^ you can use ':count' in label to insert selected items count
             ->setDataAttr('action', 'bulk-selected')
             ->setDataAttr('confirm', trans('path.to.translation'))
             //^ confirm action before sending request to server
             ->setDataAttr('url', cmfRoute('route', [], false))
             ->setDataAttr('method', 'delete')
             //^ can be 'post', 'put', 'delete' depending on action type
             ->setDataAttr('id-field', 'id')
             //^ for bulk actions on selected items id field name to use to get rows ids, default: 'id'
             ->setDataAttr('on-success', 'callbackFuncitonName')
             //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
             //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
             ->setDataAttr('response-type', 'json')
             //^ one of: json, html, xml. Default: 'json'
             ->setHref('javascript: void(0)');
     * Values will be received in the 'ids' key of the request as array
     * - call some url via ajax passing filter conditions and then run "callback(json)"
         Tag::a()
             ->setContent(trans('path.to.translation'))
             //^ you can use ':count' in label to insert filtered items count
             ->setDataAttr('action', 'bulk-filtered')
             ->setDataAttr('confirm', trans('path.to.translation'))
             //^ confirm action before sending request to server
             ->setDataAttr('url', cmfRoute('route', [], false))
             ->setDataAttr('method', 'put')
             //^ can be 'post', 'put', 'delete' depending on action type
             ->setDataAttr('on-success', 'callbackFuncitonName')
             //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
             //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
             ->setDataAttr('response-type', 'json')
             //^ one of: json, html, xml. Default: 'json'
             ->setHref('javascript: void(0)');
     * - bulk actions with custom on-click handler
         Tag::button()
             ->setContent(trans('path.to.translation'))
             //^ you can use ':count' in label to insert selected items count or filtered items count
             //^ depending on 'data-type' attribute
             ->setClass('btn btn-success')
             ->setDataAttr('type', 'bulk-selected')
             //^ 'bulk-selected' or 'bulk-filtered'
             ->setDataAttr('url', cmfRoute('route', [], false))
             ->setDataAttr('id-field', 'id')
             //^ id field name to use to get rows ids, default: 'id'
             ->setOnClick('someFunction(this)')
             //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * 2. List of toolbar items:
     *      [
     *          ToolbarItem1,
     *          ToolbarItem2,
     *          'delete' => null
     *      ]
     * @return $this
     */
    public function setToolbarItems(\Closure $callback) {
        $this->toolbarItems = $callback;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->getScaffoldConfig()->isCreateAllowed();
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->getScaffoldConfig()->isEditAllowed();
    }

    /**
     * @return boolean
     */
    public function isCloningAllowed() {
        return $this->getScaffoldConfig()->isCloningAllowed();
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->getScaffoldConfig()->isDeleteAllowed();
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->getScaffoldConfig()->isDetailsViewerAllowed();
    }

    /**
     * @return bool
     */
    public function hasSpecialConditions() {
        return !empty($this->specialConditions);
    }

    /**
     * @return array
     */
    public function getSpecialConditions() {
        return $this->specialConditions instanceof \Closure
            ? call_user_func($this->specialConditions, $this)
            : $this->specialConditions;
    }

    /**
     * @param array|\Closure $specialConditions - array or function (ScaffoldSectionConfig $scaffoldSectionConfig) {}
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setSpecialConditions($specialConditions) {
        if (!is_array($specialConditions) && !($specialConditions instanceof \Closure)) {
            throw new \InvalidArgumentException('$specialConditions argument must be an array or \Closure');
        }
        $this->specialConditions = $specialConditions;
        return $this;
    }

    /**
     * @return string
     */
    public function getWidth() {
        return min($this->width, 100);
    }

    /**
     * @param $percents
     * @return $this
     */
    public function setWidth($percents) {
        $this->width = $percents;
        return $this;
    }

    /**
     * Get css classes for content container.
     * Uses witdh to collect col-??-?? and col-??-offset?? classes
     * @return string
     */
    public function getCssClassesForContainer() {
        $colsXl = $this->getWidth() >= 100 ? 12 : ceil(12 * ($this->getWidth() / 100));
        $colsXlLeft = floor((12 - $colsXl) / 2);
        $colsLg = $colsXl >= 10 ? 12 : $colsXl + 2;
        $colsLgLeft = floor((12 - $colsLg) / 2);
        return "col-xs-12 col-xl-{$colsXl} col-lg-{$colsLg} col-xl-offset-{$colsXlLeft} col-lg-offset-{$colsLgLeft}";
    }

    /**
     * @param bool $isEnabled
     * @param string|null $size - 'sm', 'md', 'lg', 'xl' | null - autodetect depending on $this->width
     * @return $this
     */
    public function setModalConfig(bool $isEnabled = true, ?string $size = null) {
        $this->openInModal = $isEnabled;
        $this->modalSize = in_array($size, ['sm', 'md', 'lg', 'xl']) ? $size : null;
        return $this;
    }

    public function getModalSize() {
        /** @noinspection NestedTernaryOperatorInspection */
        return $this->modalSize ?: ($this->getWidth() > 60 ? 'lg' : 'md');
    }

    public function isUsingModal() {
        return $this->openInModal;
    }

    /**
     * @return string
     */
    public function getJsInitiator() {
        return $this->jsInitiator;
    }

    /**
     * @return bool
     */
    public function hasJsInitiator() {
        return !empty($this->jsInitiator);
    }

    /**
     * For data grids:
     * - JS function will be called instead of ScaffoldDataGridHelper.init()
     * - JS function will receive 2 arguments: dataGridSelector, dataTablesConfig
     * - You may want to call ScaffoldDataGridHelper.init(dataGridSelector, dataTablesConfig) in this
     *   function to continue normal initialization
     * - Function MUST return DataTables object
     * For forms:
     * - JS function will be called before default form init (see FormHelper.initForm)
     * - JS function will receive 3 arguments: $form, $formContainer, onSubmitSuccess
     * - JS function context is form's DOM element
     * - If JS function returns false - default init will not proceed
     * For item details viewer:
     * - JS function will be called after data was loaded and content rendered
     * - JS function will receive 1 argument depending on situation: $content or $modal
     * @param string $jsFunctionName - name of existing JS function without braces, for example: 'initSomething' or 'SomeVar.init'
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setJsInitiator($jsFunctionName) {
        if (!is_string($jsFunctionName) && !preg_match('%^[$_a-zA-Z][a-zA-Z0-9_.\[\]\'"]+$%', $jsFunctionName)) {
            throw new \InvalidArgumentException("Invalid JavaScript funciton name: [$jsFunctionName]");
        }
        $this->jsInitiator = $jsFunctionName;
        return $this;
    }

    /**
     * Called before scaffold template rendering
     */
    public function beforeRender() {

    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \BadMethodCallException
     */
    public function finish() {
        if ($this->isFinished) {
            throw new \BadMethodCallException('Attempt to call ' . get_class($this) . '->finish() twice');
        } else {
            $this->isFinished = true;
        }
    }

    /**
     * @param array $columnNames
     * @return $this
     */
    public function setAdditionalColumnsToSelect(...$columnNames) {
        if (count($columnNames) && is_array($columnNames[0])) {
            $columnNames = $columnNames[0];
        }
        $this->additionalColumnsToSelect = $columnNames;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalColumnsToSelect() {
        return $this->additionalColumnsToSelect;
    }

    /**
     * @param string $section
     * @return CmfRedirectMenuItem
     */
    public function getItemEditMenuItem($section = 'toolbar') {
        return CmfMenuItem::redirect(routeToCmfItemEditForm($this->getScaffoldConfig()->getResourceName(), '{{= it.___pk_value}}', false, null, true))
            ->setTitle($this->translateGeneral($section . '.edit_item'))
            ->setIconClasses('glyphicon glyphicon-edit')
            ->setIconColorClass('text-green')
            ->setButtonClasses('btn btn-success item-edit')
            ->setAccessProvider(function () {
                return $this->isEditAllowed();
            })
            ->setConditionToShow('it.___edit_allowed');
    }

    /**
     * @param string $section
     * @return CmfRedirectMenuItem
     */
    public function getItemCloneMenuItem($section = 'toolbar') {
        return CmfMenuItem::redirect(routeToCmfItemCloneForm($this->getScaffoldConfig()->getResourceName(), '{{= it.___pk_value}}', false, null, true))
            ->setTitle($this->translateGeneral($section . '.clone_item'))
            ->setIconClasses('fa fa-copy')
            ->setIconColorClass('text-primary')
            ->setButtonClasses('btn btn-primary item-clone')
            ->setAccessProvider(function () {
                return $this->isCloningAllowed();
            })
            ->setConditionToShow('it.___cloning_allowed');
    }

    /**
     * @param string $section
     * @return CmfRedirectMenuItem
     */
    public function getItemCreateMenuItem($section = 'toolbar') {
        return CmfMenuItem::redirect(routeToCmfItemAddForm($this->getScaffoldConfig()->getResourceName(), [], false, null, true))
            ->setTitle($this->translateGeneral($section . '.create_item'))
            ->setIconClasses('fa fa-file-o')
            ->setIconColorClass('text-primary')
            ->setButtonClasses('btn btn-primary item-add')
            ->setAccessProvider(function () {
                return $this->isCreateAllowed();
            })
            ->setConditionToShow('it.___create_allowed');

    }

    /**
     * @param string $section
     * @return CmfRedirectMenuItem
     */
    public function getItemDetailsMenuItem($section = 'toolbar') {
        return CmfMenuItem::redirect(routeToCmfItemDetails($this->getScaffoldConfig()->getResourceName(), '{{= it.___pk_value}}', false, null, true))
            ->setTitle($this->translateGeneral($section . '.view_item'))
            ->setIconClasses('glyphicon glyphicon-info-sign')
            ->setIconColorClass('text-light-blue')
            ->setButtonClasses('btn btn-info')
            ->setAccessProvider(function () {
                return $this->isDetailsViewerAllowed();
            })
            ->setConditionToShow('it.___details_allowed');
    }

    /**
     * @param string $section
     * @return CmfRequestMenuItem
     */
    public function getItemDeleteMenuItem($section = 'toolbar') {
        return CmfMenuItem::request(routeToCmfItemDelete($this->getScaffoldConfig()->getResourceName(), '{{= it.___pk_value}}', false, null, true), 'delete')
            ->setTitle($this->translateGeneral($section . '.delete_item'))
            ->setIconClasses('glyphicon glyphicon-trash')
            ->setIconColorClass('text-red')
            ->setButtonClasses('btn btn-danger item-delete')
            ->setBlockDataGrid(true)
            ->setConfirm($this->translateGeneral('message.delete_item_confirm'))
            ->setAccessProvider(function () {
                return $this->isDeleteAllowed();
            })
            ->setConditionToShow('it.___delete_allowed');
    }

}
