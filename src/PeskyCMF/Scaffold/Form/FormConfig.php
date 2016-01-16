<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldActionException;
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
    public function createFieldConfig($fieldName) {
        $columnConfig = $this->getModel()->getTableColumn($fieldName);
        $config = FormFieldConfig::create()
            ->setType($columnConfig->getType());
        return $config;
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
     * @return array
     */
    public function validateDataForCreate(array $data, array $messages = []) {
        return $this->validateData($data, $this->getValidatorsForCreate(), $messages);
    }

    /**
     * @param array $data
     * @param array $messages
     * @return array
     */
    public function validateDataForEdit(array $data, array $messages = []) {
        return $this->validateData($data, $this->getValidatorsForEdit(), $messages);
    }

    /**
     * @param array $data
     * @param array $validators
     * @param array $messages
     * @return array
     * @throws ScaffoldActionException
     */
    public function validateData(array $data, array $validators, array $messages = []) {
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

}