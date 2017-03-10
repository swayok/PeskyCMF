<?php

namespace PeskyCMF\Scaffold\Form;

class KeyValueSetFormInput extends FormInput {

    protected $minValuesCount = 0;
    protected $maxValuesCount = 0; //< 0 = infinite
    /** @var string */
    protected $keysLabel;
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
        return $this->keysLabel ?: $this->getScaffoldSectionConfig()->translate($this, '_key');
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
     * @return string
     */
    public function getValuesLabel() {
        return $this->valuesLabel ?: $this->getScaffoldSectionConfig()->translate($this, '_value');
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
        return $this->addRowButtonLabel ?: cmfTransGeneral('.form.input.key_value_set.add_row');
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
        return $this->deleteRowButtonLabel ?: cmfTransGeneral('.form.input.key_value_set.delete_row');
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

    public function getValidators() {
        return [
            $this->getName() => 'array|min:' . $this->getMinValuesCount() . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'array',
            $this->getName() . '.*.key' => 'required|string',
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