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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setKeysLabel($keysLabel) {
        $this->keysLabel = (string)$keysLabel;
        return $this;
    }

    /**
     * @param \Closure $options
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setDeleteRowButtonLabel($deleteRowButtonLabel) {
        $this->deleteRowButtonLabel = (string)$deleteRowButtonLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_HIDDEN;
    }

    /**
     * @return \Closure
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            return function () {
                return $this->getDefaultRenderer();
            };
        } else {
            return $this->renderer;
        }
    }

    /**
     * @return InputRenderer
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getDefaultRenderer() {
        $renderer = new InputRenderer();
        $renderer->setTemplate('cmf::input.key_value_set');
        return $renderer;
    }

    public function getValidators($isCreation) {
        $allowedValues = $this->hasKeysOptions() ? '|in:' . implode(',', array_keys($this->getKeysOptions())) : '';
        return [
            $this->getName() => 'array|min:' . $this->getMinValuesCount() . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'array',
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

    public function doDefaultValueConversionByType($value, $type, array $record) {
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