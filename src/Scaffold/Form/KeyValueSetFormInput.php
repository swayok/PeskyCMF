<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

class KeyValueSetFormInput extends FormInput
{
    
    protected int $minValuesCount = 0;
    protected int $maxValuesCount = 0; //< 0 = infinite
    protected ?string $keysLabel = null;
    protected ?\Closure $keysOptions = null;
    protected ?string $valuesLabel = null;
    protected ?string $addRowButtonLabel = null;
    protected ?string $deleteRowButtonLabel = null;
    
    public function getMinValuesCount(): int
    {
        return $this->minValuesCount;
    }
    
    /**
     * @return static
     */
    public function setMinValuesCount(int $minValuesCount)
    {
        $this->minValuesCount = max(0, $minValuesCount);
        return $this;
    }
    
    public function getMaxValuesCount(): int
    {
        return $this->maxValuesCount;
    }
    
    /**
     * @return static
     */
    public function setMaxValuesCount(int $maxValuesCount)
    {
        $this->maxValuesCount = max(0, $maxValuesCount);
        return $this;
    }
    
    public function getKeysLabel(): string
    {
        return $this->keysLabel ?: $this->getScaffoldSectionConfig()->translate(null, 'input.' . $this->getNameForTranslation() . '_key');
    }
    
    /**
     * @return static
     */
    public function setKeysLabel(string $keysLabel)
    {
        $this->keysLabel = $keysLabel;
        return $this;
    }
    
    /**
     * @param \Closure $options
     * @return static
     */
    public function setKeysOptions(\Closure $options)
    {
        $this->keysOptions = $options;
        return $this;
    }
    
    public function hasKeysOptions(): bool
    {
        return !empty($this->keysOptions);
    }
    
    /**
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     */
    public function getKeysOptions(): array
    {
        if (!$this->keysOptions) {
            throw new \BadMethodCallException('Key options closure is not set. Maybe you have missed hasKeysOptions() method call.');
        }
        $options = value($this->keysOptions);
        if (!is_array($options)) {
            throw new \UnexpectedValueException('Keys options closure must return an array');
        }
        return $options;
    }
    
    public function getValuesLabel(): string
    {
        return $this->valuesLabel ?: $this->getScaffoldSectionConfig()->translate(null, 'input.' . $this->getNameForTranslation() . '_value');
    }
    
    /**
     * @return static
     */
    public function setValuesLabel(string $valuesLabel)
    {
        $this->valuesLabel = $valuesLabel;
        return $this;
    }
    
    public function getAddRowButtonLabel(): string
    {
        return $this->addRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.add_row');
    }
    
    /**
     * @return static
     */
    public function setAddRowButtonLabel(string $addRowButtonLabel)
    {
        $this->addRowButtonLabel = $addRowButtonLabel;
        return $this;
    }
    
    public function getDeleteRowButtonLabel(): string
    {
        return $this->deleteRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.delete_row');
    }
    
    /**
     * @return static
     */
    public function setDeleteRowButtonLabel(string $deleteRowButtonLabel)
    {
        $this->deleteRowButtonLabel = $deleteRowButtonLabel;
        return $this;
    }
    
    public function getType(): string
    {
        return static::TYPE_HIDDEN;
    }
    
    protected function getDefaultRenderer(): \Closure
    {
        return function () {
            $renderer = new InputRenderer();
            $renderer->setTemplate('cmf::input.key_value_set');
            return $renderer;
        };
    }
    
    public function getValidators(bool $isCreation): array
    {
        $allowedValues = $this->hasKeysOptions() ? '|in:' . implode(',', array_keys($this->getKeysOptions())) : '';
        return [
            $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount(
                ) . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'nullable|array',
            $this->getName() . '.*.key' => 'required|string' . $allowedValues,
            $this->getName() . '.*.value' => 'required',
        ];
    }
    
    public function modifySubmitedValueBeforeValidation($value, array $data)
    {
        $value = parent::modifySubmitedValueBeforeValidation($value, $data);
        if (is_array($value)) {
            foreach ($value as $index => $keyValuePair) {
                if (empty($keyValuePair['key']) || trim($keyValuePair['key']) === '') {
                    // remove record with empty key
                    unset($value[$index]);
                }
            }
        }
        return $value;
    }
    
    public function doDefaultValueConversionByType($value, string $type, array $record): array
    {
        if (!is_array($value)) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException('$value argument must be a string or array');
            }
            $value = json_decode($value, true);
            if (!is_array($value)) {
                $value = [];
            }
        }
        /** @var array $value */
        $ret = [];
        foreach ($value as $key => $valueForKey) {
            if (is_int($key)) {
                $ret[] = $valueForKey;
            } else {
                $ret[] = [
                    'key' => $key,
                    'value' => $valueForKey,
                ];
            }
        }
        return $ret;
    }
    
    
}