<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ValueRenderer;

class InputRenderer extends ValueRenderer
{
    
    protected array $attributes = [];
    protected array $attributesForCreate = [];
    protected array $attributesForEdit = [];
    protected ?\Closure $options = null;
    protected ?\Closure $optionsForCreate = null;
    protected ?\Closure $optionsForEdit = null;
    protected bool $isRequired = false;
    protected ?bool $isRequiredForCreate = null;
    protected ?bool $isRequiredForEdit = null;
    protected string $prefix = '';
    protected string $suffix = '';
    
    public static function create(?string $view = null, array $attributes = []): InputRenderer
    {
        return new InputRenderer($view, $attributes);
    }
    
    public function __construct(?string $view = null, array $attributes = [])
    {
        parent::__construct($view);
        $this->attributes = $attributes;
    }
    
    /**
     * @return static
     */
    public function setAttributes(array $attributes)
    {
        if (array_key_exists('options', $attributes)) {
            $this->setOptions($attributes['options']);
            unset($attributes['options']);
        }
        $this->attributes = $attributes;
        return $this;
    }
    
    public function getAttributes(): array
    {
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
     * @param string $name
     * @param string|bool $value
     * @param bool $owerwrite
     *  - true: overwrites existing attribute value
     *  - false: do nothing if attribute value already set
     * @return static
     */
    public function addAttribute(string $name, $value, bool $owerwrite = true)
    {
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
    public function getAttribute(string $name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }
    
    /**
     * @return static
     */
    public function setAttributesForCreate(array $attributesForCreate)
    {
        $this->attributesForCreate = $attributesForCreate;
        return $this;
    }
    
    public function getAttributesForCreate(array $excludeAttributes = []): array
    {
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
     * @return static
     */
    public function setAttributesForEdit(array $attributesForEdit)
    {
        $this->attributesForEdit = $attributesForEdit;
        return $this;
    }
    
    public function getAttributesForEdit(array $excludeAttributes = []): array
    {
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
     * @return static
     */
    public function setOptions(\Closure $options)
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * @return static
     */
    public function setOptionsForCreate(\Closure $options)
    {
        $this->optionsForCreate = $options;
        return $this;
    }
    
    /**
     * @return static
     */
    public function setOptionsForEdit(\Closure $options)
    {
        $this->optionsForEdit = $options;
        return $this;
    }
    
    public function getOptions(): array
    {
        return $this->collectOptions($this->options);
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function getOptionsForCreate(): array
    {
        if (empty($this->optionsForCreate)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForCreate);
        }
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function getOptionsForEdit(): array
    {
        if (empty($this->optionsForEdit)) {
            return $this->getOptions();
        } else {
            return $this->collectOptions($this->optionsForEdit);
        }
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    private function collectOptions(\Closure $options): array
    {
        $options = $options();
        if (!is_array($options)) {
            throw new \InvalidArgumentException('$options closure must return an array');
        }
        return $options;
    }
    
    public function areOptionsDifferent(): bool
    {
        return (
            !empty($this->optionsForCreate)
            || !empty($this->optionsForEdit)
        );
    }
    
    /**
     * @param $bool
     * @return static
     */
    public function setIsRequired($bool)
    {
        $this->isRequired = (bool)$bool;
        if ($this->isRequired) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = true;
        } elseif ($this->isRequiredForCreate && $this->isRequiredForEdit) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = false;
        }
        return $this;
    }
    
    /**
     * @param $bool
     * @return static
     */
    public function setIsRequiredForCreate($bool)
    {
        $this->isRequiredForCreate = (bool)$bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }
    
    /**
     * @param $bool
     * @return static
     */
    public function setIsRequiredForEdit($bool)
    {
        $this->isRequiredForEdit = (bool)$bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }
    
    /**
     * @return static
     */
    public function required()
    {
        $this->setIsRequired(true);
        return $this;
    }
    
    /**
     * @return static
     */
    public function notRequired()
    {
        $this->setIsRequired(false);
        return $this;
    }
    
    public function isRequired(): bool
    {
        return $this->isRequired;
    }
    
    /**
     * @return static
     */
    public function requiredForCreate()
    {
        $this->setIsRequiredForCreate(true);
        return $this;
    }
    
    /**
     * @return static
     */
    public function notRequiredForCreate()
    {
        $this->setIsRequiredForCreate(false);
        return $this;
    }
    
    public function isRequiredForCreate(): bool
    {
        return $this->isRequiredForCreate ?? $this->isRequired;
    }
    
    /**
     * @return static
     */
    public function requiredForEdit()
    {
        $this->setIsRequiredForEdit(true);
        return $this;
    }
    
    /**
     * @return static
     */
    public function notRequiredForEdit()
    {
        $this->setIsRequiredForEdit(false);
        return $this;
    }
    
    public function isRequiredForEdit(): bool
    {
        return $this->isRequiredForEdit ?? $this->isRequired;
    }
    
    /**
     * Add prefix text to input (text for left side addon in terms of bootstrap css)
     * Note: not all templates support this feature
     * @return static
     */
    public function setPrefixText(string $string)
    {
        $this->prefix = trim($string);
        return $this;
    }
    
    public function hasPrefixText(): bool
    {
        return !empty($this->prefix);
    }
    
    public function getPrefixText(): string
    {
        return $this->prefix;
    }
    
    /**
     * Add suffix text to input (text for right side addon in terms of bootstrap css)
     * Note: not all templates support this feature
     * @return static
     */
    public function setSuffixText(string $string)
    {
        $this->suffix = trim($string);
        return $this;
    }
    
    public function hasSuffixText(): bool
    {
        return !empty($this->suffix);
    }
    
    public function getSuffixText(): string
    {
        return $this->suffix;
    }
    
}