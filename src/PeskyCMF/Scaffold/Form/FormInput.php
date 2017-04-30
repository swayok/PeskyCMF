<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyCMF\Scaffold\ValueViewerConfigException;
use PeskyORM\ORM\Column;

class FormInput extends RenderableValueViewer {

    /** @var bool */
    protected $showOnCreate = true;
    /** @var bool */
    protected $showOnEdit = true;

    const TYPE_STRING = Column::TYPE_STRING;
    const TYPE_PASSWORD = Column::TYPE_PASSWORD;
    const TYPE_EMAIL = Column::TYPE_EMAIL;
    const TYPE_TEXT = Column::TYPE_TEXT;
    const TYPE_WYSIWYG = 'wysiwyg';
    const TYPE_BOOL = Column::TYPE_BOOL;
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_TAGS = 'tags';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_DATE = Column::TYPE_DATE;
    const TYPE_DATETIME = Column::TYPE_TIMESTAMP;
    const TYPE_DATE_RANGE = 'daterange';
    const TYPE_DATETIME_RANGE = 'datetimerange';
    const TYPE_TIME = Column::TYPE_TIME;
    const TYPE_TIME_RANGE = 'timerange';

    /**
     * value can be:
     * 1. <array> of pairs:
     *      'option_value' => 'option_label'
     * 3. \Closure
     * @var null|array|\Closure
     */
    protected $options;

    /** @var \Closure|null */
    protected $optionsLoader;

    /** @var null|string */
    protected $emptyOptionLabel;

    /** @var null|string */
    protected $tooltip;
    /** @var null|array */
    protected $disablersConfigs = [];
    /** @var null|\Closure */
    protected $submittedValueModifier;

    /**
     * Default input id
     * @return string
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ValueViewerConfigException
     */
    public function getDefaultId() {
        return static::makeDefaultId(
            $this->getName(),
            request()->route()->parameter('table_name', $this->getScaffoldSectionConfig()->getTable()->getName())
        );
    }

    /**
     * @param bool $forHtmlInput - convert name into a valid HTML input name('Relation.coln_name' to 'Relation[col_name]')
     * @return null|string
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function getName($forHtmlInput = false) {
        if ($forHtmlInput) {
            $nameParts = explode('.', $this->getName());
            if (count($nameParts) > 1) {
                return $nameParts[0] . '[' . implode('][', array_slice($nameParts, 1)) . ']';
            } else {
                return $nameParts[0];
            }
        } else {
            return parent::getName();
        }
    }

    /**
     * Make default html id for input using $formInputName
     * @param string $formInputName
     * @param string $tableName
     * @return string
     */
    static public function makeDefaultId($formInputName, $tableName) {
        return 't-' . $tableName . '-c-' . preg_replace('%[^a-zA-Z0-9-]+%', '_', $formInputName) . '-input';
    }

    public function render(array $dataForTemplate = []) {
        $renderedInput = parent::render($dataForTemplate);
        if ($this->isShownOnCreate() && $this->isShownOnEdit()) {
            return $renderedInput;
        } else {
            // display only when creating or editing
            return '{{? ' . ($this->isShownOnCreate() ? '' : '!') . "it._is_creation }}{$renderedInput}{{?}}";
        }
    }

    /**
     * @return null|array|\Closure
     */
    public function getOptions() {
        return $this->hasOptionsLoader() ? [] : $this->options;
    }

    /**
     * @param null|array|\Closure $options
     * @return $this
     * @throws ValueViewerConfigException
     */
    public function setOptions($options) {
        if (!is_array($options) && !($options instanceof \Closure)) {
            throw new ValueViewerConfigException($this, '$options argument should be an array or \Closure');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * @param \Closure $loader = function ($pkValue, FormInput $formInput, FormConfig $formConfig) { return [] }
     * @return $this
     */
    public function setOptionsLoader(\Closure $loader) {
        $this->optionsLoader = $loader;
        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getOptionsLoader() {
        return $this->optionsLoader;
    }

    /**
     * @return bool
     */
    public function hasOptionsLoader() {
        return !empty($this->optionsLoader);
    }

    /**
     * @param $label
     * @return $this
     */
    public function setEmptyOptionLabel($label) {
        $this->emptyOptionLabel = $label;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmptyOptionLabel() {
        return $this->emptyOptionLabel === null ? '' : $this->emptyOptionLabel;
    }

    /**
     * @return bool
     */
    public function hasEmptyOptionLabel() {
        return $this->emptyOptionLabel !== null;
    }

    /**
     * @return boolean
     */
    public function isShownOnCreate() {
        return $this->showOnCreate;
    }

    /**
     * @return $this
     */
    public function showOnCreate() {
        $this->showOnCreate = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnCreate() {
        $this->showOnCreate = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShownOnEdit() {
        return $this->showOnEdit;
    }

    /**
     * @return $this
     */
    public function showOnEdit() {
        $this->showOnEdit = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnEdit() {
        $this->showOnEdit = false;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        parent::setType($type);
        return $this;
    }

    /**
     * @param string $default
     * @param null|InputRenderer $renderer
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getLabel(InputRenderer $renderer = null) {
        $isRequired = '';
        if ($renderer === null) {
            $isRequired = $this->getTableColumn()->isValueRequiredToBeNotEmpty() ? '*' : '';
        } else if ($renderer->isRequired()) {
            $isRequired = '*';
        } else if ($renderer->isRequiredForCreate()) {
            $isRequired = '{{? !!it.isCreation }}*{{?}}';
        } else if ($renderer->isRequiredForEdit()) {
            $isRequired = '{{? !it.isCreation }}*{{?}}';
        }
        return parent::getLabel() . $isRequired;
    }

    /**
     * @return null|string|array
     */
    public function getTooltip() {
        return $this->tooltip;
    }

    /**
     * @return string
     */
    public function getFormattedTooltip() {
        if ($this->hasTooltip()) {
            $tooltip = $this->getTooltip();
            return '<span class="help-block"><p class="mn">'
                    . '<i class="glyphicon glyphicon-info-sign text-blue fs16 va-t mr5 lh20" style="top: 0;"></i>'
                    . (is_array($tooltip) ? implode('</p><p class="mn pl20">', $tooltip) : (string)$tooltip)
                . '</p></span>';
        } else {
            return '';
        }
    }

    /**
     * @param string|array $tooltip - array: array of strings. If processed by getFormattedTooltip() - it will be
     *      converted to string where each element of array starts with new line (<br>)
     * @return $this
     */
    public function setTooltip($tooltip) {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTooltip() {
        return !empty($this->tooltip);
    }

    /**
     * @return array
     */
    public function getValidators() {
        return [];
    }

    /**
     * Modify incoming value before validating it. May be useful for situations when you need to clean
     * incoming value from unnecessary data
     * @param mixed $value
     * @param array $data
     * @return mixed
     */
    public function modifySubmitedValueBeforeValidation($value, array $data) {
        if (!empty($this->submittedValueModifier)) {
            return call_user_func($this->submittedValueModifier, $value, $data);
        } else {
            return $value;
        }
    }

    /**
     * Closure may modify incoming value before it is validated.
     * @param \Closure $modifier - function ($value, array $data) { return $value; }
     * @return $this
     */
    public function setSubmittedValueModifier(\Closure $modifier) {
        $this->submittedValueModifier = $modifier;
        return $this;
    }

    /**
     * This input should be disabled only when $otherInput has no provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be disabled
     * @param string $otherInput
     * @param mixed $hasValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setDisabledUntil($otherInput, $hasValue, $ignoreIfInputIsAbsent = false) {
        return $this->addDisablerConfig($otherInput, false, $hasValue, $ignoreIfInputIsAbsent, false);
    }

    /**
     * This input should be disabled only when $otherInput has provided value ($hasValue).
     * Note: can be called several times to add more conditions. Conditions will be processed as "1 OR 2 OR 3..."
     * If any condition matches - input will be disabled
     * @param string $otherInput
     * @param mixed $hasValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setDisabledWhen($otherInput, $hasValue, $ignoreIfInputIsAbsent = false) {
        return $this->addDisablerConfig($otherInput, true, $hasValue, $ignoreIfInputIsAbsent, false);
    }

    /**
     * This input should be marked as 'readonly' only when $otherInput has no provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be readonly
     * @param string $otherInput
     * @param mixed $hasValue
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
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setReadonlyUntil($otherInput, $hasValue, $ignoreIfInputIsAbsent = false, $changeValue = null) {
        return $this->addDisablerConfig($otherInput, false, $hasValue, $ignoreIfInputIsAbsent, true, $changeValue);
    }

    /**
     * This input should be marked as 'readonly' only when $otherInput has provided value ($hasValue)
     * Note: can be called several times to add more conditions. Conditions will be preocessed "1 OR 2 OR 3..."
     * If any condition matches - input will be readonly
     * @param string $otherInput
     * @param mixed $hasValue
     *      - bool: if $otherInput is checkbox/trigger |
     *      - string or array: if $otherInput is select or radios
     *      - regexp: custom regexp. MUST BE COMPATIBLE to javascript's regexp implementation
     *          start & end delitmiter is '/'; must do exact matching using ^ and $
     *          Example: "/^(val1|val2)$/", "/^([a-d][c-z])+/"
     * @param bool $ignoreIfInputIsAbsent
     *      - true: if input is not present in form this condition will be ignored
     *      - false: if input is not present in form this condition will write about this situation in browser's console and will not be used
     * @param null $changeValue
 *          - null: leave as is
     *      - string or bool: change value of this input when it gets "readonly" attribute
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setReadonlyWhen($otherInput, $hasValue, $ignoreIfInputIsAbsent = false, $changeValue = null) {
        return $this->addDisablerConfig($otherInput, true, $hasValue, $ignoreIfInputIsAbsent, true, $changeValue);
    }

    protected function addDisablerConfig(
        $enablerInputName,
        $isEqualsTo,
        $value,
        $ignoreIfInputIsAbsent = true,
        $setReadOnly = false,
        $readonlyValue = null
    ) {
        $closure = function () use ($enablerInputName, $isEqualsTo, $value, $ignoreIfInputIsAbsent, $setReadOnly, $readonlyValue) {
            if (is_array($value)) {
                array_walk($value, function (&$value) {
                    $value = preg_quote($value, '/');
                });
                $value = '/^(' . implode('|', $value) . ')$/';
            } else if (!is_bool($value) && !preg_match('%^/.*/[a-z]*$%', $value)) {
                $value = '/^(' . preg_quote($value, '%') . ')$/';
            }
            return [
                'disabler_input_name' => $enablerInputName,
                'on_value' => $value,
                'value_is_equals' => (bool)$isEqualsTo,
                'attribute' => (bool)$setReadOnly ? 'readonly' : 'disabled',
                'set_readonly_value' => (bool)$setReadOnly ? $readonlyValue : null,
                'ignore_if_disabler_input_is_absent' => $ignoreIfInputIsAbsent
            ];
        };
        if (array_key_exists('conditions', $this->disablersConfigs)) {
            $this->disablersConfigs['conditions'][] = $closure();
        } else {
            $this->disablersConfigs[] = $closure;
        }
        return $this;
    }

    /**
     * @return array
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function getDisablersConfigs() {
        if (!array_key_exists('input_name', $this->disablersConfigs)) {
            $ret = [
                'input_name' => $this->getName(true),
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

    /**
     * @return bool
     */
    public function hasDisablersConfigs() {
        return !empty($this->disablersConfigs);
    }

    /**
     * @param ValueRenderer|InputRenderer $renderer
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \UnexpectedValueException
     */
    public function configureDefaultRenderer(ValueRenderer $renderer) {
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
                            [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
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
                            [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
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
                default:
                    $renderer->setTemplate('cmf::input.text');
            }
        }
        return $this;
    }


}