<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Validation\Rule;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

class FormInput extends RenderableValueViewer
{
    
    protected bool $showOnCreate = true;
    protected bool $showOnEdit = true;
    
    public const TYPE_READONLY_TEXT = 'readonly_text';
    public const TYPE_PASSWORD = Column::TYPE_PASSWORD;
    public const TYPE_EMAIL = Column::TYPE_EMAIL;
    public const TYPE_WYSIWYG = 'wysiwyg';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTISELECT = 'multiselect';
    public const TYPE_TAGS = 'tags';
    public const TYPE_HIDDEN = 'hidden';
    public const TYPE_DATE_RANGE = 'daterange';
    public const TYPE_DATETIME_RANGE = 'datetimerange';
    public const TYPE_TIME_RANGE = 'timerange';
    
    /**
     * value can be:
     * 1. <array> of pairs:
     *      'option_value' => 'option_label'
     * 3. \Closure
     * @var null|array|\Closure
     */
    protected $options;
    
    protected ?\Closure $optionsLoader = null;
    
    protected bool $enableOptionsFilteringByKeywords;
    protected int $minCharsRequiredToInitOptionsFiltering = 1;
    
    protected ?string $emptyOptionLabel = null;
    
    protected ?string $tooltip = null;
    protected array $disablersConfigs = [];
    protected ?\Closure $submittedValueModifier = null;
    protected string $additionalHtml = '';
    
    /**
     * Default input id
     */
    public function getDefaultId(): string
    {
        return static::makeDefaultId(
            $this->getName(),
            $this->getScaffoldSectionConfig()->getScaffoldConfig()->getResourceName()
        );
    }
    
    /**
     * @param bool $forHtmlInput - convert name into a valid HTML input name('Relation.coln_name' to 'Relation[col_name]')
     * @return string
     */
    public function getName(bool $forHtmlInput = false): string
    {
        if ($forHtmlInput) {
            $name = $this->getName(false); //< correct
            if (static::isComplexViewerName($name)) {
                $name = implode('.', static::splitComplexViewerName($name));
            }
            $nameParts = explode('.', $name);
            if (count($nameParts) > 1) {
                return $nameParts[0] . '[' . implode('][', array_slice($nameParts, 1)) . ']';
            } else {
                return $nameParts[0];
            }
        } else {
            return parent::getName();
        }
    }
    
    protected function getNameForDisablers(): string
    {
        return $this->getName(true);
    }
    
    /**
     * Make default html id for input using $formInputName
     */
    public static function makeDefaultId(string $formInputName, string $resourceName): string
    {
        return 't-' . $resourceName . '-c-' . preg_replace('%[^a-zA-Z0-9-]+%', '_', $formInputName) . '-input';
    }
    
    public function render(array $dataForTemplate = []): string
    {
        $renderedInput = parent::render($dataForTemplate);
        if ($this->isShownOnCreate() && $this->isShownOnEdit()) {
            return $renderedInput;
        } else {
            // display only when creating or editing
            return '{{? ' . ($this->isShownOnCreate() ? '' : '!') . "it._is_creation }}{$renderedInput}{{?}}";
        }
    }
    
    /**
     * @return array|\Closure
     */
    public function getOptions()
    {
        return $this->hasOptionsLoader() ? [] : $this->options;
    }
    
    public function hasOptionsOrOptionsLoader(): bool
    {
        return $this->hasOptionsLoader() || !empty($this->options);
    }
    
    /**
     * @param null|array|\Closure $options
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !($options instanceof \Closure)) {
            throw new \InvalidArgumentException($this, '$options argument should be an array or \Closure');
        }
        $this->options = $options;
        return $this;
    }
    
    /**
     * @param \Closure $loader = function ($pkValue, $keywords, FormInput $formInput, FormConfig $formConfig) { return [] }
     * @return static
     */
    public function setOptionsLoader(\Closure $loader)
    {
        $this->optionsLoader = $loader;
        return $this;
    }
    
    /**
     * Allows autocomplete functionality (search by keywords) for <select> inputs using options loader
     * @param int $minCharsToInitFiltering - minimum characters required to initiate ajax request
     * @return static
     */
    public function enableOptionsFilteringByKeywords(int $minCharsToInitFiltering = 1)
    {
        $this->enableOptionsFilteringByKeywords = true;
        $this->minCharsRequiredToInitOptionsFiltering = $minCharsToInitFiltering;
        return $this;
    }
    
    public function getOptionsLoader(): ?\Closure
    {
        return $this->optionsLoader;
    }
    
    public function hasOptionsLoader(): bool
    {
        return !empty($this->optionsLoader);
    }
    
    public function isOptionsFilteringEnabled(): bool
    {
        return $this->enableOptionsFilteringByKeywords;
    }
    
    public function getMinCharsRequiredToInitOptionsFiltering(): int
    {
        return $this->minCharsRequiredToInitOptionsFiltering;
    }
    
    /**
     * @return static
     */
    public function setEmptyOptionLabel(string $label)
    {
        $this->emptyOptionLabel = $label;
        return $this;
    }
    
    public function getEmptyOptionLabel(): string
    {
        return $this->emptyOptionLabel ?: '';
    }
    
    public function hasEmptyOptionLabel(): bool
    {
        return $this->emptyOptionLabel !== null;
    }
    
    public function isShownOnCreate(): bool
    {
        return $this->showOnCreate;
    }
    
    /**
     * @return static
     */
    public function showOnCreate()
    {
        $this->showOnCreate = true;
        return $this;
    }
    
    /**
     * @return static
     */
    public function hideOnCreate()
    {
        $this->showOnCreate = false;
        return $this;
    }
    
    public function isShownOnEdit(): bool
    {
        return $this->showOnEdit;
    }
    
    /**
     * @return static
     */
    public function showOnEdit()
    {
        $this->showOnEdit = true;
        return $this;
    }
    
    /**
     * @return static
     */
    public function hideOnEdit()
    {
        $this->showOnEdit = false;
        return $this;
    }
    
    public function getLabel(?InputRenderer $renderer = null): string
    {
        $isRequired = '';
        if ($renderer === null) {
            $isRequired = $this->getTableColumn()->isValueRequiredToBeNotEmpty() ? '*' : '';
        } elseif ($renderer->isRequired()) {
            $isRequired = '*';
        } elseif ($renderer->isRequiredForCreate()) {
            $isRequired = '{{? !!it.isCreation }}*{{?}}';
        } elseif ($renderer->isRequiredForEdit()) {
            $isRequired = '{{? !it.isCreation }}*{{?}}';
        }
        return parent::getLabel() . $isRequired;
    }
    
    /**
     * Add some html after input
     * @return static
     */
    public function setAdditionalHtml(string $html)
    {
        $this->additionalHtml = $html;
        return $this;
    }
    
    public function getAdditionalHtml(): string
    {
        return $this->additionalHtml;
    }
    
    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }
    
    public function getFormattedTooltip(): string
    {
        if ($this->hasTooltip()) {
            return $this->buildTooltip($this->getTooltip());
        } else {
            return '';
        }
    }
    
    /**
     * @param string|array $tooltip
     * @return string
     */
    protected function buildTooltip($tooltip): string
    {
        return '<span class="help-block"><p class="mn">'
            . '<i class="glyphicon glyphicon-info-sign text-blue fs16 va-t mr5 lh20" style="top: 0;"></i>'
            . (is_array($tooltip) ? implode('</p><p class="mn pl20">', $tooltip) : (string)$tooltip)
            . '</p></span>';
    }
    
    /**
     * @param string|array $tooltip - array: array of strings. If processed by getFormattedTooltip() - it will be
     *      converted to string where each element of array starts with new line (<br>)
     * @return static
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }
    
    public function hasTooltip(): bool
    {
        return !empty($this->tooltip);
    }
    
    /**
     * @param bool $isCreation - true: validators for record creation; false: validators for record update
     * @return array
     */
    public function getValidators(bool $isCreation): array
    {
        if (!$this->isLinkedToDbColumn() || $this->hasRelation()) {
            return [];
        }
        $column = $this->getTableColumn();
        $columnName = $column->getName();
        $rule = $column->isValueRequiredToBeNotEmpty() || ($column->isItPrimaryKey() && $isCreation) ? 'required|' : 'nullable|';
        if ($column->isValueMustBeUnique()) {
            $additionalColumns = $column->getUniqueContraintAdditonalColumns();
            $uniquenessValidator = Rule::unique($column->getTableStructure()->getTableName(), $columnName);
            if (!$isCreation) {
                $uniquenessValidator->ignore(
                    '{{' . $column->getTableStructure()->getPkColumnName() . '}}',
                    $column->getTableStructure()->getPkColumnName()
                );
            }
            if (!empty($additionalColumns)) {
                foreach ($additionalColumns as $additionalColumnName) {
                    $uniquenessValidator->where($additionalColumnName, '{{' . $additionalColumnName . '}}');
                }
            }
            if (!$column->isUniqueContraintCaseSensitive()) {
                // validation with lowercasing of this column's value
                $uniquenessValidator = preg_replace('%^unique%', 'unique_ceseinsensitive', (string)$uniquenessValidator);
            }
            $rule .= $uniquenessValidator;
        } elseif (in_array($column->getType(), [$column::TYPE_JSON, $column::TYPE_JSONB], true)) {
            $rule .= 'array';
        } elseif ($column->getType() === $column::TYPE_BOOL) {
            $rule .= 'boolean';
        } elseif ($column->getType() === $column::TYPE_INT) {
            $rule .= 'integer' . ($column->isItPrimaryKey() || $column->isItAForeignKey() ? '|min:1' : '');
        } elseif ($column->getType() === $column::TYPE_FLOAT) {
            $rule .= 'numeric';
        } elseif ($column->getType() === $column::TYPE_EMAIL) {
            $rule .= 'string|email';
        } elseif ($column->getType() === $column::TYPE_FILE) {
            $rule .= 'file';
        } elseif ($column->getType() === $column::TYPE_IMAGE) {
            $rule .= 'image';
        } elseif ($column->getType() === $column::TYPE_ENUM) {
            $rule .= 'string|in:' . implode(',', $column->getAllowedValues());
        } elseif ($column->getType() === $column::TYPE_IPV4_ADDRESS) {
            $rule .= 'ipv4';
        } elseif ($column->getType() === $column::TYPE_UNIX_TIMESTAMP) {
            $rule .= 'integer|min:0';
        } elseif ($column->getType() === $column::TYPE_TIMEZONE_OFFSET) {
            $rule .= 'integer';
        } else {
            $rule .= 'string';
        }
        if ($column->isItAForeignKey()) {
            /** @var Relation $relation */
            $relation = $column->getForeignKeyRelation();
            $rule .= '|exists:' . $relation->getForeignTable()->getName() . ',' . $relation->getForeignColumnName();
        }
        return [$columnName => $rule];
    }
    
    /**
     * This indicates if value of this input should be saved normally (false) or it will be saved manually (true)
     * Manual saving is useful to create/update related records for HAS MANY relations
     */
    public function hasOwnValueSavingMethod(): bool
    {
        return false;
    }
    
    /**
     * Provides special value saver used only when isValueWillBeSavedManually() returns true;
     * This is used in complex situations like saving relations of type HAS MANY
     * Closure must throw PeskyORM\Exception\InvalidDataException if something is wring with incoming data.
     * @return \Closure - funciton ($value, RecordInterface $record, $created) {  }
     */
    public function getValueSaver(): \Closure
    {
        return function () {
            return true;
        };
    }
    
    /**
     * Modify incoming value before validating it. May be useful for situations when you need to clean
     * incoming value from unnecessary data
     * @param mixed $value
     * @param array $data
     * @return mixed
     */
    public function modifySubmitedValueBeforeValidation($value, array $data)
    {
        if ($this->hasSubmittedValueModifier()) {
            return call_user_func($this->getSubmittedValueModifier(), $value, $data);
        } else {
            return $value;
        }
    }
    
    /**
     * Closure may modify incoming value before it is validated.
     * @param \Closure $modifier - function ($value, array $data) { return $value; }
     * @return static
     */
    public function setSubmittedValueModifier(\Closure $modifier)
    {
        $this->submittedValueModifier = $modifier;
        return $this;
    }
    
    protected function getSubmittedValueModifier(): ?\Closure
    {
        return $this->submittedValueModifier;
    }
    
    public function hasSubmittedValueModifier(): bool
    {
        return !empty($this->submittedValueModifier);
    }
    
    /**
     * This input should be disabled only when $otherInput has no provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be disabled
     * @param string $otherInput
     * @param mixed $otherInputValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @return static
     */
    public function setDisabledUntil(string $otherInput, $otherInputValue, bool $ignoreIfInputIsAbsent = false)
    {
        return $this->addDisablerConfig($otherInput, false, $otherInputValue, $ignoreIfInputIsAbsent, false);
    }
    
    /**
     * This input should be disabled only when $otherInput has provided value ($hasValue).
     * Note: can be called several times to add more conditions. Conditions will be processed as "1 OR 2 OR 3..."
     * If any condition matches - input will be disabled
     * @param string $otherInput
     * @param mixed $otherInputValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @return static
     */
    public function setDisabledWhen(string $otherInput, $otherInputValue, bool $ignoreIfInputIsAbsent = false)
    {
        return $this->addDisablerConfig($otherInput, true, $otherInputValue, $ignoreIfInputIsAbsent, false);
    }
    
    /**
     * Input will be always marked as 'readonly'
     * @return static
     */
    public function setDisabledAlways()
    {
        $this->disablersConfigs = [];
        $this->disablersConfigs[] = function () {
            return [
                'force_state' => true,
                'attribute' => 'disabled',
            ];
        };
        return $this;
    }
    
    /**
     * This input should be marked as 'readonly' only when $otherInput has no provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be readonly
     * @param string $otherInput
     * @param mixed $otherInputValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @param null $changeValue
     *      - null: leave as is
     *      - string or bool: change value of this input when it gets "readonly" attribute
     * @return static
     */
    public function setReadonlyUntil(string $otherInput, $otherInputValue, bool $ignoreIfInputIsAbsent = false, $changeValue = null)
    {
        return $this->addDisablerConfig($otherInput, false, $otherInputValue, $ignoreIfInputIsAbsent, true, $changeValue);
    }
    
    /**
     * This input should be marked as 'readonly' only when $otherInput has provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be readonly
     * @param string $otherInput
     * @param mixed $otherInputValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @param null $changeValue
     *      - null: leave as is
     *      - string or bool: change value of this input when it gets "readonly" attribute
     * @return static
     */
    public function setReadonlyWhen(string $otherInput, $otherInputValue, bool $ignoreIfInputIsAbsent = false, $changeValue = null)
    {
        return $this->addDisablerConfig($otherInput, true, $otherInputValue, $ignoreIfInputIsAbsent, true, $changeValue);
    }
    
    /**
     * Input will be always marked as 'readonly'
     * @return static
     */
    public function setReadonlyAlways()
    {
        $this->disablersConfigs = [];
        $this->disablersConfigs[] = function () {
            return [
                'force_state' => true,
                'attribute' => 'readonly',
            ];
        };
        return $this;
    }
    
    /**
     * @return static
     */
    protected function addDisablerConfig(
        string $enablerInputName,
        bool $isEqualsTo,
        $value,
        bool $ignoreIfInputIsAbsent = true,
        bool $setReadOnly = false,
        $readonlyValue = null
    ) {
        $closure = function () use ($enablerInputName, $isEqualsTo, $value, $ignoreIfInputIsAbsent, $setReadOnly, $readonlyValue) {
            if (is_array($value)) {
                array_walk($value, function (&$value) {
                    $value = preg_quote($value, '/');
                });
                $value = '/^(' . implode('|', $value) . ')$/';
            } elseif (!is_bool($value) && !preg_match('%^/.*/[a-z]*$%', $value)) {
                $value = '/^(' . preg_quote($value, '%') . ')$/';
            }
            return [
                'disabler_input_name' => $enablerInputName,
                'on_value' => $value,
                'value_is_equals' => $isEqualsTo,
                'attribute' => $setReadOnly ? 'readonly' : 'disabled',
                'set_readonly_value' => $setReadOnly ? $readonlyValue : null,
                'ignore_if_disabler_input_is_absent' => $ignoreIfInputIsAbsent,
            ];
        };
        if (array_key_exists('conditions', $this->disablersConfigs)) {
            $this->disablersConfigs['conditions'][] = $closure();
        } else {
            $this->disablersConfigs[] = $closure;
        }
        return $this;
    }
    
    public function getDisablersConfigs(): array
    {
        if (!array_key_exists('input_name', $this->disablersConfigs)) {
            $ret = [
                'input_name' => $this->getNameForDisablers(),
                'conditions' => [],
            ];
            foreach ($this->disablersConfigs as $closure) {
                $config = $closure();
                $ret['conditions'][] = $config;
            }
            $this->disablersConfigs = $ret;
        }
        return $this->disablersConfigs;
    }
    
    public function hasDisablersConfigs(): bool
    {
        return !empty($this->disablersConfigs);
    }
    
    /**
     * @param ValueRenderer|InputRenderer $renderer
     * @return static
     */
    public function configureDefaultRenderer(ValueRenderer $renderer)
    {
        parent::configureDefaultRenderer($renderer);
        if (!$renderer->hasTemplate()) {
            switch ($this->getType()) {
                case static::TYPE_BOOL:
                    $renderer->setTemplate('cmf::input.trigger');
                    break;
                case static::TYPE_HIDDEN:
                    $renderer->setTemplate('cmf::input.hidden');
                    break;
                case static::TYPE_TEXT:
                case static::TYPE_MULTILINE:
                    $renderer->setTemplate('cmf::input.textarea');
                    break;
                case static::TYPE_WYSIWYG:
                    $renderer->setTemplate('cmf::input.wysiwyg');
                    break;
                case static::TYPE_SELECT:
                    $renderer
                        ->setTemplate('cmf::input.select')
                        ->setOptions($this->getOptions());
                    break;
                case static::TYPE_MULTISELECT:
                    $renderer
                        ->setTemplate('cmf::input.multiselect')
                        ->setOptions($this->getOptions());
                    if (
                        !$this->hasValueConverter()
                        && in_array(
                            $this->getTableColumn()->getType(),
                            [static::TYPE_JSON, static::TYPE_JSONB],
                            true
                        )
                    ) {
                        $this->setValueConverter(function ($value) {
                            return $value;
                        });
                    }
                    break;
                case static::TYPE_TAGS:
                    $renderer->setTemplate('cmf::input.tags');
                    $options = $this->getOptions();
                    if (!empty($options)) {
                        $renderer->setOptions($options);
                    }
                    if (
                        !$this->hasValueConverter()
                        && in_array(
                            $this->getTableColumn()->getType(),
                            [static::TYPE_JSON, static::TYPE_JSONB],
                            true
                        )
                    ) {
                        $this->setValueConverter(function ($value) {
                            return $value;
                        });
                    }
                    break;
                case static::TYPE_IMAGE:
                    $renderer->setTemplate('cmf::input.image');
                    break;
                case static::TYPE_DATETIME:
                    $renderer->setTemplate('cmf::input.datetime');
                    break;
                case static::TYPE_DATE:
                    $renderer->setTemplate('cmf::input.date');
                    break;
                case static::TYPE_EMAIL:
                    $renderer
                        ->setTemplate('cmf::input.text')
                        ->setAttributes(['type' => 'email']);
                    break;
                case static::TYPE_PASSWORD:
                    $renderer->setTemplate('cmf::input.password');
                    break;
                case static::TYPE_READONLY_TEXT:
                    $renderer->setTemplate('cmf::input.readonly_text');
                    break;
                default:
                    $renderer->setTemplate('cmf::input.text');
            }
        }
        return $this;
    }
    
    
}
