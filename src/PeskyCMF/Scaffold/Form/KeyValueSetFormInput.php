<?php

namespace PeskyCMF\Scaffold\Form;

class KeyValueSetFormInput extends FormInput {

    protected $minValuesCount = 0;
    protected $maxValuesCount = 0; //< 0 = infinite
    protected $keysLabel;
    protected $valuesLabel;

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
     * @return mixed
     */
    public function getKeysLabel() {
        return $this->keysLabel ?: cmfTransCustom('.' . $this->getScaffoldSectionConfig()->getTable()->getName() . '.form.input.' . $this->getName() . '_key');
    }

    /**
     * @param mixed $keysLabel
     * @return $this
     */
    public function setKeysLabel($keysLabel) {
        $this->keysLabel = $keysLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValuesLabel() {
        return $this->valuesLabel ?: cmfTransCustom('.' . $this->getScaffoldSectionConfig()->getTable()->getName() . '.form.input.' . $this->getName() . '_value');
    }

    /**
     * @param mixed $valuesLabel
     * @return $this
     */
    public function setValuesLabel($valuesLabel) {
        $this->valuesLabel = $valuesLabel;
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
        $renderer
            ->setTemplate('cmf::input.key_value_set')
            ->addData('minValuesCount', $this->getMinValuesCount())
            ->addData('maxValuesCount', $this->getMaxValuesCount())
            ->addData('keysLabel', $this->getKeysLabel())
            ->addData('valuesLabel', $this->getValuesLabel());
        return $renderer;
    }

    public function getValidators() {
        return [
            $this->getName() . '.*' => 'array|min:' . $this->getMinValuesCount() . '|max:' . $this->getMaxValuesCount(),
            $this->getName() . '.*.key' => 'required',
            $this->getName() . '.*.value' => 'required'
        ];
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (!is_array($value)) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException('$value argument must be a string or array');
            }
            $value = json_decode($value);
            if (!is_array($value)) {
                $value = [];
            }
        }
        /** @var array $value*/
        $ret = [];
        foreach ($value as $key => $valueForKey) {
            $ret = [
                'key' => $key,
                'value' => $valueForKey
            ];
        }
        return $ret;
    }


}