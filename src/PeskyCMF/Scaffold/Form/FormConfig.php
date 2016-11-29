<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\ScaffoldSectionException;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\Column;
use Swayok\Utils\Set;
use Swayok\Utils\StringUtils;

class FormConfig extends ScaffoldSectionConfig {

    protected $template = 'cmf::scaffold/form';
    protected $bulkEditingTemplate = 'cmf::scaffold/bulk_edit_form';

    /**
     * Fields list that can be edited in bulk (for many records at once)
     * @var FormInput[]
     */
    protected $bulkEditableColumns = [];
    /**
     * @var null|mixed
     */
    protected $itemId = null;
    /**
     * @var bool
     */
    protected $hasFiles = false;

    /** @var bool */
    protected $hasOptionsLoader = null;
    /** @var \Closure */
    protected $validators;
    /** @var \Closure */
    protected $validatorsForCreate;
    /** @var \Closure */
    protected $validatorsForEdit;
    /** @var array|\Closure|null */
    protected $defaultValuesModifier = [];

    /** @var string|\Closure */
    protected $additionalHtmlForForm = '';

    const VALIDATOR_FOR_ID = 'required|integer|min:1';
    /** @var \Closure */
    protected $beforeSaveCallback;
    /** @var \Closure */
    protected $beforeBulkEditDataSaveCallback;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForCreation = false;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForUpdate = false;
    /** @var \Closure */
    protected $beforeValidateCallback;
    /** @var \Closure|null */
    protected $validationSuccessCallback;
    /** @var \Closure|null */
    protected $afterSaveCallback;
    /** @var \Closure|null */
    protected $afterBulkEditDataSaveCallback;

    public function setBulkEditingTemplate($view) {
        $this->bulkEditingTemplate = $view;
        return $this;
    }

    public function getBulkEditingTemplate() {
        if (empty($this->bulkEditingTemplate)) {
            throw new ScaffoldSectionException($this, 'The view file for bulk editing is not set');
        }
        return $this->bulkEditingTemplate;
    }

    protected function createValueRenderer() {
        return InputRenderer::create();
    }

    /**
     * Alias for setValueViewers
     * @param array $formInputs
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function setFormInputs(array $formInputs) {
        return $this->setValueViewers($formInputs);
    }

    /**
     * @return FormInput[]|AbstractValueViewer[]
     */
    public function getFormInputs() {
        return $this->getValueViewers();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFormInput($name) {
        return $this->hasValueViewer($name);
    }

    /**
     * @param InputRenderer|ValueRenderer $renderer
     * @param FormInput|AbstractValueViewer $formInput
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ValueViewerException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        AbstractValueViewer $formInput
    ) {
        switch ($formInput->getType()) {
            case $formInput::TYPE_BOOL:
                $renderer->setTemplate('cmf::input/trigger');
                break;
            case $formInput::TYPE_HIDDEN:
                $renderer->setTemplate('cmf::input/hidden');
                break;
            case $formInput::TYPE_TEXT:
                $renderer->setTemplate('cmf::input/textarea');
                break;
            case $formInput::TYPE_WYSIWYG:
                $renderer->setTemplate('cmf::input/wysiwyg');
                break;
            case $formInput::TYPE_SELECT:
                $renderer
                    ->setTemplate('cmf::input/select')
                    ->setOptions($formInput->getOptions());
                break;
            case $formInput::TYPE_MULTISELECT:
                $renderer
                    ->setTemplate('cmf::input/multiselect')
                    ->setOptions($formInput->getOptions());
                if (
                    !$formInput->hasValueConverter()
                    && in_array(
                        $formInput->getTableColumn()->getType(),
                        [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
                        true
                    )
                ) {
                    $formInput->setValueConverter(function ($value) {
                        return $value;
                    });
                }
                break;
            case $formInput::TYPE_TAGS:
                $renderer->setTemplate('cmf::input/tags');
                $options = $formInput->getOptions();
                if (!empty($options)) {
                    $renderer->setOptions($options);
                }
                if (
                    !$formInput->hasValueConverter()
                    && in_array(
                        $formInput->getTableColumn()->getType(),
                        [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
                        true
                    )
                ) {
                    $formInput->setValueConverter(function ($value) {
                        return $value;
                    });
                }
                break;
            case $formInput::TYPE_IMAGE:
                $renderer->setTemplate('cmf::input/image');
                break;
            case $formInput::TYPE_DATETIME:
                $renderer->setTemplate('cmf::input/datetime');
                break;
            case $formInput::TYPE_DATE:
                $renderer->setTemplate('cmf::input/date');
                break;
            case $formInput::TYPE_EMAIL:
                $renderer
                    ->setTemplate('cmf::input/text')
                    ->setAttributes(['type' => 'email']);
                break;
            case $formInput::TYPE_PASSWORD:
                $renderer->setTemplate('cmf::input/password');
                break;
            default:
                $renderer->setTemplate('cmf::input/text');
        }
        if ($formInput->isDbColumn()) {
            $this->configureRendererByColumnConfig($renderer, $formInput->getTableColumn());
        }
        if ($formInput->hasDefaultRendererConfigurator()) {
            call_user_func($formInput->getDefaultRendererConfigurator(), $renderer, $formInput);
        }
    }

    /**
     * @param InputRenderer $rendererConfig
     * @param Column $columnConfig
     */
    protected function configureRendererByColumnConfig(
        InputRenderer $rendererConfig,
        Column $columnConfig
    ) {
        $rendererConfig->setIsRequired(!$columnConfig->isValueCanBeNull() && !$columnConfig->hasDefaultValue());
    }

    /**
     * @param array $columns
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \BadMethodCallException
     */
    public function setBulkEditableColumns(array $columns) {
        if (empty($this->valueViewers)) {
            throw new \BadMethodCallException('setFields() method must be called before');
        }
        foreach ($columns as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addBulkEditableColumns($name, $config);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param null|FormInput $formInput - null: FormInput will be imported from $this->fields or created default one
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionException
     */
    public function addBulkEditableColumns($name, FormInput $formInput = null) {
        if ((!$formInput || $formInput->isDbColumn()) && !$this->getTable()->getTableStructure()->hasColumn($name)) {
            throw new ScaffoldSectionException($this, "Unknown table column [$name]");
        } else if ($this->getTable()->getTableStructure()->getColumn($name)->isItAFile()) {
            throw new ScaffoldSectionException(
                $this,
                "Attaching files in bulk editing form is not suppoted. Table column: [$name]"
            );
        }
        if (empty($formInput)) {
            $formInput = $this->hasValueViewer($name) ? $this->getValueViewer($name) : $this->createValueViewer();
        }
        /** @var FormInput $formInput */
        $formInput->setName($name);
        $formInput->setPosition($this->getNextBulkEditableColumnPosition($formInput));
        $formInput->setScaffoldSectionConfig($this);
        $this->bulkEditableColumns[$name] = $formInput;
        return $this;
    }

    /**
     * @return FormInput[]
     */
    public function getBulkEditableColumns() {
        return $this->bulkEditableColumns;
    }

    /**
     * @param FormInput $formInput
     * @return int
     */
    protected function getNextBulkEditableColumnPosition(FormInput $formInput) {
        return count($this->bulkEditableColumns);
    }

    /**
     * @return mixed|null
     * @throws ScaffoldSectionException
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * @param mixed $itemId
     * @return $this
     */
    public function setItemId($itemId) {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFiles() {
        return $this->hasFiles;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setHasFiles($value) {
        $this->hasFiles = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasOptionsLoader() {
        if ($this->hasOptionsLoader === null) {
            $this->hasOptionsLoader = false;
            foreach ($this->getValueViewers() as $viewer) {
                if ($viewer->hasOptionsLoader()) {
                    $this->hasOptionsLoader = true;
                    break;
                }
            }
        }
        return $this->hasOptionsLoader;
    }

    /**
     * @param int|string|null $pkValue - primary key value
     * @return array[]
     * @throws \PeskyCMF\Scaffold\ValueViewerException
     */
    public function loadOptions($pkValue) {
        $options = array();
        foreach ($this->getValueViewers() as $viewer) {
            if ($viewer->hasOptionsLoader()) {
                $options[$viewer->getName()] = call_user_func(
                    $viewer->getOptionsLoader(),
                    $viewer,
                    $this,
                    $pkValue
                );
            }
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function createValueViewer() {
        return FormInput::create();
    }

    /**
     * @return array
     */
    public function getValidatorsForEdit() {
        return array_merge(
            $this->validators ? call_user_func($this->validators) : [],
            $this->validatorsForCreate ? call_user_func($this->validatorsForEdit) : []
        );
    }

    /**
     * @return bool
     */
    public function hasValidatorsForEdit() {
        return !empty($this->validatorsForEdit);
    }

    /**
     * @param \Closure $validatorsForEdit - you can insert fields from received data via '{{field_name}}'
     * @return $this
     */
    public function addValidatorsForEdit(\Closure $validatorsForEdit) {
        $this->validatorsForEdit = $validatorsForEdit;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidatorsForCreate() {
        return array_merge(
            $this->validators ? call_user_func($this->validators) : [],
            $this->validatorsForCreate ? call_user_func($this->validatorsForCreate) : []
        );
    }

    /**
     * @return bool
     */
    public function hasValidatorsForCreate() {
        return !empty($this->validatorsForCreate);
    }

    /**
     * @param \Closure $validatorsForCreate
     * @return $this
     */
    public function addValidatorsForCreate(\Closure $validatorsForCreate) {
        $this->validatorsForCreate = $validatorsForCreate;
        return $this;
    }

    /**
     * @param \Closure $validators
     * @return $this
     */
    public function setValidators(\Closure $validators) {
        $this->validators = $validators;
        return $this;
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalidation
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     * @throws ScaffoldSectionException
     */
    public function validateDataForCreate(array $data, array $messages = [], $isRevalidation = false) {
        return $this->validateData($data, $this->getValidatorsForCreate(), $messages, $isRevalidation);
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalidation
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     * @throws ScaffoldSectionException
     */
    public function validateDataForEdit(array $data, array $messages = [], $isRevalidation = false) {
        return $this->validateData($data, $this->getValidatorsForEdit(), $messages, $isRevalidation);
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalidation
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     * @throws ScaffoldSectionException
     */
    public function validateDataForBulkEdit(array $data, array $messages = [], $isRevalidation = false) {
        $rules = array_intersect_key($this->getValidatorsForEdit(), $data);
        if (empty($rules)) {
            return [];
        }
        return $this->validateData($data, $rules, $messages, $isRevalidation, true);
    }

    /**
     * @param \Closure $callback - function (array $data, $isRevalidation) { return true; }
     * Note: callback MUST return true if everything is ok, otherwise - returned values treated as error
     * @return $this
     */
    public function setBeforeValidateCallback(\Closure $callback) {
        $this->beforeValidateCallback = $callback;
        return $this;
    }

    /**
     * @param array $data
     * @param $isRevalidation
     * @return array|bool|string - true: no errors | other: errors detected
     */
    public function beforeValidate(array $data, $isRevalidation) {
        if (!empty($this->beforeValidateCallback)) {
            $success = call_user_func($this->beforeValidateCallback, $data, $isRevalidation);
            if ($success !== true) {
                return (array)$success;
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @param array $validators - supports inserts in format "{{id}}" where "id" can be any key from $data
     * @param array $messages
     * @param bool $isRevalidation
     * @param bool $isBulkEdit
     * @return array|string|bool
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     *
     * @throws ScaffoldSectionException
     */
    public function validateData(
        array $data,
        array $validators,
        array $messages = [],
        $isRevalidation = false,
        $isBulkEdit = false
    ) {
        $success = $this->beforeValidate($data, $isRevalidation);
        if ($success !== true) {
            return $success;
        }

        if (!is_array($validators)) {
            throw new ScaffoldSectionException($this, '$validators must be an array');
        }
        if (empty($validators)) {
            return [];
        }
        if (empty($messages)) {
            $messages = cmfTransCustom('.' . $this->getTable()->getName() . '.form.validation');
        }
        if (!is_array($messages)) {
            $messages = [];
        } else {
            $messages = Set::flatten($messages);
        }
        $columnsWithArrayType = [];
        foreach ($validators as $key => &$value) {
            if (is_string($value)) {
                $value = StringUtils::insert($value, $data, ['before' => '{{', 'after' => '}}']);
                if (preg_match('%(^|\|)array%i', $value)) {
                    $columnsWithArrayType[] = $key;
                }
            } else if (is_array($value)) {
                /** @var array $value */
                foreach ($value as &$validator) {
                    if (is_string($validator)) {
                        $validator = StringUtils::insert($value, $data, ['before' => '{{', 'after' => '}}']);
                    }
                    if ($validator === 'array') {
                        $columnsWithArrayType[] = $key;
                    }
                }
                unset($validator);
            }
        }
        unset($value);
        $validator = \Validator::make($data, $validators, $messages);
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            foreach ($errors as $viewerName => $error) {
                if (in_array($viewerName, $columnsWithArrayType, true)) {
                    $errors[$viewerName . '[]'] = $error;
                    unset($errors[$viewerName]);
                }
            }
            return $errors;
        }

        $success = $this->onValidationSuccess($data, $isRevalidation, $isBulkEdit);
        if ($success !== true) {
            return $success;
        }

        return [];
    }

    /**
     * Called after request data validation and before specific callbacks and data saving.
     * Note: if you need to revalidate data after callback - use setRevalidateDataAfterBeforeSaveCallback() method
     * Note: is not applied to bulk edit!
     * @param \Closure $callback = function ($isCreation, array $validatedData, FormConfig $formConfig) { return $validatedData; }
     * @return $this
     */
    public function setBeforeSaveCallback(\Closure $callback) {
        $this->beforeSaveCallback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBeforeSaveCallback() {
        return !empty($this->beforeSaveCallback);
    }

    /**
     * @return \Closure
     */
    public function getBeforeSaveCallback() {
        return $this->beforeSaveCallback;
    }

    /**
     * Called after request data validation and before specific callbacks and data saving.
     * Note: if you need to revalidate data after callback - use setRevalidateDataAfterBeforeSaveCallback() method
     * Note: is not applied to bulk edit!
     * @param \Closure $callback = function (array $validatedData, FormConfig $formConfig) { return $validatedData; }
     * @return $this
     */
    public function setBeforeBulkEditDataSaveCallback(\Closure $callback) {
        $this->beforeBulkEditDataSaveCallback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBeforeBulkEditDataSaveCallback() {
        return !empty($this->beforeBulkEditDataSaveCallback);
    }

    /**
     * @return \Closure
     */
    public function getBeforeBulkEditDataSaveCallback() {
        return $this->beforeBulkEditDataSaveCallback;
    }

    /**
     * @param bool $forCreation
     * @param bool $forUpdate
     * @return $this
     */
    public function setRevalidateDataAfterBeforeSaveCallback($forCreation, $forUpdate) {
        $this->revalidateDataAfterBeforeSaveCallbackForCreation = (bool)$forCreation;
        $this->revalidateDataAfterBeforeSaveCallbackForUpdate = (bool)$forUpdate;
        return $this;
    }

    /**
     * @param bool $isCreation
     * @return bool
     */
    public function shouldRevalidateDataAfterBeforeSaveCallback($isCreation) {
        return $isCreation
            ? $this->revalidateDataAfterBeforeSaveCallbackForCreation
            : $this->revalidateDataAfterBeforeSaveCallbackForUpdate;
    }

    /**
     * @param \Closure $calback = function (array $data, $isRevalidation, $isBulkEdit) { return true }
     * Note: callback MUST return true if everything is ok, otherwise - returned values treated as error
     * Values allowed to be returned:
     *      - true: no errors
     *      - string: custom "validation failed" message (without errors for certain fields)
     *      - array: validation errors for certain fields, may contain "_mesasge" key to be displayed instead of
     *               default "validation failed" message
     * @return $this
     */
    public function setValidationSuccessCallback(\Closure $calback) {
        $this->validationSuccessCallback = $calback;
        return $this;
    }

    /**
     * @param array $data
     * @param bool $isRevalidation
     * @param bool $isBulkEdit
     * @return array|bool - true: no errors | other - validation errors
     * @throws \LogicException
     */
    protected function onValidationSuccess(array $data, $isRevalidation, $isBulkEdit) {
        if (!empty($this->validationSuccessCallback)) {
            $success = call_user_func($this->validationSuccessCallback, $data, $isRevalidation, $isBulkEdit);
            if ($success !== true) {
                if (is_string($success)) {
                    return ['_message' => $success];
                } else if (is_array($success)) {
                    return $success;
                } else {
                    throw new \LogicException(
                        'validationSuccessCallback must return true, string or array with key-value pairs'
                    );
                }
            }
        }
        return true;
    }

    /**
     * Callback is called after successfully saving data but before model's commit()
     * It must return true if everything is ok or instance of \Symfony\Component\HttpFoundation\JsonResponse
     * Response success detected by HTTP code of \Illuminate\Http\JsonResponse: code < 400 - success; code >= 400 - error
     * @param \Closure $callback - function ($isCreation, array $validatedData, CmfDbRecord $object, FormConfig $formConfig) { return true; }
     * @return $this
     */
    public function setAfterSaveCallback(\Closure $callback) {
        $this->afterSaveCallback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAfterSaveCallback() {
        return !empty($this->afterSaveCallback);
    }

    /**
     * @return \Closure
     */
    public function getAfterSaveCallback() {
        return $this->afterSaveCallback;
    }

    /**
     * Callback is called after successfully saving data but before model's commit()
     * It must return true if everything is ok or instance of \Symfony\Component\HttpFoundation\JsonResponse
     * Response success detected by HTTP code of \Illuminate\Http\JsonResponse: code < 400 - success; code >= 400 - error
     * @param \Closure $callback - function (array $validatedData, FormConfig $formConfig) { return []; }
     * @return $this
     */
    public function setAfterBulkEditDataSaveCallback(\Closure $callback) {
        $this->afterBulkEditDataSaveCallback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAfterBulkEditDataAfterSaveCallback() {
        return !empty($this->afterBulkEditDataSaveCallback);
    }

    /**
     * @return \Closure
     */
    public function getAfterBulkEditDataAfterSaveCallback() {
        return $this->afterBulkEditDataSaveCallback;
    }

    /**
     * @param array|\Closure $arrayOrCallable
     *      - \Closure: funciton (array $defaults, FormConfig $formConfig) { return $defaults; }
     * @return $this
     * @throws ScaffoldSectionException
     */
    public function setDefaultValuesModifier($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !($arrayOrCallable instanceof \Closure)) {
            throw new ScaffoldSectionException($this, 'setDataToAddToRecord($arrayOrCallable) accepts only array or \Closure');
        }
        $this->defaultValuesModifier = $arrayOrCallable;
        return $this;
    }

    /**
     * @param array $defaults
     * @return array
     * @throws ScaffoldSectionException
     */
    public function alterDefaultValues(array $defaults) {
        if (!empty($this->defaultValuesModifier)) {
            if ($this->defaultValuesModifier instanceof \Closure) {
                $defaults = call_user_func($this->defaultValuesModifier, $defaults, $this);
                if (!is_array($defaults)) {
                    throw new ScaffoldSectionException(
                        $this,
                        'Altering default values is invalid. Callback must return an array'
                    );
                }
            } else {
                return array_merge($defaults, $this->defaultValuesModifier);
            }
        }
        return $defaults;
    }

    /**
     * @param $stringOfFunction - function (FormConfig $formConfig) { return '<div>'; }
     * @return $this
     * @throws ScaffoldSectionException
     */
    public function setAdditionalHtmlForForm($stringOfFunction) {
        if (!is_string($stringOfFunction) && !($stringOfFunction instanceof \Closure)) {
            throw new ScaffoldSectionException($this, 'setAdditionalHtmlForForm($stringOfFunction) accepts only string or function');
        }
        $this->additionalHtmlForForm = $stringOfFunction;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalHtmlForForm() {
        if (empty($this->additionalHtmlForForm)) {
            return '';
        } else if (is_string($this->additionalHtmlForForm)) {
            return $this->additionalHtmlForForm;
        } else {
            return call_user_func($this->additionalHtmlForForm, $this);
        }
    }

}