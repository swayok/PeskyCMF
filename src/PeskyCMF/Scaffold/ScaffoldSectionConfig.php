<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;

abstract class ScaffoldSectionConfig {
    /**
     * @var TableInterface
     */
    protected $table;
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

    public function setTemplate($template) {
        $this->template = $template;
        return $this;
    }

    public function getTemplate() {
        if (empty($this->template)) {
            throw new ScaffoldSectionException($this, 'Scaffold action view file not set');
        }
        return $this->template;
    }

    /**
     * @param array $viewers
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionException
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
     * Get field configs only for fields that exist in DB ($valueViewer->isDbColumn() === true)
     * @return AbstractValueViewer[]|DataGridColumn[]|ValueCell[]|FormInput[]
     */
    public function getViewersLinkedToDbColumns() {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if ($viewer->isDbColumn()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * Get field configs only for fields that does not exist in DB ($valueViewer->isDbColumn() === false)
     * @return AbstractValueViewer[]|DataGridColumn[]|ValueCell[]|FormInput[]
     */
    public function getStandaloneViewers() {
        $ret = [];
        foreach ($this->getValueViewers() as $key => $viewer) {
            if (!$viewer->isDbColumn()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * @return AbstractValueViewer
     * @throws ScaffoldException
     */
    abstract public function createValueViewer();

    /**
     * @param string $name
     * @return DataGridColumn|ValueCell|FormInput|AbstractValueViewer|array
     * @throws ScaffoldSectionException
     */
    public function getValueViewer($name) {
        if (!$this->hasValueViewer($name)) {
            throw new ScaffoldSectionException($this, "Scaffold action has not field with name [$name]");
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
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionException
     */
    public function addValueViewer($name, AbstractValueViewer $viewer = null) {
        if ((!$viewer || $viewer->isDbColumn()) && !$this->getTable()->getTableStructure()->hasColumn($name)) {
            throw new ScaffoldSectionException($this, "Unknown table column [$name]");
        }
        if (empty($viewer)) {
            $viewer = $this->createValueViewer();
        }
        $viewer->setName($name);
        $viewer->setPosition($this->getNextValueViewerPosition($viewer));
        $viewer->setScaffoldSectionConfig($this);
        $this->valueViewers[$name] = $viewer;
        return $this;
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
     * @throws \PeskyCMF\Scaffold\ValueViewerException
     */
    public function prepareRecord(array $record) {
        /** @noinspection UnnecessaryParenthesesInspection */
        $permissions = [
            '___delete_allowed' => (
                $this->isDeleteAllowed()
                && $this->scaffoldConfig->isRecordDeleteAllowed($record)
            ),
            '___edit_allowed' => (
                $this->isEditAllowed()
                && $this->scaffoldConfig->isRecordEditAllowed($record)
            ),
            '___details_allowed' => (
                $this->isDetailsViewerAllowed()
                && $this->scaffoldConfig->isRecordDetailsAllowed($record)
            )
        ];
        $customData = $this->getCustomDataForRecord($record);
        $dbFields = $this->getViewersLinkedToDbColumns();
        $pkKey = $this->getTable()->getPkColumnName();
        // backup values
        $recordWithBackup = [];
        foreach ($record as $key => $value) {
            $recordWithBackup[$key] = $recordWithBackup['__' . $key] = $value;
        }
        foreach ($record as $key => $notUsed) {
            if ($this->getTable()->getTableStructure()->hasRelation($key)) {
                continue;
            }
            if (empty($dbFields[$key])) {
                if ($key !== $pkKey) {
                    unset($recordWithBackup[$key]);
                }
                continue;
            }
            $fieldConfig = $dbFields[$key];
            if (
                is_object($fieldConfig)
                && method_exists($fieldConfig, 'convertValue')
                && (
                    !method_exists($fieldConfig, 'isVisible')
                    || $fieldConfig->isVisible()
                )
            ) {
                $recordWithBackup[$key] = $fieldConfig->convertValue(
                    $recordWithBackup[$key],
                    $recordWithBackup
                );
            }
        }
        if (!empty($customData) && is_array($customData)) {
            $recordWithBackup = array_merge($recordWithBackup, $customData);
        }
        $recordWithBackup = array_merge($recordWithBackup, $permissions);
        foreach ($this->getStandaloneViewers() as $key => $fieldConfig) {
            $valueConverter = $fieldConfig->getValueConverter();
            if ($valueConverter instanceof \Closure) {
                $recordWithBackup[$key] = call_user_func($valueConverter, $recordWithBackup, $fieldConfig, $this);
            } else if (!array_has($recordWithBackup, $key)) {
                $recordWithBackup[$key] = '';
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
     * @param array|\Closure $arrayOrClosure - function (ScaffoldSectionConfig $actionConfig) { return [] }
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
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     */
    public function getDefaultValueRenderer() {
        if (!empty($this->defaultFieldRenderer)) {
            return $this->defaultFieldRenderer;
        } else {
            $this->setDefaultValueRenderer(function (AbstractValueViewer $fieldConfig, $actionConfig, array $dataForView) {
                $rendererConfig = $this->createValueRenderer()->setData($dataForView);
                $this->configureDefaultValueRenderer($rendererConfig, $fieldConfig);
                return $rendererConfig;
            });
            return $this->defaultFieldRenderer;
        }
    }

    /**
     * @return ValueRenderer
     * @throws ScaffoldSectionException
     */
    abstract protected function createValueRenderer();

    /**
     * @param ValueRenderer $renderer
     * @param AbstractValueViewer $viewer
     */
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        AbstractValueViewer $viewer
    ) {

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
        * Tag::a()
            * ->setContent(trans('path.to.translation'))
            * ->setClass('btn btn-warning')
            * ->setDataAttr('action', 'request')
            * ->setDataAttr('url', route('route', [], false))
            * ->setDataAttr('method', 'put')
            * ->setDataAttr('data', 'id=:id:')
            * ->setDataAttr('on-success', 'callbackFuncitonName')
            * //^ callbackFuncitonName must be a function name: 'funcName' or 'Some.funcName' allowed
            * //^ It will receive 3 args: data, $link, defaultOnSuccessCallback
            * ->setHref('javascript: void(0)');
     * - redirect to other url
        * Tag::a()
            * ->setContent(trans('path.to.translation'))
            * ->setClass('btn btn-warning')
            * ->setHref('url', route('route', [], false))
            * ->setTarget('_blank')
     * ONLY FOR DATA GRIDS:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
        * Tag::a()
            * ->setContent(trans('path.to.translation'))
            * //^ you can use ':count' in label to insert selected items count
            * ->setDataAttr('action', 'bulk-selected')
            * ->setDataAttr('confirm', trans('path.to.translation'))
            * //^ confirm action before sending request to server
            * ->setDataAttr('url', route('route', [], false))
            * ->setDataAttr('method', 'delete')
            * //^ can be 'post', 'put', 'delete' depending on action type
            * ->setDataAttr('id-field', 'id')
            * //^ id field name to use to get rows ids, default: 'id'
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
            * ->setDataAttr('url', route('route', [], false))
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
            * ->setDataAttr('url', route('route', [], false))
            * ->setDataAttr('id-field', 'id')
            * //^ id field name to use to get rows ids, default: 'id'
            * ->setOnClick('someFunction(this)')
            * //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * @return $this
     * @throws ScaffoldSectionException
     */
    public function setToolbarItems(\Closure $callback) {
        $this->toolbarItems = $callback;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->scaffoldConfig->isCreateAllowed();
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->scaffoldConfig->isEditAllowed();
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->scaffoldConfig->isDeleteAllowed();
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->scaffoldConfig->isDetailsViewerAllowed();
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
     * @throws ScaffoldSectionException
     */
    public function setSpecialConditions($specialConditions) {
        if (!is_array($specialConditions) && !($specialConditions instanceof \Closure)) {
            throw new ScaffoldSectionException($this, 'setSpecialConditions expects array or \Closure');
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
     * @throws ScaffoldSectionException
     */
    public function setJsInitiator($jsFunctionName) {
        if (!is_string($jsFunctionName) && !preg_match('%^[$_a-zA-Z][a-zA-Z0-9_.\[\]\'"]+$%s', $jsFunctionName)) {
            throw new ScaffoldSectionException($this, "Invalid JavaScript funciton name: [$jsFunctionName]");
        }
        $this->jsInitiator = $jsFunctionName;
        return $this;
    }

    /**
     * Finish building config.
     * This may trigger some actions that should be applied after all configurations were provided
     */
    public function finish() {

    }

}