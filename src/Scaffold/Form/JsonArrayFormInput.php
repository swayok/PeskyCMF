<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;

class JsonArrayFormInput extends FormInput
{
    
    protected int $minValuesCount = 0;
    protected int $maxValuesCount = 0; //< 0 = infinite
    
    /** @var FormInput[] */
    protected array $subInputs = [];
    protected string $addRowButtonLabel;
    protected string $deleteRowButtonLabel;
    
    protected ?\Closure $validatorsForSubInputs = null;
    protected ?\Closure $submittedRowsFilter = null;
    
    /**
     * @param FormInput[]|string[] $subInputs = ['name1', 'name2' => FormInput::create()]
     */
    public function setSubInputs(array $subInputs): static
    {
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
     * @return static
     * @noinspection PhpDocSignatureInspection
     */
    public function addSubInput(string $name, ?FormInput $subInputConfig): static
    {
        if (!$subInputConfig) {
            $subInputConfig = FormInput::create();
        }
        $subInputConfig->setIsLinkedToDbColumn(false);
        if ($this->hasName()) {
            $subInputConfig->setName($this->makeFullSubInputName($name));
        }
        $subInputConfig->setLabel('');
        $subInputConfig->setPosition(count($this->subInputs));
        $subInputConfig->setScaffoldSectionConfig($this->getScaffoldSectionConfig());
        $this->subInputs[$name] = $subInputConfig;
        return $this;
    }
    
    /**
     * @return FormInput[]
     */
    public function getSubInputs(): array
    {
        return $this->subInputs;
    }
    
    public function setName(string $name): static
    {
        parent::setName($name);
        foreach ($this->subInputs as $inputName => $input) {
            $input->setName($this->makeFullSubInputName($inputName));
        }
        return $this;
    }
    
    public function setScaffoldSectionConfig(ScaffoldSectionConfig $scaffoldSectionConfig): static
    {
        parent::setScaffoldSectionConfig($scaffoldSectionConfig);
        foreach ($this->subInputs as $input) {
            $input->setScaffoldSectionConfig($scaffoldSectionConfig);
        }
        return $this;
    }
    
    /**
     * Signature:
     * function (bool $isCreation, array $defaultValidators): array { return ['input_name.*.subinput_name' => 'required']; }
     */
    public function setValidatorsForSubInputs(\Closure $validators): static
    {
        $this->validatorsForSubInputs = $validators;
        return $this;
    }
    
    protected function getValidatorsForSubInputs(bool $isCreation): array
    {
        $validators = [];
        foreach ($this->subInputs as $input) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $validators = array_merge($validators, $input->getValidators($isCreation));
        }
        if ($this->validatorsForSubInputs) {
            return call_user_func($this->validatorsForSubInputs, $isCreation, $validators);
        }
        return $validators;
    }
    
    public function getMinValuesCount(): int
    {
        return $this->minValuesCount;
    }
    
    public function setMinValuesCount(int $minValuesCount): static
    {
        $this->minValuesCount = max(0, $minValuesCount);
        return $this;
    }
    
    public function getMaxValuesCount(): int
    {
        return $this->maxValuesCount;
    }
    
    public function setMaxValuesCount(int $maxValuesCount): static
    {
        $this->maxValuesCount = max(0, $maxValuesCount);
        return $this;
    }
    
    public function getAddRowButtonLabel(): string
    {
        return $this->addRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.add_row');
    }
    
    public function setAddRowButtonLabel(string $addRowButtonLabel): static
    {
        $this->addRowButtonLabel = $addRowButtonLabel;
        return $this;
    }
    
    public function getDeleteRowButtonLabel(): string
    {
        return $this->deleteRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.delete_row');
    }
    
    public function setDeleteRowButtonLabel(string $deleteRowButtonLabel): static
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
            $renderer->setTemplate('cmf::input.json_array');
            return $renderer;
        };
    }
    
    public function getValidators(bool $isCreation): array
    {
        return array_merge(
            [
                $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount(
                    ) . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
                $this->getName() . '.*' => 'required|array',
            ],
            $this->getValidatorsForSubInputs($isCreation)
        );
    }
    
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record): array
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
        return $value;
    }
    
    /**
     * Signature:
     * function(mixed $row): bool { return true; }
     */
    public function setSubmittedRowsFilter(\Closure $filter): static
    {
        $this->submittedRowsFilter = $filter;
        return $this;
    }
    
    /**
     * @param mixed $value
     * @param array $data
     * @return string|array
     */
    public function modifySubmitedValueBeforeValidation(mixed $value, array $data): array|string
    {
        if ($this->submittedRowsFilter && is_array($value)) {
            $filteredRows = [];
            foreach ($value as $row) {
                if (call_user_func($this->submittedRowsFilter, $row)) {
                    $filteredRows[] = $row;
                }
            }
            $value = $filteredRows;
        }
        return parent::modifySubmitedValueBeforeValidation($value, $data);
    }
    
    protected function hasName(): bool
    {
        return !empty($this->name);
    }
    
    protected function makeFullSubInputName(string $inputName): string
    {
        return $this->getName() . '..' . $inputName;
    }
    
}
