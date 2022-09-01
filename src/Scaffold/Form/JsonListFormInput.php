<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

class JsonListFormInput extends FormInput
{
    
    protected int $initalRowsCount = 1;
    protected int $minValuesCount = 0;
    protected int $maxValuesCount = 0; //< 0 = infinite
    
    protected ?string $tableHeaderForValue = null;
    protected ?string $addRowButtonLabel = null;
    protected ?string $deleteRowButtonLabel = null;
    
    public function getName(bool $forHtmlInput = false): string
    {
        $name = parent::getName($forHtmlInput);
        return $forHtmlInput ? $name . '[]' : $name;
    }
    
    public function setMinValuesCount(int $minValuesCount): static
    {
        $this->minValuesCount = max(0, $minValuesCount);
        return $this;
    }
    
    public function getMinValuesCount(): int
    {
        return $this->minValuesCount;
    }
    
    public function setMaxValuesCount(int $maxValuesCount): static
    {
        $this->maxValuesCount = max(0, $maxValuesCount);
        return $this;
    }
    
    public function getMaxValuesCount(): int
    {
        return $this->maxValuesCount;
    }
    
    public function setInitialRowsCount(int $initialRowsCount): static
    {
        $this->initalRowsCount = max(0, $initialRowsCount);
        return $this;
    }
    
    public function getInitialRowsCount(): int
    {
        return $this->initalRowsCount;
    }
    
    public function setAddRowButtonLabel(string $addRowButtonLabel): static
    {
        $this->addRowButtonLabel = $addRowButtonLabel;
        return $this;
    }
    
    public function getAddRowButtonLabel(): string
    {
        return $this->addRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.add_row');
    }
    
    public function setDeleteRowButtonLabel(string $deleteRowButtonLabel): static
    {
        $this->deleteRowButtonLabel = $deleteRowButtonLabel;
        return $this;
    }
    
    public function getDeleteRowButtonLabel(): string
    {
        return $this->deleteRowButtonLabel ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.delete_row');
    }
    
    public function setTableHeaderForValue(string $tableHeaderForValue): static
    {
        $this->tableHeaderForValue = $tableHeaderForValue;
        return $this;
    }
    
    public function getTableHeaderForValue(): string
    {
        return $this->tableHeaderForValue
            ?: $this->getScaffoldSectionConfig()->translateGeneral(
                'input.key_value_set.table_header_for_abstract_value'
            );
    }
    
    public function getType(): string
    {
        return static::TYPE_TEXT;
    }
    
    protected function getDefaultRenderer(): \Closure
    {
        return function () {
            $renderer = new InputRenderer();
            $renderer->setTemplate('cmf::input.json_list');
            return $renderer;
        };
    }
    
    public function getValidators(bool $isCreation): array
    {
        return [
            $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount(
                ) . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'nullable|string',
        ];
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
    
    public function modifySubmitedValueBeforeValidation(mixed $value, array $data): array
    {
        if ($this->hasSubmittedValueModifier()) {
            return call_user_func($this->getSubmittedValueModifier(), $value, $data);
        } elseif (is_array($value)) {
            return array_filter($value, function ($value) {
                return !empty($value);
            });
        } else {
            return $value;
        }
    }
}
