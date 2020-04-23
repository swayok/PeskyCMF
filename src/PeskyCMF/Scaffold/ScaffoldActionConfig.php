<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\Form\FormFieldConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;
use Swayok\Html\Tag;

abstract class ScaffoldActionConfig {
    /** @var CmfDbModel */
    protected $model;
    /** @var array */
    protected $fieldsConfigs = [];
    /**
     * Fields list
     * @var array|ScaffoldFieldConfig[]
     */
    protected $fields = [];
    /** @var string */
    protected $title;
    /** @var array */
    protected $contains = [];
    /** @var null|callable */
    protected $defaultFieldRenderer = null;
    /**
     * Container width (percents)
     * @var int
     */
    protected $width = 100;
    /**
     * @var callable|null
     */
    protected $toolbarItems = null;
    /** @var array|callable */
    protected $specialConditions = [];
    /** @var ScaffoldSectionConfig */
    protected $scaffoldSection;
    /** @var array|callable */
    protected $dataToAddToRecord;
    /** @var array|callable */
    protected $dataToSendToView;
    /** @var string */
    protected $view = null;
    /** @var string */
    protected $jsInitiator = null;

    /**
     * @param CmfDbModel $model
     * @param ScaffoldSectionConfig $scaffoldSection
     * @return $this
     */
    static public function create(CmfDbModel $model, ScaffoldSectionConfig $scaffoldSection) {
        $class = get_called_class();
        return new $class($model, $scaffoldSection);
    }

    /**
     * ScaffoldActionConfig constructor.
     * @param CmfDbModel $model
     * @param ScaffoldSectionConfig $scaffoldSection
     */
    public function __construct(CmfDbModel $model, ScaffoldSectionConfig $scaffoldSection) {
        $this->model = $model;
        $this->scaffoldSection = $scaffoldSection;
    }

    public function setView($view) {
        $this->view = $view;
        return $this;
    }

    public function getView() {
        if (empty($this->view)) {
            throw new ScaffoldActionException($this, 'Scaffold action view file not set');
        }
        return $this->view;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields) {
        /** @var ScaffoldFieldConfig|null $config */
        foreach ($fields as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addField($name, $config);
        }
        return $this;
    }

    /**
     * @return array|ScaffoldFieldConfig[]|DataGridFieldConfig[]|ItemDetailsFieldConfig[]|FormFieldConfig[]
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Get field configs only for fields that exist in DB ($fieldConfig->isDbField() === true)
     * @return array|ScaffoldFieldConfig[]|DataGridFieldConfig[]|ItemDetailsFieldConfig[]|FormFieldConfig[]
     */
    public function getDbFields() {
        $ret = [];
        foreach ($this->getFields() as $key => $field) {
            if ($field->isDbField()) {
                $ret[$key] = $field;
            }
        }
        return $ret;
    }

    /**
     * Get field configs only for fields that does not exist in DB ($fieldConfig->isDbField() === false)
     * @return array|ScaffoldFieldConfig[]|DataGridFieldConfig[]|ItemDetailsFieldConfig[]|FormFieldConfig[]
     */
    public function getNonDbFields() {
        $ret = [];
        foreach ($this->getFields() as $key => $field) {
            if (!$field->isDbField()) {
                $ret[$key] = $field;
            }
        }
        return $ret;
    }

    /**
     * @return ScaffoldFieldConfig
     * @throws ScaffoldException
     */
    abstract public function createFieldConfig();

    /**
     * @param string $name
     * @return DataGridFieldConfig|ItemDetailsFieldConfig|FormFieldConfig|ScaffoldFieldConfig|array
     * @throws ScaffoldActionException
     */
    public function getField($name) {
        if (!$this->hasField($name)) {
            throw new ScaffoldActionException($this, "Scaffold action has not field with name [$name]");
        }
        return $this->fields[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasField($name) {
        return !empty($name) && !empty($this->fields[$name]);
    }

    /**
     * @param string $name
     * @param null|ScaffoldFieldConfig $config
     * @return $this
     * @throws ScaffoldActionException
     */
    public function addField($name, $config = null) {
        if ((!$config || $config->isDbField()) && !$this->getModel()->hasTableColumn($name)) {
            throw new ScaffoldActionException($this, "Unknown table column [$name]");
        }
        if (empty($config)) {
            $config = $this->createFieldConfig();
        }
        $config->setName($name);
        $config->setPosition($this->getNextFieldPosition($config));
        $config->setScaffoldActionConfig($this);
        $this->fields[$name] = $config;
        return $this;
    }

    /**
     * @param ScaffoldFieldConfig $fieldConfig
     * @return int
     */
    protected function getNextFieldPosition(ScaffoldFieldConfig $fieldConfig) {
        return count($this->fields);
    }

    /**
     * @return CmfDbModel
     */
    public function getModel() {
        return $this->model;
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
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     */
    public function prepareRecord(array $record) {
        $permissions = [
            '___delete_allowed' => (
                $this->isDeleteAllowed()
                && $this->scaffoldSection->isRecordDeleteAllowed($record)
            ),
            '___edit_allowed' => (
                $this->isEditAllowed()
                && $this->scaffoldSection->isRecordEditAllowed($record)
            ),
            '___details_allowed' => (
                $this->isDetailsViewerAllowed()
                && $this->scaffoldSection->isRecordDetailsAllowed($record)
            )
        ];
        $customData = $this->getCustomDataForRecord($record);
        $dbFields = $this->getDbFields();
        $pkKey = $this->getModel()->getPkColumnName();
        // backup values
        $recordWithBackup = [];
        foreach ($record as $key => $value) {
            $recordWithBackup[$key] = $recordWithBackup['__' . $key] = $value;
        }
        foreach ($record as $key => $notUsed) {
            if ($this->getModel()->hasTableRelation($key)) {
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
        foreach ($this->getNonDbFields() as $key => $fieldConfig) {
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
     * @param array|callable $arrayOrCallable
     *      - callable: funciton (array $record, ScaffoldActionConfig $scaffoldAction) { return []; }
     * @return $this
     * @throws ScaffoldException
     */
    public function setDataToAddToRecord($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !is_callable($arrayOrCallable)) {
            throw new ScaffoldException($this, 'setDataToAddToRecord($arrayOrCallable) accepts only array or callable');
        }
        $this->dataToAddToRecord = $arrayOrCallable;
        return $this;
    }

    /**
     * @param array $record
     * @return array|mixed
     */
    public function getCustomDataForRecord(array $record) {
        if (empty($this->dataToAddToRecord)) {
            return [];
        } else if (is_callable($this->dataToAddToRecord)) {
            return call_user_func($this->dataToAddToRecord, $record, $this);
        } else {
            return $this->dataToAddToRecord;
        }
    }

    /**
     * @param array|callable $arrayOrCallable - function (ScaffoldActionConfig $actionConfig) { return [] }
     * @return $this
     * @throws ScaffoldException
     */
    public function sendDataToView($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !is_callable($arrayOrCallable)) {
            throw new ScaffoldException($this, 'setDataToAddToRecord($arrayOrCallable) accepts only array or callable');
        }
        $this->dataToSendToView = $arrayOrCallable;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getAdditionalDataForView() {
        if (empty($this->dataToSendToView)) {
            return [];
        } else if (is_callable($this->dataToSendToView)) {
            return call_user_func($this->dataToSendToView, $this);
        } else {
            return $this->dataToSendToView;
        }
    }

    /**
     * @return array
     */
    public function getContains() {
        return $this->contains;
    }

    /**
     * @return bool
     */
    public function hasContains() {
        return !empty($this->contains);
    }

    /**
     * @param string $relationAlias
     * @param array $containOptions
     * @return $this
     */
    public function addContain($relationAlias, $containOptions = []) {
        $this->contains[$relationAlias] = $containOptions;
        return $this;
    }

    /**
     * @param array $contains
     * @return $this
     */
    public function setContains($contains) {
        $this->contains = $contains;
        return $this;
    }

    /**
     * @return callable|null
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     */
    public function getDefaultFieldRenderer() {
        if (!empty($this->defaultFieldRenderer)) {
            return $this->defaultFieldRenderer;
        } else {
            $this->setDefaultFieldRenderer(function (ScaffoldFieldConfig $fieldConfig, $actionConfig, array $dataForView) {
                $rendererConfig = $this->createFieldRendererConfig()->setData($dataForView);
                $this->configureDefaultRenderer($rendererConfig, $fieldConfig);
                return $rendererConfig;
            });
            return $this->defaultFieldRenderer;
        }
    }

    /**
     * @return ScaffoldFieldRendererConfig
     * @throws ScaffoldActionException
     */
    abstract protected function createFieldRendererConfig();

    /**
     * @param ScaffoldFieldRendererConfig $rendererConfig
     * @param ScaffoldFieldConfig $fieldConfig
     */
    protected function configureDefaultRenderer(
        ScaffoldFieldRendererConfig $rendererConfig,
        ScaffoldFieldConfig $fieldConfig
    ) {

    }

    /**
     * @param callable $defaultFieldRenderer
     * @return $this
     */
    public function setDefaultFieldRenderer(callable $defaultFieldRenderer) {
        $this->defaultFieldRenderer = $defaultFieldRenderer;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDefaultFieldRenderer() {
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
        /** @var Tag|string $item */
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
     * @param \Closure $callback - function (ScaffoldActionConfig $scaffoldAction) { return []; }
     * Callback must return an array.
     * Array may contain only strings, Tag class instances, or any object with build() or __toString() method
     * Examples:
     * - call some url via ajax and then run "callback(json)"
        Tag::a()
            ->setContent(trans('path.to.translation'))
            ->setClass('btn btn-warning')
            ->setDataAttr('action', 'request')
            ->setDataAttr('url', route('route', [], false))
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
            ->setHref('url', route('route', [], false))
            ->setTarget('_blank')
     * ONLY FOR DATA GRIDS:
     * - call some url via ajax passing all selected ids and then run "callback(json)"
        Tag::a()
            ->setContent(trans('path.to.translation'))
            //^ you can use ':count' in label to insert selected items count
            ->setDataAttr('action', 'bulk-selected')
            ->setDataAttr('confirm', trans('path.to.translation'))
            //^ confirm action before sending request to server
            ->setDataAttr('url', route('route', [], false))
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
            ->setDataAttr('url', route('route', [], false))
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
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('id-field', 'id')
            //^ id field name to use to get rows ids, default: 'id'
            ->setOnClick('someFunction(this)')
            //^ for 'bulk-selected': inside someFunction() you can get selected rows ids via $(this).data('data').ids
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setToolbarItems(\Closure $callback) {
        $this->toolbarItems = $callback;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->scaffoldSection->isCreateAllowed();
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->scaffoldSection->isEditAllowed();
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->scaffoldSection->isDeleteAllowed();
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->scaffoldSection->isDetailsViewerAllowed();
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
        return is_callable($this->specialConditions)
            ? call_user_func($this->specialConditions, $this)
            : $this->specialConditions;
    }

    /**
     * @param array|callable $specialConditions - array or function (ScaffoldActionConfig $scaffoldAction) {}
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setSpecialConditions($specialConditions) {
        if (!is_array($specialConditions) && !is_callable($specialConditions)) {
            throw new ScaffoldActionException($this, 'setSpecialConditions expects array or callable');
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
     * @throws ScaffoldActionException
     */
    public function setJsInitiator($jsFunctionName) {
        if (!is_string($jsFunctionName) && !preg_match('%^[$_a-zA-Z][a-zA-Z0-9_.\[\]\'"]+$%s', $jsFunctionName)) {
            throw new ScaffoldActionException($this, "Invalid JavaScript funciton name: [$jsFunctionName]");
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