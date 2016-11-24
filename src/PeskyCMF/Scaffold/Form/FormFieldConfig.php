<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldFieldException;
use PeskyCMF\Scaffold\ScaffoldRenderableFieldConfig;
use PeskyORM\ORM\Column;

class FormFieldConfig extends ScaffoldRenderableFieldConfig {

    /** @var bool */
    protected $showOnCreate = true;
    /** @var bool */
    protected $showOnEdit = true;

    const TYPE_STRING = Column::TYPE_STRING;
    const TYPE_PASSWORD = Column::TYPE_PASSWORD;
    const TYPE_EMAIL = Column::TYPE_EMAIL;
    const TYPE_TEXT = Column::TYPE_TEXT;
    const TYPE_WYSIWYG = 'wysiwyg';
    const TYPE_BOOL = Column::TYPE_BOOL;
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_TAGS = 'tags';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_DATE = Column::TYPE_DATE;
    const TYPE_DATETIME = Column::TYPE_TIMESTAMP;
    const TYPE_DATE_RANGE = 'daterange';
    const TYPE_DATETIME_RANGE = 'datetimerange';
    const TYPE_TIME = Column::TYPE_TIME;
    const TYPE_TIME_RANGE = 'timerange';

    /**
     * value can be:
     * 1. <array> of pairs:
     *      'option_value' => 'option_label'
     * 3. callable
     * @var null|array|callable
     */
    protected $options = null;

    /** @var callable|null */
    protected $optionsLoader = null;

    /**
     * Default input id
     * @return string
     * @throws ScaffoldFieldException
     */
    public function getDefaultId() {
        return static::makeDefaultId($this->getName());
    }

    /**
     * Make default html id for input using $fieldName
     * @param string $fieldName
     * @return string
     */
    static public function makeDefaultId($fieldName) {
        return preg_replace('%[^a-zA-Z0-9-]+%', '_', $fieldName) . '-input';
    }

    /**
     * @return null|array|callable
     */
    public function getOptions() {
        return $this->hasOptionsLoader() ? [] : $this->options;
    }

    /**
     * @param null|array|callable $options
     * @return $this
     * @throws ScaffoldFieldException
     */
    public function setOptions($options) {
        if (!is_array($options) && !is_callable($options)) {
            throw new ScaffoldFieldException($this, '$options should be an array');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * @param callable $loader = function (FormFieldConfig $fieldConfig, FormConfig $formConfig, $pkValue = null) { return [] }
     * @return $this
     */
    public function setOptionsLoader(callable $loader) {
        $this->optionsLoader = $loader;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getOptionsLoader() {
        return $this->optionsLoader;
    }

    /**
     * @return bool
     */
    public function hasOptionsLoader() {
        return !empty($this->optionsLoader);
    }

    /**
     * @return boolean
     */
    public function isShownOnCreate() {
        return $this->showOnCreate;
    }

    /**
     * @return $this
     */
    public function showOnCreate() {
        $this->showOnCreate = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnCreate() {
        $this->showOnCreate = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShownOnEdit() {
        return $this->showOnEdit;
    }

    /**
     * @return $this
     */
    public function showOnEdit() {
        $this->showOnEdit = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnEdit() {
        $this->showOnEdit = false;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        parent::setType($type);
        return $this;
    }

    /**
     * @param string $default
     * @return string
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     */
    public function getLabel($default = '') {
        $suffix = $this->isDbField() && !$this->getTableColumn()->isValueCanBeNull() ? '*' : '';
        return parent::getLabel($default) . $suffix;
    }

}