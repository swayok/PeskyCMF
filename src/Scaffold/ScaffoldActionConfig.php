<?php

namespace PeskyCMF\Scaffold;

use App\Db\BaseDbModel;
use PeskyCMF\Scaffold\DataGrid\DataGridFieldConfig;
use PeskyCMF\Scaffold\Form\FormFieldConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsFieldConfig;
use phpDocumentor\Reflection\DocBlock\Tag;

abstract class ScaffoldActionConfig {
    /** @var BaseDbModel */
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
     * @var Tag[]
     */
    protected $toolbarItems = array();
    /** @var bool */
    protected $isItemDetailsAllowed = true;
    /** @var bool */
    protected $isCreateAllowed = true;
    /** @var bool */
    protected $isEditAllowed = true;
    /** @var bool */
    protected $isDeleteAllowed = true;

    /**
     * @param BaseDbModel $model
     * @return $this
     */
    static public function create(BaseDbModel $model) {
        $class = get_called_class();
        return new $class($model);
    }

    /**
     * ScaffoldActionConfig constructor.
     * @param BaseDbModel $model
     */
    public function __construct(BaseDbModel $model) {
        $this->model = $model;
    }

    /**
     * @param array $fields
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setFields(array $fields) {
        $model = $this->getModel();
        $index = 0;
        $processedFields = [];
        /** @var ScaffoldFieldConfig $config */
        foreach ($fields as $name => $config) {
            if (is_integer($name)) {
                $name = $config;
                $config = null;
            }
            if (!$model->hasTableColumn($name)) {
                throw new ScaffoldActionException($this, "Unknown table column [$name]");
            }
            if (empty($config)) {
                $config = $this->createFieldConfig($name);
            }
            $config->setName($name);
            $config->setPosition($index);
            $config->setScaffoldActionConfig($this);
            $processedFields[$name] = $config;
            $index++;
        }
        $this->fields = $processedFields;
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
     * @throws ScaffoldActionException
     */
    public function createFieldConfig($fieldName) {
        throw new ScaffoldActionException($this, 'createFieldConfig() mthod not implemented');
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
     * @return BaseDbModel
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
        return $this->toolbarItems;
    }

    /**
     * @param Tag[] $toolbarItems
     * @return $this
     * @throws ScaffoldActionException
     */
    public function setToolbarItems(array $toolbarItems) {
        foreach ($toolbarItems as &$toolbarItem) {
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
        $this->toolbarItems = $toolbarItems;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->isCreateAllowed;
    }

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsCreationAllowed($isAllowed) {
        $this->isCreateAllowed = !!$isAllowed;
        return $this;
    }

    /**
     * @return $this
     */
    public function allowCreate() {
        $this->isCreateAllowed = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowCreate() {
        $this->isCreateAllowed = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->isEditAllowed;
    }

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsEditingAllowed($isAllowed) {
        $this->isEditAllowed = !!$isAllowed;
        return $this;
    }

    /**
     * @return $this
     */
    public function allowEdit() {
        $this->isEditAllowed = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowEdit() {
        $this->isEditAllowed = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->isDeleteAllowed;
    }

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsDeleteAllowed($isAllowed) {
        $this->isDeleteAllowed = !!$isAllowed;
        return $this;
    }

    /**
     * @return $this
     */
    public function allowDelete() {
        $this->isDeleteAllowed = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowDelete() {
        $this->isDeleteAllowed = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isItemDetailsAllowed() {
        return $this->isItemDetailsAllowed;
    }

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsItemDetailsAllowed($isAllowed) {
        $this->isItemDetailsAllowed = !!$isAllowed;
        return $this;
    }

    /**
     * @return $this
     */
    public function allowItemDetails() {
        $this->isItemDetailsAllowed = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowItemDetails() {
        $this->isItemDetailsAllowed = false;
        return $this;
    }

}