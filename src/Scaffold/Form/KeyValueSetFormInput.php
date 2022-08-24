<?php

namespace PeskyCMF\Scaffold\Form;

class KeyValueSetFormInput extends FormInput {

    protected $minValuesCount = 0;
    protected $maxValuesCount = 0; //< 0 = infinite
    /** @var string */
    protected $keysLabel;
    /** @var \Closure|null */
    protected $keysOptions;
    /** @var string */
    protected $valuesLabel;
    /** @var string */
    protected $addRowButtonLabel;
    /** @var string */
    protected $deleteRowButtonLabel;

    /**
     * @return int
     */
    public function getMinValuesCount() {
        return $this->minValuesCount;
    }

    /**
     * @param int $minValuesCount
     * @return static
     */
    public function setMinValuesCount($minValuesCount) {
        $this->minValuesCount = max(0, (int)$minValuesCount);
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxValuesCount() {
        return $this->maxValuesCount;
    }

    /**
     * @param int $maxValuesCount
     * @return static
     */
    public function setMaxValuesCount($maxValuesCount) {
        $this->maxValuesCount = max(0, (int)$maxValuesCount);
        return $this;
    }

    /**
     * @return string
     */
    public function getKeysLabel() {
        return $this->keysLabel ?: $this->getScaffoldSectionConfig()->translate(null, 'input.' . $this->getNameForTranslation() . '_key');
    }

    /**
     * @param string $keysLabel
     * @return static
     */
    public function setKeysLabel($keysLabel) {
        $this->keysLabel = (string)$keysLabel;
        return $this;
    }

    /**
     * @param \Closure $options
     * @return static
     */
    public function setKeysOptions(\Closure $options) {
        $this->keysOptions = $options;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasKeysOptions() {
        return !empty($this->keysOptions);
    }

    /**
     * @return array|mixed
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     */
    public function getKeysOptions() {
        if (!$this->keysOptions) {
            throw new \BadMethodCallException('Key options closure is not set. Maybe you have missed hasKeysOptions() method call.');
        }
        $options = value($this->keysOptions);
        if (!is_array($options)) {
            throw new \UnexpectedValueException('Keys options closure must return an array');
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getValuesLabel() {
        return $this->valuesLabel ?: $this->getScaffoldSectionConfig()->translate(null, 'input.' . $this->getNameForTranslation() . '_value');
    }

    /**
     * @param string $valuesLabel
     * @return static
     */
    public function setValuesLabel($valuesLabel) {
        $this->valuesLabel = (string)$valuesLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddRowButtonLabel() {
        return $this->addRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.add_row');
    }

    /**
     * @param string $addRowButtonLabel
     * @return static
     */
    public function setAddRowButtonLabel($addRowButtonLabel) {
        $this->addRowButtonLabel = $addRowButtonLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeleteRowButtonLabel() {
        return $this->deleteRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.delete_row');
    }

    /**
     * @param string $deleteRowButtonLabel
     * @return static
     */
    public function setDeleteRowButtonLabel($deleteRowButtonLabel) {
        $this->deleteRowButtonLabel = (string)$deleteRowButtonLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::TYPE_HIDDEN;
    }

    protected function getDefaultRenderer(): \Closure {
        return function () {
            $renderer = new InputRenderer();
            $renderer->setTemplate('cmf::input.key_value_set');
            return $renderer;
        };
    }

    public function getValidators(bool $isCreation): array {
        $allowedValues = $this->hasKeysOptions() ? '|in:' . implode(',', array_keys($this->getKeysOptions())) : '';
        return [
            $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount() . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'nullable|array',
            $this->getName() . '.*.key' => 'required|string' . $allowedValues,
            $this->getName() . '.*.value' => 'required'
        ];
    }

    public function modifySubmitedValueBeforeValidation($value, array $data) {
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

    public function doDefaultValueConversionByType($value, string $type, array $record): array {
        if (!is_array($value)) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException('$value argument must be a string or array');
            }
            $value = json_decode($value, true);
            if (!is_array($value)) {
                $value = [];
            }
        }
        /** @var array $value*/
        $ret = [];
        foreach ($value as $key => $valueForKey) {
            if (is_int($key)) {
                $ret[] = $valueForKey;
            } else {
                $ret[] = [
                    'key' => $key,
                    'value' => $valueForKey
                ];
            }
        }
        return $ret;
    }


}