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
    
    public function setAttributes(array $attributes): static
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
     * @param bool|string|int $value
     * @param bool $owerwrite
     *  - true: overwrites existing attribute value
     *  - false: do nothing if attribute value already set
     * @return static
     */
    public function addAttribute(string $name, bool|string|int $value, bool $owerwrite = true): static
    {
        if ($owerwrite || !array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        }
        return $this;
    }
    
    public function getAttribute(string $name, bool|string|int|null $default = null): bool|string|int|null
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }
    
    public function setAttributesForCreate(array $attributesForCreate): static
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
    
    public function setAttributesForEdit(array $attributesForEdit): static
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
    
    
    public function setOptions(\Closure $options): static
    {
        $this->options = $options;
        return $this;
    }
    
    public function setOptionsForCreate(\Closure $options): static
    {
        $this->optionsForCreate = $options;
        return $this;
    }
    
    public function setOptionsForEdit(\Closure $options): static
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
    
    public function setIsRequired(bool $bool): static
    {
        $this->isRequired = $bool;
        if ($this->isRequired) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = true;
        } elseif ($this->isRequiredForCreate && $this->isRequiredForEdit) {
            $this->isRequiredForCreate = $this->isRequiredForEdit = false;
        }
        return $this;
    }
    
    public function setIsRequiredForCreate(bool $bool): static
    {
        $this->isRequiredForCreate = $bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }
    
    public function setIsRequiredForEdit(bool $bool): static
    {
        $this->isRequiredForEdit = $bool;
        $this->isRequired = $this->isRequiredForCreate && $this->isRequiredForEdit;
        return $this;
    }
    
    public function required(): static
    {
        $this->setIsRequired(true);
        return $this;
    }
    
    public function notRequired(): static
    {
        $this->setIsRequired(false);
        return $this;
    }
    
    public function isRequired(): bool
    {
        return $this->isRequired;
    }
    
    public function requiredForCreate(): static
    {
        $this->setIsRequiredForCreate(true);
        return $this;
    }
    
    public function notRequiredForCreate(): static
    {
        $this->setIsRequiredForCreate(false);
        return $this;
    }
    
    public function isRequiredForCreate(): bool
    {
        return $this->isRequiredForCreate ?? $this->isRequired;
    }
    
    public function requiredForEdit(): static
    {
        $this->setIsRequiredForEdit(true);
        return $this;
    }
    
    public function notRequiredForEdit(): static
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
     */
    public function setPrefixText(string $string): static
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
     */
    public function setSuffixText(string $string): static
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