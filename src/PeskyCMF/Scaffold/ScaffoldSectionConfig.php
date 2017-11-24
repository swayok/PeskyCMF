<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
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
    protected $defaultFieldRenderer = null;
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
     * @var \Closure|null
     */
    protected $toolbarItems = null;
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
    protected $template = null;
    /**
     * @var string
     */
    protected $jsInitiator = null;
    protected $isFinished = false;
    /** @var  bool */
    protected $allowRelationsInValueViewers = false;
    /** @var array */
    protected $additionalColumnsToSelect = [];

    /**
     * @param TableInterface $table
     * @param ScaffoldConfig $scaffoldConfig
     * @return $this
     */
    static public function create(TableInterface $table, ScaffoldConfig $scaffoldConfig) {
        $class = get_called_class();
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
            $text = cmfTransGeneral($this->getSectionTranslationsPrefix('general') . '.' . $path);
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
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     */
    protected function setValueViewers(array $viewers) {
        /** @var AbstractValueViewer|null $config */
        foreach ($viewers as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addValueViewer($name, $config);
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
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function addValueViewer($name, AbstractValueViewer $viewer = null) {
        $usesRelation = false;
        if (
            !$this->thereIsNoDbColumns
            && (!$viewer || $viewer->isLinkedToDbColumn())
            && !$this->getTable()->getTableStructure()->hasColumn($name)
        ) {
            if ($this->allowRelationsInValueViewers) {
                list($relation, $relationColumnName) = $this->validateRelationValueViewerName($name);
                $usesRelation = true;
            } else {
                throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column '{$name}'");
            }
        }
        if (empty($viewer)) {
            $viewer = $this->createValueViewer();
        }
        if ($this->thereIsNoDbColumns) {
            $viewer->setIsLinkedToDbColumn(false);
        }
        $viewer->setName($name);
        $viewer->setPosition($this->getNextValueViewerPosition($viewer));
        $viewer->setScaffoldSectionConfig($this);
        if ($usesRelation) {
            $viewer->setRelation($relation, $relationColumnName);
        }
        $this->valueViewers[$name] = $viewer;
        return $this;
    }

    /**
     * @param $name
     * @return array - array(Relation, column_name)
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    protected function validateRelationValueViewerName($name) {
        $nameParts = explode('.', $name);
        $hasRelation = $this->getTable()->getTableStructure()->hasRelation($nameParts[0]);
        if (!$hasRelation && count($nameParts) === 1) {
            throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column '{$name}'");
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
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function prepareRecord(array $record, array $virtualColumns = []) {
        /** @noinspection UnnecessaryParenthesesInspection */
        $pkKey = $this->getTable()->getPkColumnName();
        $permissionsAndServiceData = [
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
            '__modal' => $this->isUsingDialog(),
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
            $recordWithBackup[$key] = $recordWithBackup['__' . $key] = $value;
        }
        foreach ($record as $key => $value) {
            if (!$this->hasValueViewer($key) && $this->getTable()->getTableStructure()->hasRelation($key)) {
                unset($recordWithBackup['__' . $key]);
                if ($this->getTable()->getTableStructure()->getRelation($key)->getType() === Relation::HAS_MANY) {
                    $recordWithBackup[$key] = [];
                    foreach ($record[$key] as $index => $relationData) {
                        $recordWithBackup[$key][$index] = $this->prepareRelatedRecord($key, $relationData, $index);
                    }
                } else {
                    $recordWithBackup[$key] = $this->prepareRelatedRecord($key, $record[$key]);
                }
                continue;
            }
            if (empty($valueViewers[$key])) {
                if ($key !== $pkKey) {
                    unset($recordWithBackup[$key]);
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
                $recordWithBackup[$key] = $valueViewer->convertValue($recordWithBackup[$key], $record);
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
     * @param null|string $index - string: passed for HAS_MANY relation | null: for relations other then HAS_MANY
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function prepareRelatedRecord($relationName, array $relationRecordData, $index = null) {
        $recordWithBackup = $relationRecordData;
        $valueViewers = $this->getViewersForRelations();
        foreach ($relationRecordData as $columnName => $value) {
            $viewerName = $relationName . '.' . ($index === null ? '' : $index . '.') . $columnName;
            if (
                array_key_exists($viewerName, $valueViewers)
                && $valueViewers[$viewerName]->getRelation()->getName() === $relationName
            ) {
                $recordWithBackup[$columnName] = $recordWithBackup['__' . $columnName] = $value;
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
                        $recordWithBackup[$columnName],
                        $relationRecordData
                    );
                }
            }
        }
        return $recordWithBackup;
    }

    /**
     * @param array|\Closure $arrayOrClosure
     *      - \Closure: funciton (array $record, ScaffoldSectionConfig $scaffoldSectionConfig) { return []; }
     * @return $this
     * @throws ScaffoldException
     */
    public function setDataToAddToRecord($arrayOrClosure) {
        if (!is_array($arrayOrClosure) && !($arrayOrClosure instanceof \Closure)) {
            throw new ScaffoldException($this, 'setDataToAddToRecord($arrayOrClosure) accepts only array or \Closure');
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
     * @throws ScaffoldException
     */
    public function sendDataToTemplate($arrayOrClosure) {
        if (!is_array($arrayOrClosure) && !($arrayOrClosure instanceof \Closure)) {
            throw new ScaffoldException($this, 'setDataToAddToRecord($arrayOrClosure) accepts only array or \Closure');
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
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
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
     * @throws \LogicException
     */
    public function getToolbarItems() {
        if (empty($this->toolbarItems)) {
            return [];
        }
        $toolbarItems = call_user_func($this->toolbarItems, $this);
        if (!is_array($toolbarItems)) {
            throw new \LogicException(get_class($this) . '->toolbarItems closure must return an array');
        }
        /**
         * @var array $toolbarItems
         * @var Tag|string $item
         */
        foreach ($toolbarItems as &$item) {
            if (is_object($item)) {
                if (method_exists($item, 'build')) {
                    $item = $item->build();
                } else if (method_exists($item, '__toString')) {
                    $item = (string) $item;
                } else {
                    throw new \LogicException(
                        get_class($this) . '->toolbarItems: array may contain only strings and objects with build() or __toString() methods'
                    );
                }
            } else if (!is_string($item)) {
                throw new \LogicException(
                    get_class($this) . '->toolbarItems: array may contain only strings and objects with build() or __toString() methods'
                );
            }
        }
        return $toolbarItems;
    }

    /**
     * @param \Closure $callback - function (ScaffoldSectionConfig $scaffoldSectionConfig) { return []; }
     * Callback must return an array.
     * Array may contain only strings, Tag class instances, or any object with build() or __toString() method
     * Examples:
     * - call some url via ajax and then run "callback(json)"
         Tag::a()
             ->setContent(trans('path.to.translation'))
             ->setClass('btn btn-warning')
             ->setDataAttr('action', 'request')
             ->setDataAttr('url', cmfRoute('route', [], false))
             ->setDataAttr('method', 'put')
             ->setDataAttr('data', 'id=:id:')
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
     * ONLY FOR DATA GRIDS:
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
             //^ id field name to use to get rows ids, default: 'id'
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
            throw new \InvalidArgumentException('setSpecialConditions expects array or \Closure');
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
     * @param string $width
     * @return $this
     */
    public function setWidth($width) {
        $this->width = $width;
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
     * NOTES for data grids:
     * - JS function will be called before any other common js executed (even before DataTables plugin initiated)
     * - JS function will be called within the context of the data grid (use this to access it)
     * @param string $jsFunctionName
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setJsInitiator($jsFunctionName) {
        if (!is_string($jsFunctionName) && !preg_match('%^[$_a-zA-Z][a-zA-Z0-9_.\[\]\'"]+$%s', $jsFunctionName)) {
            throw new \InvalidArgumentException("Invalid JavaScript funciton name: [$jsFunctionName]");
        }
        $this->jsInitiator = $jsFunctionName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUsingDialog() {
        return false;
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

}