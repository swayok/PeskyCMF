<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Support\Arr;
use PeskyCMF\Config\CmfConfig;
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

abstract class ScaffoldSectionConfig
{
    
    protected TableInterface $table;
    protected ScaffoldConfig $scaffoldConfig;
    
    protected ?string $title = null;
    
    /**
     * This scaffold config uses no db columns
     */
    protected bool $thereIsNoDbColumns = false;
    
    /**
     * Fields list
     * @var array|AbstractValueViewer[]
     */
    protected array $valueViewers = [];
    
    protected array $relationsToRead = [];
    
    protected ?\Closure $defaultFieldRenderer = null;
    protected ?\Closure $rawRecordDataModifier = null;
    
    /**
     * Container width (percents)
     */
    protected int $width = 100;
    
    protected bool $openInModal = true;
    /**
     * @var string|null - 'sm', 'md', 'lg', 'xl' | null - autodetect depending on $this->width
     */
    protected ?string $modalSize;
    
    protected ?\Closure $toolbarItems = null;
    
    protected \Closure|array $specialConditions = [];
    
    protected array $additionalColumnsToSelect = [];
    
    protected \Closure|array|null $dataToAddToRecord = null;
    protected \Closure|array|null $dataToSendToTemplate = null;
    
    protected string $template;
    protected ?string $jsInitiator = null;
    protected bool $isFinished = false;
    /**
     * Allow viewer names like "Relation.column_name"
     */
    protected bool $allowRelationsInValueViewers = false;
    /**
     * Allow viewer names like "column_name:key_name" for json/jsonb DB columns
     */
    protected bool $allowComplexValueViewerNames = false;
    
    public static function create(TableInterface $table, ScaffoldConfig $scaffoldConfig): ScaffoldSectionConfig|static
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
    
    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * @throws ScaffoldSectionConfigException
     */
    public function getTemplate(): string
    {
        if (empty($this->template)) {
            throw new ScaffoldSectionConfigException($this, 'Scaffold section view file not set');
        }
        return $this->template;
    }
    
    /**
     * Translate resource-related items (column names, input labels, etc.)
     */
    public function translate(?AbstractValueViewer $viewer = null, string $suffix = '', array $parameters = []): array|string
    {
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
     */
    public function translateGeneral(string $path, array $parameters = []): array|string
    {
        $prefix = $this->getSectionTranslationsPrefix();
        $text = $this->getScaffoldConfig()->translate($prefix, $path, $parameters);
        if (preg_match('%\.' . preg_quote($prefix . '.' . $path, '%') . '$%', $text)) {
            $text = $this->getCmfConfig()->transGeneral(
                $this->getSectionTranslationsPrefix('general') . '.' . $path,
                $parameters
            );
        }
        return $text;
    }
    
    /**
     * Prefix for this section's translations (ex: 'datagrid', 'item_details', 'form')
     * Will be used in $this->translate() and $this->translateGeneral()
     * @param string|null $subtype - null, 'value_viewer', 'general'.
     *      - null - common translations for section. Path will be like 'resource.{section}.translation'
     *      - 'value_viewer' - translations for value viewers labels. It is preferred to nest
     *          value viewer labels deeper in section (ex: 'datagrid.columns') or outside of section (ex: 'columns').
     *          Path will be like 'resource.{section}.value_viewers.translation'
     *      - 'general' - translations for general UI elements ($this->translateGeneral()). Used only when there is
     *          no custom translation (example path: 'resource.{section}.translation') and system needs to get
     *          translation from cmfConfig()->transGeneral(). By default it should be same as for $subtype = null
     * @return string
     */
    abstract protected function getSectionTranslationsPrefix(?string $subtype = null): string;
    
    /**
     * Disables any usage of TableStructure (validation, automatic field type guessing, etc)
     */
    public function thereIsNoDbColumns(): static
    {
        $this->thereIsNoDbColumns = true;
        return $this;
    }
    
    /**
     * @param AbstractValueViewer[] $viewers
     */
    protected function setValueViewers(array $viewers): static
    {
        /** @var AbstractValueViewer|null $config */
        foreach ($viewers as $name => $config) {
            $this->normalizeAndAddValueViewer($name, $config);
        }
        return $this;
    }
    
    protected function normalizeAndAddValueViewer(int|string $name, \Closure|string|AbstractValueViewer|null $config): static
    {
        $valueConverter = null;
        if (is_int($name)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $name = $config;
            $config = null;
        } elseif ($config instanceof \Closure) {
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
    public function getValueViewers(): array
    {
        return $this->valueViewers;
    }
    
    /**
     * Get only viewers that are linked to table columns defined in TableConfig class ($valueViewer->isDbColumn() === true)
     * @param bool $includeViewersForRelations - false: only vievers linked to main table's columns will be returned
     * @return AbstractValueViewer[]|DataGridColumn[]|FormInput[]|ValueCell[]
     */
    public function getViewersLinkedToDbColumns(bool $includeViewersForRelations = false): array
    {
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
    public function getStandaloneViewers(): array
    {
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
    public function getViewersForRelations(): array
    {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if ($viewer->isLinkedToDbColumn() && $viewer->hasRelation()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }
    
    abstract public function createValueViewer(): AbstractValueViewer;
    
    /**
     * @return DataGridColumn|ValueCell|FormInput|AbstractValueViewer
     * @throws \InvalidArgumentException
     * @noinspection PhpDocSignatureInspection
     */
    public function getValueViewer(string $name): AbstractValueViewer
    {
        if (!$this->hasValueViewer($name)) {
            throw new \InvalidArgumentException('Scaffold ' . get_class($this) . " has no viewer with name [$name]");
        }
        return $this->valueViewers[$name];
    }
    
    public function hasValueViewer(string $name): bool
    {
        return !empty($name) && !empty($this->valueViewers[$name]);
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function addValueViewer(
        string $name,
        ?AbstractValueViewer &$viewer = null,
        bool $autodetectIfLinkedToDbColumn = false
    ): static {
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
    protected function validateRelationValueViewerName(string $name, bool $throwErrorIfLocalColumnNotFound = true): array
    {
        $nameParts = explode('.', $name);
        $hasRelation = $this->getTable()->getTableStructure()->hasRelation($nameParts[0]);
        if (!$hasRelation && count($nameParts) === 1) {
            if ($throwErrorIfLocalColumnNotFound) {
                throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column '{$name}'");
            } else {
                return [null, null];
            }
        } elseif (!$hasRelation) {
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
     */
    protected function isValidComplexValueViewerName(string $name): bool
    {
        if ($this->allowComplexValueViewerNames && AbstractValueViewer::isComplexViewerName($name)) {
            [$colName,] = AbstractValueViewer::splitComplexViewerName($name);
            if ($this->getTable()->getTableStructure()->hasColumn($colName)) {
                $type = $this->getTable()->getTableStructure()->getColumn($colName)->getType();
                return in_array($type, [Column::TYPE_JSON, Column::TYPE_JSONB], true);
            }
        }
        return false;
    }
    
    protected function getNextValueViewerPosition(AbstractValueViewer $viewer): int
    {
        return count($this->valueViewers);
    }
    
    public function getTable(): TableInterface
    {
        return $this->table;
    }
    
    public function getTitle(string $default = ''): ?string
    {
        return empty($this->title) ? $default : $this->title;
    }
    
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    protected function modifyRawRecordData(array $record): array
    {
        if ($this->rawRecordDataModifier !== null) {
            $record = call_user_func($this->rawRecordDataModifier, $record, $this);
            if (!is_array($record)) {
                throw new \UnexpectedValueException('modifyRawDataRecord must return array');
            }
        }
        return $record;
    }
    
    /**
     * Modify record's data before it is processed by ScaffoldSectionConfig->prepareRecord().
     * Signature:
     * function (array $record, ScaffoldSectionConfig $sectionConfig): array { return $record }
     */
    public function setRawRecordDataModifier(\Closure $modifier): static
    {
        $this->rawRecordDataModifier = $modifier;
        return $this;
    }
    
    /**
     * @param array $record
     * @param array $virtualColumns - list of columns that are provided in TableStructure but marked as not existing in DB
     * @return array
     * @noinspection NotOptimalIfConditionsInspection
     */
    public function prepareRecord(array $record, array $virtualColumns = []): array
    {
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
            '___pk_value' => $record[$pkKey],
        ];
        if (!empty($virtualColumns)) {
            $recordObj = $this->getTable()->newRecord()->enableTrustModeForDbData()->fromDbData($record);
            foreach ($virtualColumns as $virtualColumn) {
                $record[$virtualColumn] = $recordObj->getValue($virtualColumn);
            }
        }
        $record = $this->modifyRawRecordData($record);
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
        foreach ($record as $key => $originalData) {
            if (!$this->hasValueViewer($key) && $this->getTable()->getTableStructure()->hasRelation($key)) {
                unset($recordWithBackup['__' . $key]);
                if ($this->getTable()->getTableStructure()->getRelation($key)->getType() === Relation::HAS_MANY) {
                    $recordWithBackup[$key] = [];
                    foreach ($originalData as $index => $relationData) {
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
                if (!Arr::has($recordWithBackup, $colName) && Arr::has($record, $colName)) {
                    $value = Arr::get($record, $colName);
                    if (is_string($value) && mb_strlen($value) >= 2) {
                        $value = json_decode($value, true);
                    }
                    Arr::set($recordWithBackup, $colName, $value);
                    Arr::set($recordWithBackup, '__' . $colName, $value);
                }
                if (
                    method_exists($valueViewer, 'convertValue')
                    && (
                        !method_exists($valueViewer, 'isVisible')
                        || $valueViewer->isVisible()
                    )
                ) {
                    $key = implode('.', $valueViewer::splitComplexViewerName($key));
                    $convertedValue = $valueViewer->convertValue(Arr::get($recordWithBackup, $key), $record);
                    Arr::set($recordWithBackup, $safeKey, $convertedValue);
                }
            } elseif (
                !$valueViewer->isLinkedToDbColumn()
                && !array_key_exists($key, $recordWithBackup)
                && method_exists($valueViewer, 'convertValue')
                && (
                    !method_exists($valueViewer, 'isVisible')
                    || $valueViewer->isVisible()
                )
            ) {
                $convertedValue = $valueViewer->convertValue(null, $record);
                Arr::set($recordWithBackup, $safeKey, $convertedValue);
            }
        }
        $customData = $this->getCustomDataForRecord($record);
        if (!empty($customData)) {
            $recordWithBackup = array_merge($recordWithBackup, $customData);
        }
        return array_merge($recordWithBackup, $permissionsAndServiceData);
    }
    
    /**
     * Process related record's data by viewers attached to it
     * @param string $relationName
     * @param array $recordData
     * @param null|string $index - string: passed for HAS_MANY relation | null: for relations other than HAS_MANY
     * @return array
     */
    protected function prepareRelatedRecord(string $relationName, array $recordData, ?string $index = null): array
    {
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
                } elseif (is_resource($value)) {
                    $recordWithBackup[$columnName] = '[resource]';
                }
            } elseif (is_resource($value)) {
                $recordWithBackup[$columnName] = '[resource]';
            }
        }
        return $recordWithBackup;
    }
    
    /**
     * Signature:
     * funciton (array $record, ScaffoldSectionConfig $scaffoldSectionConfig): array { return []; }
     */
    public function setDataToAddToRecord(array|\Closure $arrayOrClosure): static
    {
        $this->dataToAddToRecord = $arrayOrClosure;
        return $this;
    }
    
    public function getCustomDataForRecord(array $recordData): array
    {
        if (empty($this->dataToAddToRecord)) {
            return [];
        } elseif ($this->dataToAddToRecord instanceof \Closure) {
            return call_user_func($this->dataToAddToRecord, $recordData, $this);
        } else {
            return $this->dataToAddToRecord;
        }
    }
    
    /**
     * Signature:
     * function (ScaffoldSectionConfig $sectionConfig): array { return [] }
     */
    public function sendDataToTemplate(array|\Closure $arrayOrClosure): static
    {
        $this->dataToSendToTemplate = $arrayOrClosure;
        return $this;
    }
    
    public function getAdditionalDataForTemplate(): array
    {
        if (empty($this->dataToSendToTemplate)) {
            return [];
        } elseif ($this->dataToSendToTemplate instanceof \Closure) {
            return call_user_func($this->dataToSendToTemplate, $this);
        } else {
            return $this->dataToSendToTemplate;
        }
    }
    
    public function getRelationsToRead(): array
    {
        return $this->relationsToRead;
    }
    
    public function hasRelationsToRead(): bool
    {
        return !empty($this->relationsToRead);
    }
    
    /**
     * Set relations to join.
     * Notes:
     * - For data grid you need to provide key-value pairs in same format as for columns selection:
     *   ['Relation' => ['col1', 'col2', ...]]. More info: AbstractSelect::columns().
     *   HAS MANY relations are forbidden.
     * - For item edit form and item details view you need to provide only names of the relations you need to read
     *   with the item. All types of relations allowed but there is no automatic possibility to get deeper relations
     */
    public function readRelations(array $relationNames): static
    {
        $this->relationsToRead = $relationNames;
        return $this;
    }
    
    public function getDefaultValueRenderer(): ?\Closure
    {
        if (empty($this->defaultFieldRenderer)) {
            $this->setDefaultValueRenderer(function (RenderableValueViewer $valueViewer, $sectionConfig, array $dataForView) {
                $rendererConfig = $this->createValueRenderer()->setData($dataForView);
                $this->configureDefaultValueRenderer($rendererConfig, $valueViewer);
                return $rendererConfig;
            });
        }
        return $this->defaultFieldRenderer;
    }
    
    abstract protected function createValueRenderer(): ValueRenderer;
    
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        RenderableValueViewer $valueViewer
    ): void {
        $valueViewer->configureDefaultRenderer($renderer);
    }
    
    public function setDefaultValueRenderer(\Closure $defaultFieldRenderer): static
    {
        $this->defaultFieldRenderer = $defaultFieldRenderer;
        return $this;
    }
    
    public function hasDefaultValueRenderer(): bool
    {
        return !empty($this->defaultFieldRenderer);
    }
    
    /**
     * @return string[]
     * @throws \UnexpectedValueException
     */
    public function getToolbarItems(): array
    {
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
                } elseif (method_exists($item, 'build')) {
                    $item = $item->build();
                } elseif (method_exists($item, '__toString')) {
                    $item = (string)$item;
                } else {
                    throw new \UnexpectedValueException(
                        get_class($this) . '->toolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } elseif (!is_string($item)) {
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
     * - CmfMenuItem::redirect($this->getCmfConfig()->route('route', [], false))
     * ->setTitle($this->translate('action.details'))
     * - CmfMenuItem::request($this->getCmfConfig()->route('route', [], false), 'delete')
     * ->setTitle($this->translate('action.delete'))
     * ->setConfirm($this->translate('message.delete_confirm'));
     * - CmfMenuItem::bulkActionOnSelectedRows($this->getCmfConfig()->route('route', [], false), 'delete')
     * ->setTitle($this->translate('action.delete'))
     * ->setPrimaryKeyColumnName('id')
     * ->setConfirm($this->translate('message.delete_confirm'));
     * - CmfMenuItem::bulkActionOnFilteredRows($this->getCmfConfig()->route('route', [], false), 'delete')
     * ->setTitle($this->translate('action.delete'))
     * ->setConfirm($this->translate('message.delete_confirm'));
     * b. Alternative usage:
     * - call some url via ajax and then run "callback(json)"
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * ->setClass('btn btn-warning')
     * ->setDataAttr('action', 'request')
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('method', 'put')
     * ->setDataAttr('data', 'id={{= it.id }}')
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setHref('javascript: void(0)');
     * - redirect to other url
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * ->setClass('btn btn-warning')
     * ->setHref($this->getCmfConfig()->route('route', [], false))
     * ->setTarget('_blank')
     * c. ONLY FOR DATA GRIDS:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert selected items count
     * ->setDataAttr('action', 'bulk-selected')
     * ->setDataAttr('confirm', trans('path.to.translation'))
     * //^ confirm action before sending request to server
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('method', 'delete')
     * //^ can be 'post', 'put', 'delete' depending on action type
     * ->setDataAttr('id-field', 'id')
     * //^ for bulk actions on selected items id field name to use to get rows ids, default: 'id'
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setDataAttr('response-type', 'json')
     * //^ one of: json, html, xml. Default: 'json'
     * ->setHref('javascript: void(0)');
     * Values will be received in the 'ids' key of the request as array
     * - call some url via ajax passing filter conditions and then run "callback(json)"
     * Tag::a()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert filtered items count
     * ->setDataAttr('action', 'bulk-filtered')
     * ->setDataAttr('confirm', trans('path.to.translation'))
     * //^ confirm action before sending request to server
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('method', 'put')
     * //^ can be 'post', 'put', 'delete' depending on action type
     * ->setDataAttr('on-success', 'callbackFuncitonName')
     * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
     * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
     * ->setDataAttr('response-type', 'json')
     * //^ one of: json, html, xml. Default: 'json'
     * ->setHref('javascript: void(0)');
     * - bulk actions with custom on-click handler
     * Tag::button()
     * ->setContent(trans('path.to.translation'))
     * //^ you can use ':count' in label to insert selected items count or filtered items count
     * //^ depending on 'data-type' attribute
     * ->setClass('btn btn-success')
     * ->setDataAttr('type', 'bulk-selected')
     * //^ 'bulk-selected' or 'bulk-filtered'
     * ->setDataAttr('url', $this->getCmfConfig()->route('route', [], false))
     * ->setDataAttr('id-field', 'id')
     * //^ id field name to use to get rows ids, default: 'id'
     * ->setOnClick('someFunction(this)')
     * //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * 2. List of toolbar items:
     *      [
     *          ToolbarItem1,
     *          ToolbarItem2,
     *          'delete' => null
     *      ]
     */
    public function setToolbarItems(\Closure $callback): static
    {
        $this->toolbarItems = $callback;
        return $this;
    }
    
    public function isCreateAllowed(): bool
    {
        return $this->getScaffoldConfig()->isCreateAllowed();
    }
    
    public function isEditAllowed(): bool
    {
        return $this->getScaffoldConfig()->isEditAllowed();
    }
    
    public function isCloningAllowed(): bool
    {
        return $this->getScaffoldConfig()->isCloningAllowed();
    }
    
    public function isDeleteAllowed(): bool
    {
        return $this->getScaffoldConfig()->isDeleteAllowed();
    }
    
    public function isDetailsViewerAllowed(): bool
    {
        return $this->getScaffoldConfig()->isDetailsViewerAllowed();
    }
    
    public function hasSpecialConditions(): bool
    {
        return !empty($this->specialConditions);
    }
    
    public function getSpecialConditions(): array
    {
        return $this->specialConditions instanceof \Closure
            ? call_user_func($this->specialConditions, $this)
            : $this->specialConditions;
    }
    
    /**
     * Signature:
     * function (ScaffoldSectionConfig $scaffoldSectionConfig): array { return []; }
     */
    public function setSpecialConditions(array|\Closure $specialConditions): static
    {
        $this->specialConditions = $specialConditions;
        return $this;
    }
    
    public function getWidth(): int
    {
        return min($this->width, 100);
    }
    
    public function setWidth(int $percents): static
    {
        $this->width = $percents;
        return $this;
    }
    
    /**
     * Get css classes for content container.
     * Uses witdh to collect col-??-?? and col-??-offset?? classes
     */
    public function getCssClassesForContainer(): string
    {
        $colsXl = $this->getWidth() >= 100 ? 12 : ceil(12 * ($this->getWidth() / 100));
        $colsXlLeft = floor((12 - $colsXl) / 2);
        $colsLg = $colsXl >= 10 ? 12 : $colsXl + 2;
        $colsLgLeft = floor((12 - $colsLg) / 2);
        return "col-xs-12 col-xl-{$colsXl} col-lg-{$colsLg} col-xl-offset-{$colsXlLeft} col-lg-offset-{$colsLgLeft}";
    }
    
    /**
     * @param bool $isEnabled
     * @param string|null $size - 'sm', 'md', 'lg', 'xl' | null - autodetect depending on $this->width
     * @return static
     */
    public function setModalConfig(bool $isEnabled = true, ?string $size = null): static
    {
        $this->openInModal = $isEnabled;
        $this->modalSize = in_array($size, ['sm', 'md', 'lg', 'xl']) ? $size : null;
        return $this;
    }
    
    public function getModalSize(): string
    {
        if ($this->modalSize) {
            return $this->modalSize;
        }
        return $this->getWidth() > 60 ? 'lg' : 'md';
    }
    
    public function isUsingModal(): bool
    {
        return $this->openInModal;
    }
    
    public function getJsInitiator(): ?string
    {
        return $this->jsInitiator;
    }
    
    public function hasJsInitiator(): bool
    {
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
     * @throws \InvalidArgumentException
     */
    public function setJsInitiator(string $jsFunctionName): static
    {
        if (!preg_match('%^[$_a-zA-Z][a-zA-Z0-9_.\[\]\'"]+$%', $jsFunctionName)) {
            throw new \InvalidArgumentException("Invalid JavaScript funciton name: [$jsFunctionName]");
        }
        $this->jsInitiator = $jsFunctionName;
        return $this;
    }
    
    /**
     * Called before scaffold template rendering
     */
    public function beforeRender(): void
    {
    }
    
    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     * @throws \BadMethodCallException
     */
    public function finish(): void
    {
        if ($this->isFinished) {
            throw new \BadMethodCallException('Attempt to call ' . get_class($this) . '->finish() twice');
        } else {
            $this->isFinished = true;
        }
    }
    
    public function setAdditionalColumnsToSelect(...$columnNames): static
    {
        if (count($columnNames) && is_array($columnNames[0])) {
            $columnNames = $columnNames[0];
        }
        $this->additionalColumnsToSelect = $columnNames;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getAdditionalColumnsToSelect(): array
    {
        return $this->additionalColumnsToSelect;
    }
    
    public function getItemEditMenuItem(string $section = 'toolbar'): CmfRedirectMenuItem
    {
        $url = $this->getScaffoldConfig()->getUrlToItemEditForm('{{= it.___pk_value}}', false, true);
        return CmfMenuItem::redirect($url)
            ->setTitle($this->translateGeneral($section . '.edit_item'))
            ->setIconClasses('glyphicon glyphicon-edit')
            ->setIconColorClass('text-green')
            ->setButtonClasses('btn btn-success item-edit')
            ->setAccessProvider(function () {
                return $this->isEditAllowed();
            })
            ->setConditionToShow('it.___edit_allowed');
    }
    
    public function getItemCloneMenuItem(string $section = 'toolbar'): CmfRedirectMenuItem
    {
        $url = $this->getScaffoldConfig()->getUrlToItemCloneForm('{{= it.___pk_value}}', false, true);
        return CmfMenuItem::redirect($url)
            ->setTitle($this->translateGeneral($section . '.clone_item'))
            ->setIconClasses('fa fa-copy')
            ->setIconColorClass('text-primary')
            ->setButtonClasses('btn btn-primary item-clone')
            ->setAccessProvider(function () {
                return $this->isCloningAllowed();
            })
            ->setConditionToShow('it.___cloning_allowed');
    }
    
    public function getItemCreateMenuItem(string $section = 'toolbar'): CmfRedirectMenuItem
    {
        $url = $this->getScaffoldConfig()->getUrlToItemAddForm([], false, true);
        return CmfMenuItem::redirect($url)
            ->setTitle($this->translateGeneral($section . '.create_item'))
            ->setIconClasses('fa fa-file-o')
            ->setIconColorClass('text-primary')
            ->setButtonClasses('btn btn-primary item-add')
            ->setAccessProvider(function () {
                return $this->isCreateAllowed();
            })
            ->setConditionToShow('it.___create_allowed');
    }
    
    public function getItemDetailsMenuItem(string $section = 'toolbar'): CmfRedirectMenuItem
    {
        $url = $this->getScaffoldConfig()->getUrlToItemDetails('{{= it.___pk_value}}', false, true);
        return CmfMenuItem::redirect($url)
            ->setTitle($this->translateGeneral($section . '.view_item'))
            ->setIconClasses('glyphicon glyphicon-info-sign')
            ->setIconColorClass('text-light-blue')
            ->setButtonClasses('btn btn-info')
            ->setAccessProvider(function () {
                return $this->isDetailsViewerAllowed();
            })
            ->setConditionToShow('it.___details_allowed');
    }
    
    public function getItemDeleteMenuItem(string $section = 'toolbar'): CmfRequestMenuItem
    {
        $url = $this->getScaffoldConfig()->getUrlToItemDelete('{{= it.___pk_value}}', false, true);
        return CmfMenuItem::request($url, 'delete')
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
