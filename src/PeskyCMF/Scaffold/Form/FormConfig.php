<?php


namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\ScaffoldSectionConfigException;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;
use Swayok\Utils\Set;
use Swayok\Utils\StringUtils;

class FormConfig extends ScaffoldSectionConfig {

    protected $template = 'cmf::scaffold.form';
    protected $bulkEditingTemplate = 'cmf::scaffold.bulk_edit_form';

    protected $allowRelationsInValueViewers = true;
    protected $allowComplexValueViewerNames = true;

    /**
     * Fields list that can be edited in bulk (for many records at once)
     * @var FormInput[]
     */
    protected $bulkEditableColumns = [];
    /**
     * @var null|mixed
     */
    protected $itemId;
    /**
     * @var bool
     */
    protected $hasFiles = false;

    /** @var bool */
    protected $hasOptionsLoader;
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

    public const VALIDATOR_FOR_ID = 'required|integer|min:1';
    /** @var \Closure|null */
    protected $incomingDataModifier;
    /** @var \Closure|null */
    protected $incomingDataModifierForBulkEdit;
    /** @var \Closure|null */
    protected $beforeSaveCallback;
     /** @var \Closure|null */
    protected $afterSaveCallback;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForCreation = false;
    /** @var bool */
    protected $revalidateDataAfterBeforeSaveCallbackForUpdate = false;
    /** @var \Closure|null */
    protected $beforeValidateCallback;
    /** @var \Closure|null */
    protected $validationSuccessCallback;
    /** @var \Closure|null */
    protected $beforeBulkEditDataSaveCallback;
    /** @var \Closure|null */
    protected $afterBulkEditDataSaveCallback;

    /** @var array */
    protected $tabs = [];
    /** @var null|int */
    protected $currentTab;
    /** @var array */
    protected $inputGroups = [];
    /** @var null|int */
    protected $currentInputsGroup;

    /** @var array|null */
    protected $tooltips;

    /**
     * @param string $laravelViewPath
     * @return $this
     */
    public function setBulkEditingTemplate($laravelViewPath) {
        $this->bulkEditingTemplate = $laravelViewPath;
        return $this;
    }

    /**
     * @return string
     * @throws ScaffoldSectionConfigException
     */
    public function getBulkEditingTemplate() {
        if (empty($this->bulkEditingTemplate)) {
            throw new ScaffoldSectionConfigException($this, 'The view file for bulk editing is not set');
        }
        return $this->bulkEditingTemplate;
    }

    /**
     * @return InputRenderer
     */
    protected function createValueRenderer() {
        return InputRenderer::create();
    }

    /**
     * @param array $formInputs
     * @return $this
     */
    public function setValueViewers(array $formInputs) {
        /** @var AbstractValueViewer|null $config */
        foreach ($formInputs as $name => $config) {
            if (is_array($config)) {
                /** @var array $config */
                $this->newInputsGroup(is_int($name) ? '' : $name);
                foreach ((array)$config as $groupInputName => $groupInputConfig) {
                    if (is_int($groupInputName)) {
                        $groupInputName = $groupInputConfig;
                        $groupInputConfig = null;
                    }
                    $this->addValueViewer($groupInputName, $groupInputConfig);
                }
                $this->currentInputsGroup = null;
            } else {
                if (is_int($name)) {
                    $name = $config;
                    $config = null;
                }
                $this->normalizeAndAddValueViewer($name, $config);
            }
        }
        return $this;
    }

    /**
     * Alias for setValueViewers
     * @param array $formInputs - formats:
     * - ['name1', 'name2' => FormInput::create(), ...]
     * - ['group lablel' => ['name1', 'name2' => FormInput::create(), ...]
     * Also you may use '/' as value to separate inputs with <hr>
     * @return $this
     */
    public function setFormInputs(array $formInputs) {
        return $this->setValueViewers($formInputs);
    }

    /**
     * @param string $tabLabel
     * @param array $formInputs
     * @return $this
     */
    public function addTab($tabLabel, array $formInputs) {
        $this->newTab($tabLabel);
        $this->setFormInputs($formInputs);
        $this->currentTab = null;
        return $this;
    }

    /**
     * Add inputs to existing tab. If tab not exists - new one will be created
     * @param $tabLabel
     * @param array $formInputs
     * @return $this
     */
    public function updateTab($tabLabel, array $formInputs) {
        $tabExists = false;
        foreach ($this->getTabs() as $tabIndex => $tabInfo) {
            if (array_get($tabInfo, 'label') === $tabLabel) {
                $this->currentTab = $tabIndex;
                $this->currentInputsGroup = null;
                if (count((array)array_get($tabInfo, 'groups', [])) === 1 && is_int(array_keys($tabInfo['groups'])[0])) {
                    $this->currentInputsGroup = array_keys($tabInfo['groups'])[0];
                }
                $tabExists = true;
                break;
            }
        }
        if (!$tabExists) {
            $this->newTab($tabLabel);
        }
        $this->setFormInputs($formInputs);
        $this->currentTab = null;
        $this->currentInputsGroup = null;
        return $this;
    }

    /**
     * @param $label
     */
    protected function newInputsGroup($label) {
        if ($this->currentTab === null) {
            $this->newTab('');
        }
        $this->currentInputsGroup = count($this->inputGroups);
        $this->tabs[$this->currentTab]['groups'][] = $this->currentInputsGroup;
        $this->inputGroups[] = [
            'label' => $label,
            'inputs_names' => []
        ];
    }

    /**
     * @param $label
     */
    protected function newTab($label) {
        $this->currentTab = count($this->tabs);
        $this->tabs[] = [
            'label' => $label,
            'groups' => []
        ];
        $this->currentInputsGroup = null;
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
     * @param string $name
     * @return FormInput|AbstractValueViewer
     */
    public function getFormInput($name) {
        return $this->getValueViewer($name);
    }

    /**
     * @return array
     */
    public function getTabs() {
        return $this->tabs;
    }

    /**
     * @return array
     */
    public function getInputsGroups() {
        return $this->inputGroups;
    }

    /**
     * @param string $name
     * @param AbstractValueViewer|null $viewer
     * @param bool $autodetectIfLinkedToDbColumn
     * @return $this
     */
    public function addValueViewer($name, AbstractValueViewer &$viewer = null, bool $autodetectIfLinkedToDbColumn = false) {
        parent::addValueViewer($name, $viewer, $autodetectIfLinkedToDbColumn);
        if ($this->currentInputsGroup === null) {
            $this->newInputsGroup('');
        }
        $this->inputGroups[$this->currentInputsGroup]['inputs_names'][] = $name;
        return $this;
    }

    /**
     * @param bool $isCreation
     * @return array
     */
    protected function collectPresetValidators($isCreation) {
        $validators = [];
        foreach ($this->getFormInputs() as $formInput) {
            if (($isCreation && !$formInput->isShownOnCreate()) || (!$isCreation && !$formInput->isShownOnEdit())) {
                continue; //< input not shown
            }
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $validators = array_merge($validators, $formInput->getValidators($isCreation));
        }
        return $validators;
    }

    /**
     * @param array $tooltips - anything except array will be ignored so it won't crash when there is no
     *      translations for tooltips in dictionaries (ex: trans('cmf.admins.form.tooltip') may be array or string)
     * @return $this
     */
    public function setTooltipsForInputs($tooltips) {
        if (is_array($tooltips)) {
            $this->tooltips = $tooltips;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTooltipsForInputs() {
        if ($this->tooltips === null) {
            $resourceName = $this->getScaffoldConfig()->getResourceName();
            if (!empty($resourceName)) {
                /** @noinspection PhpParamsInspection */
                $this->setTooltipsForInputs($this->translate(null, 'tooltip'));
            }
            // make sure tooltips is always an array
            if (!is_array($this->tooltips)) {
                $this->tooltips = [];
            }
        }
        return $this->tooltips;
    }

    /**
     * @param string $inputName
     * @return bool
     */
    public function hasTooltipForInput($inputName) {
        return (
            ($this->hasFormInput($inputName) && $this->getFormInput($inputName)->hasTooltip())
            || !empty($this->getTooltipsForInputs()[$inputName])
        );
    }

    /**
     * @param string $inputName
     * @return mixed
     */
    public function getTooltipForInput($inputName) {
        if ($this->hasFormInput($inputName) && $this->getFormInput($inputName)->hasTooltip()) {
            return $this->getFormInput($inputName)->hasTooltip();
        } else {
            return array_get($this->getTooltipsForInputs(), $inputName, '');
        }
    }

    protected function configureDefaultValueRenderer(ValueRenderer $renderer, RenderableValueViewer $formInput) {
        parent::configureDefaultValueRenderer($renderer, $formInput);
        if (
            $formInput->isLinkedToDbColumn()
            && (
                !$formInput->hasRelation()
                || $formInput->getRelation()->getType() !== Relation::HAS_MANY
            )
        ) {
            $this->configureRendererByColumnConfig($renderer, $formInput);
        }
        if ($formInput->hasDefaultRendererConfigurator()) {
            call_user_func($formInput->getDefaultRendererConfigurator(), $renderer, $formInput);
        }
    }

    /**
     * @param InputRenderer|ValueRenderer $renderer
     * @param FormInput|RenderableValueViewer $formInput
     */
    protected function configureRendererByColumnConfig(ValueRenderer $renderer, FormInput $formInput) {
        if ($formInput::isComplexViewerName($formInput->getName())) {
            $renderer->setIsRequired(false);
        } else {
            $renderer->setIsRequired($formInput->getTableColumn()->isValueRequiredToBeNotEmpty());
        }
    }

    /**
     * @param array $columns
     * @return $this
     * @throws \BadMethodCallException
     */
    public function setBulkEditableColumns(array $columns) {
        if (empty($this->valueViewers)) {
            throw new \BadMethodCallException('setValueViewers() method must be called before');
        }
        foreach ($columns as $name => $config) {
            if (is_int($name)) {
                $name = $config;
                $config = null;
            }
            $this->addBulkEditableColumn($name, $config);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param null|FormInput $formInput - null: FormInput will be imported from $this->fields or created default one
     * @return $this
     * @throws ScaffoldSectionConfigException
     * @throws \InvalidArgumentException
     */
    public function addBulkEditableColumn($name, FormInput $formInput = null) {
        if ((!$formInput || $formInput->isLinkedToDbColumn()) && !$this->getTable()->getTableStructure()->hasColumn($name)) {
            throw new \InvalidArgumentException("Table {$this->getTable()->getName()} has no column [$name]");
        } else if ($this->getTable()->getTableStructure()->getColumn($name)->isItAFile()) {
            throw new ScaffoldSectionConfigException(
                $this,
                "Attaching files in bulk editing form is not suppoted. Table column: [$name]"
            );
        }
        if (!$formInput) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
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
        if ($this->hasFiles === null) {
            $this->hasFiles = !empty(
                array_intersect($this->getValueViewers(), $this->getTable()->getTableStructure()->getFileColumns())
            );
        }
        return $this->hasFiles;
    }

    /**
     * @return bool
     */
    public function hasOptionsLoader() {
        if ($this->hasOptionsLoader === null) {
            $this->hasOptionsLoader = false;
            /** @var FormInput $viewer */
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
     */
    public function loadOptions($pkValue) {
        $options = array();
        /** @var FormInput $viewer */
        foreach ($this->getValueViewers() as $viewer) {
            if ($viewer->hasOptionsLoader()) {
                $options[$viewer->getName()] = call_user_func(
                    $viewer->getOptionsLoader(),
                    $pkValue,
                    '',
                    $viewer,
                    $this
                );
            }
        }
        return $options;
    }

    /**
     * @param string $inputName
     * @param int|string|null $pkValue - primary key value
     * @param string|null $keywords - keywords for filtering
     * @return array
     */
    public function loadOptionsForInput($inputName, $pkValue, $keywords) {
        $viewer = $this->getValueViewer($inputName);
        if (!$viewer->hasOptionsLoader()) {
            return [];
        }
        $optionsMap = call_user_func(
            $viewer->getOptionsLoader(),
            $pkValue,
            $keywords,
            $viewer,
            $this
        );
        $options = [];
        foreach ($optionsMap as $value => $label) {
            if (is_array($label)) {
                $options[] = $label;
            } else {
                $options[] = [
                    'value' => $value,
                    'text' => $label,
                ];
            }
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function createValueViewer() {
        return FormInput::create();
    }

    /**
     * @return FormInput[]
     */
    public function getInputsWithOwnValueSavingMethods() {
        $ret = [];
        foreach ($this->getFormInputs() as $key => $viewer) {
            if ($viewer->hasOwnValueSavingMethod()) {
                $ret[$key] = $viewer;
            }
        }
        return $ret;
    }

    /**
     * @param array $updates
     * @param RecordInterface|null $record - RecordInterface: record before editing | null: used for bulk editing
     * @return array
     */
    public function getValidatorsForEdit(array $updates, ?RecordInterface $record) {
        return array_merge(
            $this->collectPresetValidators(false),
            $this->validators ? call_user_func($this->validators, $updates) : [],
            $this->validatorsForEdit ? call_user_func($this->validatorsForEdit, $updates, $record) : []
        );
    }

    /**
     * @param \Closure $validatorsForEdit - function (array $updates, ?RecordInterface $record) { return []; }
     * Note: You can insert fields from $updates using '{{field_name}}'
     * @return $this
     */
    public function addValidatorsForEdit(\Closure $validatorsForEdit) {
        $this->validatorsForEdit = $validatorsForEdit;
        return $this;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getValidatorsForCreate(array $data) {
        return array_merge(
            $this->collectPresetValidators(true),
            $this->validators ? call_user_func($this->validators, $data) : [],
            $this->validatorsForCreate ? call_user_func($this->validatorsForCreate, $data) : []
        );
    }

    /**
     * @param \Closure $validatorsForCreate = function (array $data) { return []; }
     * Note: You can insert fields from received data via '{{field_name}}'
     * @return $this
     */
    public function addValidatorsForCreate(\Closure $validatorsForCreate) {
        $this->validatorsForCreate = $validatorsForCreate;
        return $this;
    }

    /**
     * @param \Closure $validators = function (array $data) { return []; }
     * Note: You can insert fields from received data via '{{field_name}}'
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
     */
    public function validateDataForCreate(array $data, array $messages = [], $isRevalidation = false) {
        return $this->validateData(
            $data,
            $this->getValidatorsForCreate($data),
            $messages,
            $isRevalidation
        );
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalidation
     * @return array
     */
    public function validateDataForEdit(array $data, RecordInterface $record, array $messages = [], $isRevalidation = false) {
        return $this->validateData(
            $data,
            $this->getValidatorsForEdit($data, $record),
            $messages,
            $isRevalidation
        );
    }

    /**
     * @param array $data
     * @param array $messages
     * @param bool $isRevalidation
     * @return array
     */
    public function validateDataForBulkEdit(array $data, array $messages = [], $isRevalidation = false) {
        $rules = array_intersect_key($this->getValidatorsForEdit($data, null), $data);
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
     * @param bool $isCreation
     * @param bool $isBulkEdit
     * @return array
     * @throws \UnexpectedValueException
     */
    public function modifyIncomingDataBeforeValidation(array $data, $isCreation, $isBulkEdit = false) {
        /** @var FormInput[] $inputs */
        $inputs = $isBulkEdit ? $this->getBulkEditableColumns() : $this->getFormInputs();
        foreach ($inputs as $inputName => $formInput) {
            if (($isCreation && !$formInput->isShownOnCreate()) || (!$isCreation && !$formInput->isShownOnEdit())) {
                continue;
            }
            if ($formInput::isComplexViewerName($inputName)) {
                $inputName = implode('.', $formInput::splitComplexViewerName($inputName));
            }
            if (array_has($data, $inputName)) {
                array_set($data, $inputName, $formInput->modifySubmitedValueBeforeValidation(array_get($data, $inputName, ''), $data));
            }
        }
        if ($isBulkEdit) {
            if ($this->incomingDataModifierForBulkEdit) {
                $data = call_user_func($this->incomingDataModifierForBulkEdit, $data, $isCreation, $this);
                if (!is_array($data)) {
                    throw new \UnexpectedValueException('incomingDataModifierForBulkEdit closure must return an array');
                }
            }
        } else if ($this->incomingDataModifier) {
            $data = call_user_func($this->incomingDataModifier, $data, $isCreation, $this);
            if (!is_array($data)) {
                throw new \UnexpectedValueException('incomingDataModifier closure must return an array');
            }
        }
        return $data;
    }

    /**
     * @param \Closure $modifier - function (array $data, $isCreation, FormConfig $formConfig) { return $data; }
     * @return $this
     */
    public function setIncomingDataModifier(\Closure $modifier) {
        $this->incomingDataModifier = $modifier;
        return $this;
    }

    /**
     * @param \Closure $modifier - function (array $data, FormConfig $formConfig) { return $data; }
     * @return $this
     */
    public function setIncomingDataModifierForBulkEdit(\Closure $modifier) {
        $this->incomingDataModifierForBulkEdit = $modifier;
        return $this;
    }

    /**
     * @param array $data
     * @param array $validators - supports inserts in format "{{id}}" where "id" can be any key from $data
     * @param array $messages
     * @param bool $isRevalidation
     * @param bool $isBulkEdit
     * @return array|string|bool
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

        if (empty($validators)) {
            return [];
        }
        if (empty($messages)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $messages = cmfTransCustom('.' . $this->getTable()->getName() . '.form.validation');
        }
        if (!is_array($messages)) {
            $messages = [];
        } else {
            $messages = Set::flatten($messages);
        }
        $columnsWithArrayType = [];
        $dataForInserts = $data;
        array_walk_recursive($dataForInserts, function (&$value) {
            if ($value === null) {
                $value = 'NULL';
            }
        });
        // add 'NULL' inserts for all columns registered in table structure to prevent '{{colname}}' parts in validators
        foreach ($this->getTable()->getTableStructure()->getColumns() as $column) {
            if (!array_key_exists($column->getName(), $dataForInserts)) {
                $dataForInserts[$column->getName()] = 'NULL';
            }
        }
        foreach ($validators as $key => &$value) {
            if (is_string($value)) {
                $value = StringUtils::insert($value, $dataForInserts, ['before' => '{{', 'after' => '}}']);
                if (preg_match('%(^|\|)array%i', $value)) {
                    $columnsWithArrayType[] = $key;
                }
            } else if (is_array($value)) {
                /** @var array $value */
                foreach ($value as &$validator) {
                    if (is_string($validator)) {
                        $validator = StringUtils::insert($validator, $dataForInserts, ['before' => '{{', 'after' => '}}']);
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
     * @throws \UnexpectedValueException
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
                    throw new \UnexpectedValueException(
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
     * @param \Closure $callback - function (bool $isCreation, array $validatedData, RecordInterface $record, FormConfig $formConfig) { return true; }
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
     * @param array|\Closure $arrayOrClosure
     *      - \Closure: funciton (array $defaults, FormConfig $formConfig) { return $defaults; }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDefaultValuesModifier($arrayOrClosure) {
        if (!is_array($arrayOrClosure) && !($arrayOrClosure instanceof \Closure)) {
            throw new \InvalidArgumentException('$stringOfFunction argument must be a string or \Closure');
        }
        $this->defaultValuesModifier = $arrayOrClosure;
        return $this;
    }

    /**
     * @param array $defaults
     * @return array
     * @throws \UnexpectedValueException
     */
    public function alterDefaultValues(array $defaults) {
        if (!empty($this->defaultValuesModifier)) {
            if ($this->defaultValuesModifier instanceof \Closure) {
                $defaults = call_user_func($this->defaultValuesModifier, $defaults, $this);
                if (!is_array($defaults)) {
                    throw new \UnexpectedValueException('Default values modifier must return array');
                }
            } else {
                return array_merge($defaults, $this->defaultValuesModifier);
            }
        }
        return $defaults;
    }

    /**
     * @param $stringOfClosure - function (FormConfig $formConfig) { return '<div>'; }
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAdditionalHtmlForForm($stringOfClosure) {
        if (!is_string($stringOfClosure) && !($stringOfClosure instanceof \Closure)) {
            throw new \InvalidArgumentException('$stringOfFunction argument must be a string or \Closure');
        }
        $this->additionalHtmlForForm = $stringOfClosure;
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

    public function beforeRender() {
        foreach ($this->getTooltipsForInputs() as $inputName => $tooltip) {
            if ($this->hasFormInput($inputName)) {
                $input = $this->getFormInput($inputName);
                if (!$input->hasTooltip()) {
                    $input->setTooltip($tooltip);
                }
            }
        }
    }

    protected function getSectionTranslationsPrefix($subtype = null) {
        return $subtype === 'value_viewer' ? 'form.input' : 'form';
    }

}
