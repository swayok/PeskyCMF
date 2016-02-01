<?php

namespace PeskyCMF\Scaffold;

use PeskyCMF\Db\CmfDbModel;
use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\Form\FormFieldConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;
use phpDocumentor\Reflection\DocBlock\Tag;

abstract class ScaffoldActionConfig {
    /** @var CmfDbModel */
    protected $model;
    /** @var array */
    protected $fieldsConfigs = array();
    /**
     * Fields list to select from db
     * @var array|ScaffoldFieldConfig[]
     */
    protected $fields = array();
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
     * @var Tag[]|callable
     */
    protected $toolbarItems = array();
    /** @var array|callable */
    protected $specialConditions = [];
    /** @var ScaffoldSectionConfig */
    protected $scaffoldSection;
    /** @var array|callable */
    protected $dataToAddToRecord;
    /** @var array|callable */
    protected $dataToSendToView;

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

    /**
     * @param array $fields
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setFields(array $fields) {
        /** @var ScaffoldFieldConfig|null $config */
        foreach ($fields as $name => $config) {
            if (is_integer($name)) {
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
     * @param string $fieldName
     * @return ScaffoldFieldConfig
     * @throws ScaffoldException
     */
    static public function createFieldConfig($fieldName) {
        throw new ScaffoldException(get_called_class() . '::createFieldConfig() method not implemented');
    }

    /**
     * @param string $name
     * @return DataGridFieldConfig|ItemDetailsFieldConfig|FormFieldConfig|ScaffoldFieldConfig|array
     * @throws ScaffoldActionException
     */
    public function getField($name) {
        if (empty($name) || empty($this->fields[$name])) {
            throw new ScaffoldActionException($this, "Unknown field [$name]");
        }
        return $this->fields[$name];
    }

    /**
     * @param string $name
     * @param null|ScaffoldFieldConfig $config
     * @return $this
     * @throws ScaffoldActionException
     */
    public function addField($name, $config = null) {
        if (!$this->getModel()->hasTableColumn($name)) {
            throw new ScaffoldActionException($this, "Unknown table column [$name]");
        }
        if (empty($config)) {
            $config = static::createFieldConfig($name);
        }
        $config->setName($name);
        $config->setPosition(count($this->fields));
        $config->setScaffoldActionConfig($this);
        $this->fields[$name] = $config;
        return $this;
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
     */
    public function prepareRecord(array &$record) {
        $permissions = [
            '___delete_allowed' =>(
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
        $fields = $this->getFields();
        $pkKey = $this->getModel()->getPkColumnName();
        foreach ($record as $key => $value) {
            if ($this->getModel()->hasTableRelation($key)) {
                continue;
            }
            if (empty($fields[$key])) {
                if ($key !== $pkKey) {
                    unset($record[$key]);
                }
                continue;
            }
            if (is_object($fields[$key]) && method_exists($fields[$key], 'convertValue')) {
                $record['__' . $key] = $record[$key];
                $record[$key] = $fields[$key]->convertValue($record[$key], $this->getModel()->getTableColumn($key), $record);
            }
        }
        $record += $permissions;
        if (!empty($customData) && is_array($customData)) {
            $record += $customData;
        }
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
     */
    public function getDefaultFieldRenderer() {
        return $this->defaultFieldRenderer;
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
     * @return Tag[]
     */
    public function getToolbarItems() {
        return is_callable($this->toolbarItems) ? call_user_func($this->toolbarItems, $this) : $this->toolbarItems;
    }

    /**
     * @param Tag[]|callable $arrayOrCallable
     * Examples:
     * - call some url via ajax and then run "callback(json)"
        Tag::a()
            ->setContent(trans('path.to.translation'))
            ->setClass('btn btn-warning')
            ->setDataAttr('action', 'request')
            ->setDataAttr('url', route('route', [], false))
            ->setDataAttr('method', 'put')
            ->setDataAttr('data', 'id=:id:')
            ->setDataAttr('on-success', 'callback(json);')
            ->setHref('#');
     * - redirect to other url
        Tag::a()
            ->setContent(trans('path.to.translation'))
            ->setClass('btn btn-warning')
            ->setHref('url', route('route', [], false))
            ->setTarget('_blank')
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setToolbarItems($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !is_callable($arrayOrCallable)) {
            throw new ScaffoldActionException($this, 'setRowActions($arrayOrCallable) accepts only array or callable');
        }
        if (!is_callable($arrayOrCallable)) {
            foreach ($arrayOrCallable as &$toolbarItem) {
                if (is_object($toolbarItem)) {
                    if (method_exists($toolbarItem, 'build')) {
                        $toolbarItem = $toolbarItem->build();
                    } else if (method_exists($toolbarItem, '__toString')) {
                        $toolbarItem = $toolbarItem->__toString();
                    } else {
                        throw new ScaffoldActionException($this, 'Toolbar item is an object without possibility to convert it to string');
                    }
                }
            }
        }
        $this->toolbarItems = $arrayOrCallable;
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

}