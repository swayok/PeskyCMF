<?php

namespace PeskyCMF\Scaffold\Form;

class JsonListFormInput extends FormInput {

    protected $initalRowsCount = 1;
    protected $minValuesCount = 0;
    protected $maxValuesCount = 0; //< 0 = infinite

    /** @var string */
    protected $tableHeaderForValue;
    /** @var string */
    protected $addRowButtonLabel;
    /** @var string */
    protected $deleteRowButtonLabel;

    public function getName($forHtmlInput = false) {
        $name = parent::getName($forHtmlInput);
        return $forHtmlInput ? $name . '[]' : $name;
    }

    /**
     * @return int
     */
    public function getMinValuesCount(): int {
        return $this->minValuesCount;
    }

    /**
     * @param int $minValuesCount
     * @return $this
     */
    public function setMinValuesCount(int $minValuesCount) {
        $this->minValuesCount = max(0, $minValuesCount);
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxValuesCount(): int {
        return $this->maxValuesCount;
    }

    /**
     * @param int $maxValuesCount
     * @return $this
     */
    public function setMaxValuesCount(int $maxValuesCount) {
        $this->maxValuesCount = max(0, $maxValuesCount);
        return $this;
    }

    /**
     * @return int
     */
    public function getInitialRowsCount(): int {
        return $this->initalRowsCount;
    }

    /**
     * @param int $initialRowsCount
     * @return $this
     */
    public function setInitialRowsCount(int $initialRowsCount) {
        $this->initalRowsCount = max(0, $initialRowsCount);
        return $this;
    }

    /**
     * @return string
     */
    public function getAddRowButtonLabel(): string {
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
    public function getDeleteRowButtonLabel(): string {
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
    public function getTableHeaderForValue(): string {
        return $this->tableHeaderForValue ?: $this->getScaffoldSectionConfig()->translateGeneral('input.key_value_set.table_header_for_abstract_value');
    }

    /**
     * @param string $tableHeaderForValue
     * @return $this
     */
    public function setTableHeaderForValue($tableHeaderForValue) {
        $this->tableHeaderForValue = (string)$tableHeaderForValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::TYPE_TEXT;
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
    protected function getDefaultRenderer(): InputRenderer {
        $renderer = new InputRenderer();
        $renderer->setTemplate('cmf::input.json_list');
        return $renderer;
    }

    public function getValidators($isCreation) {
        return [
            $this->getName() => ($this->getMinValuesCount() > 0 ? 'required' : 'nullable') . '|array|min:' . $this->getMinValuesCount() . ($this->getMaxValuesCount() > 0 ? '|max:' . $this->getMaxValuesCount() : ''),
            $this->getName() . '.*' => 'nullable|string',
        ];
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

    public function modifySubmitedValueBeforeValidation($values, array $data) {
        if ($this->hasSubmittedValueModifier()) {
            return call_user_func($this->getSubmittedValueModifier(), $values, $data);
        } else if (is_array($values)) {
            return array_filter($values, function ($value) {
                return !empty($value);
            });
        } else {
            return $values;
        }
    }
}
