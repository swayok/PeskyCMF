<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
use PeskyORM\DbColumnConfig;
use Swayok\Utils\Set;
use Swayok\Utils\StringUtils;

class FormConfig extends ScaffoldActionConfig {

    /**
     * @var null|mixed
     */
    protected $itemId = null;
    /**
     * @var bool
     */
    protected $hasFiles = false;
    /**
     * Form width (percents)
     * @var int
     */
    protected $width = 100;
    /** @var bool */
    protected $hasOptionsLoader = null;
    /** @var array  */
    protected $validatorsForCreate = [];
    /** @var array  */
    protected $validatorsForEdit = [];

    const VALIDATOR_FOR_ID = 'required|integer|min:1';
    /** @var callable */
    protected $beforeSaveCallback;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForCreation = false;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForUpdate = false;
    /** @var callable */
    protected $beforeValidateCallback;

    /**
     * @return callable|\Closure
     */
    public function getDefaultFieldRenderer() {
        if (!empty($this->defaultFieldRenderer)) {
            return $this->defaultFieldRenderer;
        } else {
            return function ($field, $actionConfig, array $dataForView) {
                return $this->_getDefaultFieldRendererConfig($field, $actionConfig, $dataForView);
            };
        }
    }

    /**
     * @param FormFieldConfig $fieldConfig
     * @param FormConfig $actionConfig
     * @param array $dataForView
     * @return mixed
     */
    protected function _getDefaultFieldRendererConfig(
        FormFieldConfig $fieldConfig,
        FormConfig $actionConfig,
        array $dataForView
    ) {
        $rendererConfig = InputRendererConfig::create()->setData($dataForView);
        $this->configureDefaultRenderer($rendererConfig, $fieldConfig);
        return $rendererConfig;
    }

    /**
     * @param InputRendererConfig $rendererConfig
     * @param FormFieldConfig $fieldConfig
     */
    protected function configureDefaultRenderer(InputRendererConfig $rendererConfig, FormFieldConfig $fieldConfig) {
        switch ($fieldConfig->getType()) {
            case $fieldConfig::TYPE_BOOL:
                $rendererConfig->setView('cmf::input/checkbox');
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
            case $fieldConfig::TYPE_PASSWORD:
                $rendererConfig
                    ->setView('cmf::input/text')
                    ->setAttributes(['type' => $fieldConfig->getType()]);
                break;
            default:
                $rendererConfig->setView('cmf::input/text');
        }
        $this->configureRendererByColumnConfig($rendererConfig, $fieldConfig->getTableColumnConfig());
        $fieldConfig->configureDefaultRenderer($rendererConfig);
    }

    /**
     * @param InputRendererConfig $rendererConfig
     * @param DbColumnConfig $columnConfig
     * @throws \PeskyORM\Exception\DbColumnConfigException
     */
    protected function configureRendererByColumnConfig(
        InputRendererConfig $rendererConfig,
        DbColumnConfig $columnConfig
    ) {
        $rendererConfig
            ->setIsRequiredForCreate($columnConfig->isRequiredOn(DbColumnConfig::ON_CREATE))
            ->setIsRequiredForEdit($columnConfig->isRequiredOn(DbColumnConfig::ON_CREATE));
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
     * @return string
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @param string $width
     * @return $this
     */
    public function setWidth($width) {
        $this->width = $width;
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
        $this->hasFiles = !!$value;
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
     * @return array[]
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     */
    public function loadOptions() {
        $options = array();
        foreach ($this->getFields() as $field) {
            if ($field->hasOptionsLoader()) {
                $options[$field->getName()] = $field->loadOptions();
            }
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    static public function createFieldConfig($fieldName) {
        return FormFieldConfig::create();
    }

    /**
     * @return array
     */
    public function getValidatorsForEdit() {
        return $this->validatorsForEdit;
    }

    /**
     * @return bool
     */
    public function hasValidatorsForEdit() {
        return !empty($this->validatorsForEdit);
    }

    /**
     * @param array $validatorsForEdit
     * @return $this
     */
    public function addValidatorsForEdit(array $validatorsForEdit) {
        $this->validatorsForEdit = array_replace($this->validatorsForEdit, $validatorsForEdit);
        return $this;
    }

    /**
     * @return array
     */
    public function getValidatorsForCreate() {
        return $this->validatorsForCreate;
    }

    /**
     * @return bool
     */
    public function hasValidatorsForCreate() {
        return !empty($this->validatorsForCreate);
    }

    /**
     * @param array $validatorsForCreate
     * @return $this
     */
    public function addValidatorsForCreate(array $validatorsForCreate) {
        $this->validatorsForCreate = array_replace($this->validatorsForCreate, $validatorsForCreate);
        return $this;
    }

    /**
     * @param array $validators
     * @return $this
     */
    public function setValidators(array $validators) {
        $this->validatorsForEdit = $this->validatorsForCreate = $validators;
        return $this;
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalitaion
     * @return array
     * @throws ScaffoldActionException
     */
    public function validateDataForCreate(array $data, array $messages = [], $isRevalitaion = false) {
        return $this->validateData($data, $this->getValidatorsForCreate(), $messages, $isRevalitaion);
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalitaion
     * @return array
     * @throws ScaffoldActionException
     */
    public function validateDataForEdit(array $data, array $messages = [], $isRevalitaion = false) {
        return $this->validateData($data, $this->getValidatorsForEdit(), $messages, $isRevalitaion);
    }

    /**
     * @param callable $callback - function (array $data, $isRevalitaion) { return true; }
     * Nore: callback MUST return true if everything is ok, otherwise - returned values treated as error
     * @return $this
     */
    public function setBeforeValidateCallback(callable $callback) {
        $this->beforeValidateCallback = $callback;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasBeforeValidateCallback() {
        return !empty($this->beforeValidateCallback);
    }

    /**
     * @param array $data
     * @param array $validators
     * @param array $messages
     * @param bool $isRevalitaion
     * @return array
     * @throws ScaffoldActionException
     */
    public function validateData(array $data, array $validators, array $messages = [], $isRevalitaion = false) {
        if ($this->hasBeforeValidateCallback()) {
            $success = call_user_func($this->beforeValidateCallback, $data, $isRevalitaion);
            if ($success !== true) {
                if (!is_array($success)) {
                    $success = [$success];
                }
                return $success;
            }
        }
        if (!is_array($validators)) {
            throw new ScaffoldActionException($this, '$validators must be an array');
        }
        if (empty($validators)) {
            return [];
        }
        if (empty($messages)) {
            $messages = CmfConfig::transCustom('.' . $this->getModel()->getTableName() . '.form.validation');
        }
        if (!is_array($messages)) {
            $messages = [];
        } else {
            $messages = Set::flatten($messages);
        }
        array_walk($validators, function (&$value, $key) use ($data) {
            if (is_string($value)) {
                $value = StringUtils::insert($value, $data, ['before' => '{{', 'after' => '}}']);
            }
        });
        $validator = \Validator::make($data, $validators, $messages);
        if ($validator->fails()) {
            return $validator->getMessageBag()->toArray();
        }
        return [];
    }

    /**
     * Called after request data validation and before specific callbacks and data saving.
     * Note: if you need to revalidate data after callback - use
     * @param callable $callback = function ($isCreation, array $validatedData, FormConfig $formConfig) { return $validatedData; }
     * @return $this
     */
    public function setBeforeSaveCallback(callable $callback) {
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
     * @param $isCreation
     * @param $validatedData
     * @return array
     */
    public function beforeSave($isCreation, array $validatedData) {
        if ($this->hasBeforeSaveCallback()) {
            return call_user_func($this->beforeSaveCallback, $isCreation, $validatedData, $this);
        }
        return $validatedData;
    }

    /**
     * @param bool $forCreation
     * @param bool $forUpdate
     * @return $this
     */
    public function setRevalidateDataAfterBeforeSaveCallback($forCreation, $forUpdate) {
        $this->revalidateDataAfterBeforeSaveCallbackForCreation = !!$forCreation;
        $this->revalidateDataAfterBeforeSaveCallbackForUpdate = !!$forUpdate;
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

}