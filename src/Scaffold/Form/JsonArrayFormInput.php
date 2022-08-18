<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;

class JsonArrayFormInput extends FormInput {

    protected $minValuesCount = 0;
    protected $maxValuesCount = 0; //< 0 = infinite

    /** @var FormInput[] */
    protected $subInputs = [];
    /** @var \Closure */
    protected $validatorsForSubInputs;
    /** @var string */
    protected $addRowButtonLabel;
    /** @var string */
    protected $deleteRowButtonLabel;
    /** @var \Closure */
    protected $submittedRowsFilter;
    
    /**
     * @param FormInput[]|string[] $subInputs = ['name1', 'name2' => FormInput::create()]
     * @return $this
     */
    public function setSubInputs(array $subInputs) {
        /** @var FormInput|null $config */
        foreach ($subInputs as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addSubInput($name, $config);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param FormInput|AbstractValueViewer|null $subInputConfig
     * @return $this
     */
    public function addSubInput(string $name, ?FormInput $subInputConfig) {
        if (!$subInputConfig) {
            $subInputConfig = FormInput::create();
        }
        $subInputConfig->setIsLinkedToDbColumn(false);
        if ($this->hasName()) {
            $subInputConfig->setName($this->makeFullSubInputName($name));
        }
        $subInputConfig->setLabel(false);
        $subInputConfig->setPosition(count($this->subInputs));
        $subInputConfig->setScaffoldSectionConfig($this->getScaffoldSectionConfig());
        $this->subInputs[$name] = $subInputConfig;
        return $this;
    }

    /**
     * @return FormInput[]
     */
    public function getSubInputs(): array {
        return $this->subInputs;
    }

    /**
     * @return $this
     */
    public function setName(string $name) {
        parent::setName($name);
        foreach ($this->subInputs as $inputName => $input) {
            $input->setName($this->makeFullSubInputName($inputName));
        }
        return $this;
    }

    /**
     * @param ScaffoldSectionConfig|null $scaffoldSectionConfig
     * @return $this
     */
    public function setScaffoldSectionConfig(?ScaffoldSectionConfig $scaffoldSectionConfig) {
        parent::setScaffoldSectionConfig($scaffoldSectionConfig);
        foreach ($this->subInputs as $input) {
            $input->setScaffoldSectionConfig($scaffoldSectionConfig);
        }
        return $this;
    }

    /**
     * @param \Closure $validators = function (bool $isCreation, array $defaultValidators) { return ['input_name.*.subinput_name' => 'required']; }
     * @return $this
     */
    public function setValidatorsForSubInputs(\Closure $validators) {
        $this->validatorsForSubInputs = $validators;
        return $this;
    }

    protected function getValidatorsForSubInputs(bool $isCreation): array {
        $validators = [];
        foreach ($this->subInputs as $name => $input) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $validators = array_merge($validators, $input->getValidators($isCreation));
        }
        if ($this->validatorsForSubInputs) {
            return call_user_func($this->validatorsForSubInputs, $isCreation, $validators);
        }
        return $validators;
    }

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
    public function getType(): string
    {
        return static::TYPE_HIDDEN;
    }

    /**
     * @return \Closure
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
     */
    protected function getDefaultRenderer() {
        $renderer = new InputRenderer();
        $renderer->setTemplate('cmf::input.json_array');
        return $renderer;
    }

    public function getValidators($isCreation) {
        return array_merge(
            [
                $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount() . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
                $this->getName() . '.*' => 'required|array',
            ],
            $this->getValidatorsForSubInputs($isCreation)
        );
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
        return $value;
    }
    
    /**
     * @param \Closure $filter = function($row): bool { return true; }
     * @return $this
     */
    public function setSubmittedRowsFilter(\Closure $filter) {
        $this->submittedRowsFilter = $filter;
        return $this;
    }
    
    public function modifySubmitedValueBeforeValidation($value, array $data) {
        if ($this->submittedRowsFilter && is_array($value)) {
            $filteredRows = [];
            foreach ($value as $index => $row) {
                if (call_user_func($this->submittedRowsFilter, $row)) {
                    $filteredRows[] = $row;
                }
            }
            $value = $filteredRows;
        }
        return parent::modifySubmitedValueBeforeValidation($value, $data);
    }
    
    protected function hasName(): bool {
        return !empty($this->name);
    }

    protected function makeFullSubInputName(string $inputName): string {
        return $this->getName() . '..' . $inputName;
    }


}
