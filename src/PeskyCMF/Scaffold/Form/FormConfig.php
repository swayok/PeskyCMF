<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
use PeskyCMF\Scaffold\ScaffoldFieldConfig;
use PeskyCMF\Scaffold\ScaffoldFieldRenderer;
use PeskyORM\ORM\Column;
use Swayok\Utils\Set;
use Swayok\Utils\StringUtils;

class FormConfig extends ScaffoldActionConfig {

    protected $view = 'cmf::scaffold/form';
    protected $bulkEditingView = 'cmf::scaffold/bulk_edit_form';

    /**
     * Fields list that can be edited in bulk (for many records at once)
     * @var FormInput[]
     */
    protected $bulkEditableFields = [];
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

    public function setBulkEditingView($view) {
        $this->bulkEditingView = $view;
        return $this;
    }

    public function getBulkEditingView() {
        if (empty($this->bulkEditingView)) {
            throw new ScaffoldActionException($this, 'The view file for bulk editing is not set');
        }
        return $this->bulkEditingView;
    }

    protected function createFieldRendererConfig() {
        return InputRenderer::create();
    }

    /**
     * @param InputRenderer|ScaffoldFieldRenderer $rendererConfig
     * @param FormInput|ScaffoldFieldConfig $fieldConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     */
    protected function configureDefaultRenderer(
        ScaffoldFieldRenderer $rendererConfig,
        ScaffoldFieldConfig $fieldConfig
    ) {
        switch ($fieldConfig->getType()) {
            case $fieldConfig::TYPE_BOOL:
                $rendererConfig->setView('cmf::input/trigger');
                break;
            case $fieldConfig::TYPE_HIDDEN:
                $rendererConfig->setView('cmf::input/hidden');
                break;
            case $fieldConfig::TYPE_TEXT:
                $rendererConfig->setView('cmf::input/textarea');
                break;
            case $fieldConfig::TYPE_WYSIWYG:
                $rendererConfig->setView('cmf::input/wysiwyg');
                break;
            case $fieldConfig::TYPE_SELECT:
                $rendererConfig
                    ->setView('cmf::input/select')
                    ->setOptions($fieldConfig->getOptions());
                break;
            case $fieldConfig::TYPE_MULTISELECT:
                $rendererConfig
                    ->setView('cmf::input/multiselect')
                    ->setOptions($fieldConfig->getOptions());
                if (
                    !$fieldConfig->hasValueConverter()
                    && in_array(
                        $fieldConfig->getTableColumn()->getType(),
                        [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
                        true
                    )
                ) {
                    $fieldConfig->setValueConverter(function ($value) {
                        return $value;
                    });
                }
                break;
            case $fieldConfig::TYPE_TAGS:
                $rendererConfig->setView('cmf::input/tags');
                $options = $fieldConfig->getOptions();
                if (!empty($options)) {
                    $rendererConfig->setOptions($options);
                }
                if (
                    !$fieldConfig->hasValueConverter()
                    && in_array(
                        $fieldConfig->getTableColumn()->getType(),
                        [FormInput::TYPE_JSON, FormInput::TYPE_JSONB],
                        true
                    )
                ) {
                    $fieldConfig->setValueConverter(function ($value) {
                        return $value;
                    });
                }
                break;
            case $fieldConfig::TYPE_IMAGE:
                $rendererConfig->setView('cmf::input/image');
                break;
            case $fieldConfig::TYPE_DATETIME:
                $rendererConfig->setView('cmf::input/datetime');
                break;
            case $fieldConfig::TYPE_DATE:
                $rendererConfig->setView('cmf::input/date');
                break;
            case $fieldConfig::TYPE_EMAIL:
                $rendererConfig
                    ->setView('cmf::input/text')
                    ->setAttributes(['type' => 'email']);
                break;
            case $fieldConfig::TYPE_PASSWORD:
                $rendererConfig->setView('cmf::input/password');
                break;
            default:
                $rendererConfig->setView('cmf::input/text');
        }
        if ($fieldConfig->isDbField()) {
            $this->configureRendererByColumnConfig($rendererConfig, $fieldConfig->getTableColumn());
        }
        if ($fieldConfig->hasDefaultRendererConfigurator()) {
            call_user_func($fieldConfig->getDefaultRendererConfigurator(), $rendererConfig, $fieldConfig);
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
     * @param array $fields
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \BadMethodCallException
     */
    public function setBulkEditableFields(array $fields) {
        if (empty($this->fields)) {
            throw new \BadMethodCallException('setFields() method must be called before');
        }
        foreach ($fields as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addBulkEditableField($name, $config);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param null|FormInput $fieldConfig - null: FormInput will be imported from $this->fields or created default one
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldActionException
     */
    public function addBulkEditableField($name, $fieldConfig = null) {
        if ((!$fieldConfig || $fieldConfig->isDbField()) && !$this->getTable()->getTableStructure()->hasColumn($name)) {
            throw new ScaffoldActionException($this, "Unknown table column [$name]");
        } else if ($this->getTable()->getTableStructure()->getColumn($name)->isItAFile()) {
            throw new ScaffoldActionException(
                $this,
                "Attaching files in bulk editing form is not suppoted. Table column: [$name]"
            );
        }
        if (empty($fieldConfig)) {
            $fieldConfig = $this->hasField($name) ? $this->getField($name) : $this->createFieldConfig();
        }
        /** @var FormInput $fieldConfig */
        $fieldConfig->setName($name);
        $fieldConfig->setPosition($this->getNextBulkEditableFieldPosition($fieldConfig));
        $fieldConfig->setScaffoldActionConfig($this);
        $this->bulkEditableFields[$name] = $fieldConfig;
        return $this;
    }

    /**
     * @return FormInput[]
     */
    public function getBulkEditableFields() {
        return $this->bulkEditableFields;
    }

    /**
     * @param FormInput $fieldConfig
     * @return int
     */
    protected function getNextBulkEditableFieldPosition(FormInput $fieldConfig) {
        return count($this->bulkEditableFields);
    }

    /**
     * @return mixed|null
     * @throws ScaffoldActionException
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
            foreach ($this->getFields() as $field) {
                if ($field->hasOptionsLoader()) {
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
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     */
    public function loadOptions($pkValue) {
        $options = array();
        foreach ($this->getFields() as $fieldConfig) {
            if ($fieldConfig->hasOptionsLoader()) {
                $options[$fieldConfig->getName()] = call_user_func(
                    $fieldConfig->getOptionsLoader(),
                    $fieldConfig,
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
    public function createFieldConfig() {
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
     * @throws ScaffoldActionException
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
     * @throws ScaffoldActionException
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
     * @throws ScaffoldActionException
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
     * @throws ScaffoldActionException
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
            throw new ScaffoldActionException($this, '$validators must be an array');
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
        $arrayFields = [];
        foreach ($validators as $key => &$value) {
            if (is_string($value)) {
                $value = StringUtils::insert($value, $data, ['before' => '{{', 'after' => '}}']);
                if (preg_match('%(^|\|)array%i', $value)) {
                    $arrayFields[] = $key;
                }
            } else if (is_array($value)) {
                /** @var array $value */
                foreach ($value as &$validator) {
                    if (is_string($validator)) {
                        $validator = StringUtils::insert($value, $data, ['before' => '{{', 'after' => '}}']);
                    }
                    if ($validator === 'array') {
                        $arrayFields[] = $key;
                    }
                }
                unset($validator);
            }
        }
        unset($value);
        $validator = \Validator::make($data, $validators, $messages);
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            foreach ($errors as $field => $error) {
                if (in_array($field, $arrayFields, true)) {
                    $errors[$field . '[]'] = $error;
                    unset($errors[$field]);
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
     * @throws ScaffoldActionException
     */
    public function setDefaultValuesModifier($arrayOrCallable) {
        if (!is_array($arrayOrCallable) && !($arrayOrCallable instanceof \Closure)) {
            throw new ScaffoldActionException($this, 'setDataToAddToRecord($arrayOrCallable) accepts only array or \Closure');
        }
        $this->defaultValuesModifier = $arrayOrCallable;
        return $this;
    }

    /**
     * @param array $defaults
     * @return array
     * @throws ScaffoldActionException
     */
    public function alterDefaultValues(array $defaults) {
        if (!empty($this->defaultValuesModifier)) {
            if ($this->defaultValuesModifier instanceof \Closure) {
                $defaults = call_user_func($this->defaultValuesModifier, $defaults, $this);
                if (!is_array($defaults)) {
                    throw new ScaffoldActionException(
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
     * @throws ScaffoldActionException
     */
    public function setAdditionalHtmlForForm($stringOfFunction) {
        if (!is_string($stringOfFunction) && !($stringOfFunction instanceof \Closure)) {
            throw new ScaffoldActionException($this, 'setAdditionalHtmlForForm($stringOfFunction) accepts only string or function');
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