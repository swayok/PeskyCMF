<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ScaffoldFieldRendererConfig;

class InputRendererConfig extends ScaffoldFieldRendererConfig {
    /** @var array */
    protected $attributes = [];
    /** @var array */
    protected $attributesForCreate = [];
    /** @var array */
    protected $attributesForEdit = [];
    /** @var array|callable */
    protected $options = [];
    /** @var array|callable */
    protected $optionsForCreate = [];
    /** @var array|callable */
    protected $optionsForEdit = [];
    /** @var bool */
    protected $isRequired = false;
    /** @var bool */
    protected $isRequiredForCreate = null;
    /** @var bool */
    protected $isRequiredForEdit = null;

    /**
     * @param null $view
     * @param array $attributes
     * @return InputRendererConfig
     */
    static public function create($view = null, array $attributes = []) {
        return new InputRendererConfig($view, $attributes);
    }

    /**
     * InputRendererConfig constructor.
     * @param string $view
     * @param array $attributes
     */
    public function __construct($view = null, array $attributes = []) {
        parent::__construct($view);
        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * @return array
     */
    public function getAttributes() {
        $ret = $this->attributes;
        if ($this->isRequired()) {
            $ret['required'] = true;
        }
        return $ret;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributesForCreate() {
        $ret = array_merge($this->attributes, $this->attributesForCreate);
        if ($this->isRequiredForCreate()) {
            $ret['required'] = true;
        }
        unset($ret['value']);
        return $ret;
    }

    /**
     * @param array $attributesForCreate
     * @return $this
     */
    public function setAttributesForCreate($attributesForCreate) {
        $this->attributesForCreate = $attributesForCreate;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributesForEdit() {
        $ret = array_merge($this->attributes, $this->attributesForEdit);
        if ($this->isRequiredForEdit()) {
            $ret['required'] = true;
        }
        unset($ret['value']);
        return $ret;
    }

    /**
     * @param array $attributesForEdit
     * @return $this
     */
    public function setAttributesForEdit($attributesForEdit) {
        $this->attributesForEdit = $attributesForEdit;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->collectOptions($this->options);
    }

    /**
     * @param array|callable $options
     * @return mixed
     */
    private function collectOptions($options) {
        if (!is_string($options) && is_callable($options)) {
            return call_user_func($options);
        } else {
            return $options;
        }
    }

    /**
     * @param array|callable $options
     * @return $this
     * @throws ScaffoldException
     */
    public function setOptions($options) {
        if (!is_array($options) && !is_callable($options)) {
            throw new ScaffoldException('Invalid $options passed to InputRendererConfig');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * @return bool
     */
    public function areOptionsDifferent() {
        return (
            !empty($this->optionsForCreate)
            || !empty($this->optionsForEdit)
        );
    }

    /**
     * @return array
     */
    public function getOptionsForCreate() {
        if (empty($this->optionsForCreate)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForCreate);
        }
    }

    /**
     * @param array|callable $options
     * @return $this
     * @throws ScaffoldException
     * @internal param array $optionsForCreate
     */
    public function setOptionsForCreate($options) {
        if (!is_array($options) && !is_callable($options)) {
            throw new ScaffoldException('Invalid $options passed to InputRendererConfig');
        }
        $this->optionsForCreate = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptionsForEdit() {
        if (empty($this->optionsForEdit)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForEdit);
        }
    }

    /**
     * @param array|callable $options
     * @return $this
     * @throws ScaffoldException
     * @internal param array $optionsForEdit
     */
    public function setOptionsForEdit($options) {
        if (!is_array($options) && !is_callable($options)) {
            throw new ScaffoldException('Invalid $options passed to InputRendererConfig');
        }
        $this->optionsForEdit = $options;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequired() {
        return $this->isRequired;
    }

    /**
     * @return $this
     */
    public function required() {
        $this->isRequired = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequired() {
        $this->isRequired = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequiredForCreate() {
        return $this->isRequiredForCreate === null ? $this->isRequired : $this->isRequiredForCreate;
    }

    /**
     * @return $this
     */
    public function requiredForCreate() {
        $this->isRequiredForCreate = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequiredForCreate() {
        $this->isRequiredForCreate = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequiredForEdit() {
        return $this->isRequiredForEdit === null ? $this->isRequired : $this->isRequiredForEdit;
    }

    /**
     * @return $this
     */
    public function requiredForEdit() {
        $this->isRequiredForEdit = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequiredForEdit() {
        $this->isRequiredForEdit = false;
        return $this;
    }

}