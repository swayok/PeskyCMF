<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldException;
use PeskyCMF\Scaffold\ValueRenderer;

class InputRenderer extends ValueRenderer {
    /** @var array */
    protected $attributes = [];
    /** @var array */
    protected $attributesForCreate = [];
    /** @var array */
    protected $attributesForEdit = [];
    /** @var array|\Closure */
    protected $options = [];
    /** @var array|\Closure */
    protected $optionsForCreate = [];
    /** @var array|\Closure */
    protected $optionsForEdit = [];
    /** @var bool */
    protected $isRequired = false;
    /** @var bool */
    protected $isRequiredForCreate = null;
    /** @var bool */
    protected $isRequiredForEdit = null;
    /** @var string */
    protected $prefix = '';
    /** @var string */
    protected $suffix = '';

    /**
     * @param null $view
     * @param array $attributes
     * @return InputRenderer
     */
    static public function create($view = null, array $attributes = []) {
        return new InputRenderer($view, $attributes);
    }

    /**
     * InputRenderer constructor.
     * @param string $view
     * @param array $attributes
     */
    public function __construct($view = null, array $attributes = []) {
        parent::__construct($view);
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes() {
        $ret = $this->attributes;
        if ($this->isRequired()) {
            $ret['required'] = true;
        }
        if (empty($ret['type']) || !in_array($ret['type'], ['checkbox', 'radio'], true)) {
            unset($ret['value']);
        }
        return $ret;
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function setAttributes($attributes) {
        if (array_key_exists('options', $attributes)) {
            $this->setOptions($attributes['options']);
            unset($attributes['options']);
        }
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @param string|bool $value
     * @param bool $owerwrite
     *  - true: overwrites existing attribute value
     *  - false: do nothing if attribute value already set
     * @return $this
     */
    public function addAttribute($name, $value, $owerwrite = true) {
        if ($owerwrite || !array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string|null|bool $default
     * @return mixed
     */
    public function getAttribute($name, $default = null) {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param array $excludeAttributes
     * @return array
     */
    public function getAttributesForCreate(array $excludeAttributes = []) {
        $ret = array_merge($this->attributes, $this->attributesForCreate);
        if ($this->isRequiredForCreate()) {
            $ret['required'] = true;
        }
        if (empty($ret['type']) || !in_array($ret['type'], ['checkbox', 'radio'], true)) {
            unset($ret['value']);
        }
        foreach ($excludeAttributes as $attr) {
            unset($ret[$attr]);
        }
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
     * @param array $excludeAttributes
     * @return array
     */
    public function getAttributesForEdit(array $excludeAttributes = []) {
        $ret = array_merge($this->attributes, $this->attributesForEdit);
        if ($this->isRequiredForEdit()) {
            $ret['required'] = true;
        }
        if (empty($ret['type']) || !in_array($ret['type'], ['checkbox', 'radio'], true)) {
            unset($ret['value']);
        }
        foreach ($excludeAttributes as $attr) {
            unset($ret[$attr]);
        }
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
     * @throws \InvalidArgumentException
     */
    public function getOptions() {
        return $this->collectOptions($this->options);
    }

    /**
     * @param array|\Closure $options
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function collectOptions($options) {
        if (!is_string($options) && $options instanceof \Closure) {
            $options = $options();
            if (!is_array($options)) {
                throw new \InvalidArgumentException('$options closure must return an array');
            }
        }
        return $options;
    }

    /**
     * @param array|\Closure $options
     * @return $this
     * @throws ScaffoldException
     */
    public function setOptions($options) {
        if (!is_array($options) && !($options instanceof \Closure)) {
            throw new ScaffoldException('Invalid $options passed to InputRenderer');
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
     * @throws \InvalidArgumentException
     */
    public function getOptionsForCreate() {
        if (empty($this->optionsForCreate)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForCreate);
        }
    }

    /**
     * @param array|\Closure $options
     * @return $this
     * @throws ScaffoldException
     * @internal param array $optionsForCreate
     */
    public function setOptionsForCreate($options) {
        if (!is_array($options) && !($options instanceof \Closure)) {
            throw new ScaffoldException('Invalid $options passed to InputRenderer');
        }
        $this->optionsForCreate = $options;
        return $this;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getOptionsForEdit() {
        if (empty($this->optionsForEdit)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForEdit);
        }
    }

    /**
     * @param array|\Closure $options
     * @return $this
     * @throws ScaffoldException
     * @internal param array $optionsForEdit
     */
    public function setOptionsForEdit($options) {
        if (!is_array($options) && !($options instanceof \Closure)) {
            throw new ScaffoldException('Invalid $options passed to InputRenderer');
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
        $this->setIsRequired(true);
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequired() {
        $this->setIsRequired(false);
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
        $this->setIsRequiredForCreate(true);
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequiredForCreate() {
        $this->setIsRequiredForCreate(false);
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
        $this->setIsRequiredForEdit(true);
        return $this;
    }

    /**
     * @return $this
     */
    public function notRequiredForEdit() {
        $this->setIsRequiredForEdit(false);
        return $this;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsRequired($bool) {
        $this->isRequired = (bool)$bool;
        if ($this->isRequired) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = true;
        } else if ($this->isRequiredForCreate && $this->isRequiredForEdit) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = false;
        }
        return $this;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsRequiredForCreate($bool) {
        $this->isRequiredForCreate = (bool)$bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsRequiredForEdit($bool) {
        $this->isRequiredForEdit = (bool)$bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }

    /**
     * Add prefix text to input (text for left side addon in terms of bootstrap css)
     * Note: not all templates support this feature
     * @param string $string
     * @return $this
     */
    public function setPrefixText($string) {
        $this->prefix = trim((string)$string);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPrefixText() {
        return !empty($this->prefix);
    }

    /**
     * @return string
     */
    public function getPrefixText() {
        return $this->prefix;
    }

    /**
     * Add suffix text to input (text for right side addon in terms of bootstrap css)
     * Note: not all templates support this feature
     * @param string $string
     * @return $this
     */
    public function setSuffixText($string) {
        $this->suffix = trim((string)$string);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSuffixText() {
        return !empty($this->suffix);
    }

    /**
     * @return string
     */
    public function getSuffixText() {
        return $this->suffix;
    }

}