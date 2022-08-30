<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Support\Arr;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\Relation;
use Swayok\Utils\Set;
use Swayok\Utils\StringUtils;

class FormConfig extends ScaffoldSectionConfig
{
    
    public const VALIDATOR_FOR_ID = 'required|integer|min:1';
    
    protected string $template = 'cmf::scaffold.form';
    protected string $bulkEditingTemplate = 'cmf::scaffold.bulk_edit_form';
    
    protected bool $allowRelationsInValueViewers = true;
    protected bool $allowComplexValueViewerNames = true;
    
    /**
     * Fields list that can be edited in bulk (for many records at once)
     * @var FormInput[]
     */
    protected array $bulkEditableColumns = [];
    /**
     * @var int|string|null
     */
    protected $itemId;
    
    protected ?bool $hasFiles = null;
    protected ?bool $hasOptionsLoader = null;
    
    protected ?\Closure $validators = null;
    protected ?\Closure $validatorsForCreate = null;
    protected ?\Closure $validatorsForEdit = null;
    
    protected ?\Closure $defaultValuesModifier = null;
    protected ?\Closure $additionalHtmlForForm = null;
    protected ?\Closure $incomingDataModifier = null;
    protected ?\Closure $incomingDataModifierForBulkEdit = null;
    protected ?\Closure $beforeSaveCallback = null;
    protected ?\Closure $afterSaveCallback = null;
    protected bool $revalidateDataAfterBeforeSaveCallbackForCreation = false;
    protected bool $revalidateDataAfterBeforeSaveCallbackForUpdate = false;
    protected ?\Closure $beforeValidateCallback = null;
    protected ?\Closure $validationSuccessCallback = null;
    protected ?\Closure $beforeBulkEditDataSaveCallback = null;
    protected ?\Closure $bulkEditAfterSaveCallback = null;
    
    protected array $tabs = [];
    protected ?int $currentTab = null;
    protected array $inputGroups = [];
    protected ?int $currentInputsGroup = null;
    
    protected ?array $tooltips = null;
    
    protected function getValidator(): ValidationFactoryContract
    {
        return $this->getCmfConfig()->getLaravelApp()->make('validator');
    }
    
    /**
     * @return static
     */
    public function setBulkEditingTemplate(string $laravelViewPath)
    {
        $this->bulkEditingTemplate = $laravelViewPath;
        return $this;
    }
    
    public function getBulkEditingTemplate(): string
    {
        if (empty($this->bulkEditingTemplate)) {
            throw new \UnexpectedValueException('The view file for bulk editing is not set');
        }
        return $this->bulkEditingTemplate;
    }
    
    protected function createValueRenderer(): InputRenderer
    {
        return InputRenderer::create();
    }
    
    /**
     * @param FormInput[] $formInputs
     * @return static
     */
    public function setValueViewers(array $formInputs)
    {
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
     * @return static
     */
    public function setFormInputs(array $formInputs)
    {
        return $this->setValueViewers($formInputs);
    }
    
    /**
     * @return static
     */
    public function addTab(string $tabLabel, array $formInputs)
    {
        $this->newTab($tabLabel);
        $this->setFormInputs($formInputs);
        $this->currentTab = null;
        return $this;
    }
    
    /**
     * Add inputs to existing tab. If tab not exists - new one will be created
     * @return static
     */
    public function updateTab(string $tabLabel, array $formInputs)
    {
        $tabExists = false;
        foreach ($this->getTabs() as $tabIndex => $tabInfo) {
            if (Arr::get($tabInfo, 'label') === $tabLabel) {
                $this->currentTab = $tabIndex;
                $this->currentInputsGroup = null;
                if (count((array)Arr::get($tabInfo, 'groups', [])) === 1 && is_int(array_keys($tabInfo['groups'])[0])) {
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
    
    protected function newInputsGroup(string $label): void
    {
        if ($this->currentTab === null) {
            $this->newTab('');
        }
        $this->currentInputsGroup = count($this->inputGroups);
        $this->tabs[$this->currentTab]['groups'][] = $this->currentInputsGroup;
        $this->inputGroups[] = [
            'label' => $label,
            'inputs_names' => [],
        ];
    }
    
    protected function newTab(string $label): void
    {
        $this->currentTab = count($this->tabs);
        $this->tabs[] = [
            'label' => $label,
            'groups' => [],
        ];
        $this->currentInputsGroup = null;
    }
    
    /**
     * @return FormInput[]
     */
    public function getFormInputs(): array
    {
        return $this->getValueViewers();
    }
    
    public function hasFormInput(string $name): bool
    {
        return $this->hasValueViewer($name);
    }
    
    public function getFormInput(string $name): FormInput
    {
        return $this->getValueViewer($name);
    }
    
    public function getTabs(): array
    {
        return $this->tabs;
    }
    
    public function getInputsGroups(): array
    {
        return $this->inputGroups;
    }
    
    /**
     * @return static
     */
    public function addValueViewer(string $name, ?AbstractValueViewer &$viewer = null, bool $autodetectIfLinkedToDbColumn = false)
    {
        parent::addValueViewer($name, $viewer, $autodetectIfLinkedToDbColumn);
        if ($this->currentInputsGroup === null) {
            $this->newInputsGroup('');
        }
        $this->inputGroups[$this->currentInputsGroup]['inputs_names'][] = $name;
        return $this;
    }
    
    protected function collectPresetValidators(bool $isCreation): array
    {
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
     * @return static
     */
    public function setTooltipsForInputs(array $tooltips)
    {
        $this->tooltips = $tooltips;
        return $this;
    }
    
    public function getTooltipsForInputs(): array
    {
        if ($this->tooltips === null) {
            $resourceName = $this->getScaffoldConfig()->getResourceName();
            if (!empty($resourceName)) {
                $this->setTooltipsForInputs($this->translate(null, 'tooltip'));
            }
            // make sure tooltips is always an array
            if (!is_array($this->tooltips)) {
                $this->tooltips = [];
            }
        }
        return $this->tooltips;
    }
    
    public function hasTooltipForInput(string $inputName): bool
    {
        return (
            ($this->hasFormInput($inputName) && $this->getFormInput($inputName)->hasTooltip())
            || !empty($this->getTooltipsForInputs()[$inputName])
        );
    }
    
    /**
     * @param string $inputName
     * @return mixed
     */
    public function getTooltipForInput(string $inputName): string
    {
        if ($this->hasFormInput($inputName) && $this->getFormInput($inputName)->hasTooltip()) {
            return $this->getFormInput($inputName)->getTooltip();
        } else {
            return Arr::get($this->getTooltipsForInputs(), $inputName, '');
        }
    }
    
    /**
     * @param InputRenderer $renderer
     * @param FormInput $valueViewer
     * @return void
     */
    protected function configureDefaultValueRenderer(ValueRenderer $renderer, RenderableValueViewer $valueViewer): void
    {
        parent::configureDefaultValueRenderer($renderer, $valueViewer);
        $relation = $valueViewer->getRelation();
        if (
            $valueViewer->isLinkedToDbColumn()
            && (
                !$relation
                || $relation->getType() !== Relation::HAS_MANY
            )
        ) {
            $this->configureRendererByColumnConfig($renderer, $valueViewer);
        }
        if ($valueViewer->hasDefaultRendererConfigurator()) {
            call_user_func($valueViewer->getDefaultRendererConfigurator(), $renderer, $valueViewer);
        }
    }
    
    protected function configureRendererByColumnConfig(InputRenderer $renderer, FormInput $formInput): void
    {
        if ($formInput::isComplexViewerName($formInput->getName())) {
            $renderer->setIsRequired(false);
        } else {
            $renderer->setIsRequired($formInput->getTableColumn()->isValueRequiredToBeNotEmpty());
        }
    }
    
    /**
     * @return static
     * @throws \BadMethodCallException
     */
    public function setBulkEditableColumns(array $columns)
    {
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
     * @return static
     * @throws \InvalidArgumentException
     */
    public function addBulkEditableColumn(string $name, FormInput $formInput = null)
    {
        $tableStructure = $this->getTable()->getTableStructure();
        if ((!$formInput || $formInput->isLinkedToDbColumn()) && !$tableStructure::hasColumn($name)) {
            throw new \InvalidArgumentException(get_class($tableStructure) . " has no column [$name]");
        } elseif ($tableStructure::getColumn($name)->isItAFile()) {
            throw new \InvalidArgumentException(
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
    public function getBulkEditableColumns(): array
    {
        return $this->bulkEditableColumns;
    }
    
    /** @noinspection PhpUnusedParameterInspection */
    protected function getNextBulkEditableColumnPosition(FormInput $formInput): int
    {
        return count($this->bulkEditableColumns);
    }
    
    /**
     * @return int|string|null
     */
    public function getItemId()
    {
        return $this->itemId;
    }
    
    /**
     * @param int|string $itemId
     * @return static
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
        return $this;
    }
    
    public function hasFiles(): bool
    {
        if ($this->hasFiles === null) {
            $filesViewers = array_intersect(
                $this->getValueViewers(),
                $this->getTable()->getTableStructure()->getFileColumns()
            );
            $this->hasFiles = !empty($filesViewers);
        }
        return $this->hasFiles;
    }
    
    public function hasOptionsLoader(): bool
    {
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
    public function loadOptions($pkValue): array
    {
        // todo: maybe use $this->itemId ???
        $options = [];
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
    public function loadOptionsForInput(string $inputName, $pkValue, ?string $keywords): array
    {
        // todo: maybe use $this->itemId ???
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
    
    public function createValueViewer(): FormInput
    {
        return FormInput::create();
    }
    
    /**
     * @return FormInput[]
     */
    public function getInputsWithOwnValueSavingMethods(): array
    {
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
    public function getValidatorsForEdit(array $updates, ?RecordInterface $record): array
    {
        return array_merge(
            $this->collectPresetValidators(false),
            $this->validators ? call_user_func($this->validators, $updates) : [],
            $this->validatorsForEdit ? call_user_func($this->validatorsForEdit, $updates, $record) : []
        );
    }
    
    /**
     * @param \Closure $validatorsForEdit - function (array $updates, ?RecordInterface $record) { return []; }
     * Note: You can insert fields from $updates using '{{field_name}}'
     * @return static
     */
    public function addValidatorsForEdit(\Closure $validatorsForEdit)
    {
        $this->validatorsForEdit = $validatorsForEdit;
        return $this;
    }
    
    public function getValidatorsForCreate(array $data): array
    {
        return array_merge(
            $this->collectPresetValidators(true),
            $this->validators ? call_user_func($this->validators, $data) : [],
            $this->validatorsForCreate ? call_user_func($this->validatorsForCreate, $data) : []
        );
    }
    
    /**
     * @param \Closure $validatorsForCreate = function (array $data) { return []; }
     * Note: You can insert fields from received data via '{{field_name}}'
     * @return static
     */
    public function addValidatorsForCreate(\Closure $validatorsForCreate)
    {
        $this->validatorsForCreate = $validatorsForCreate;
        return $this;
    }
    
    /**
     * @param \Closure $validators = function (array $data) { return []; }
     * Note: You can insert fields from received data via '{{field_name}}'
     * @return static
     */
    public function setValidators(\Closure $validators)
    {
        $this->validators = $validators;
        return $this;
    }
    
    public function validateDataForCreate(array $data, array $messages = [], bool $isRevalidation = false): array
    {
        return $this->validateData(
            $data,
            $this->getValidatorsForCreate($data),
            $messages,
            $isRevalidation
        );
    }
    
    public function validateDataForEdit(array $data, RecordInterface $record, array $messages = [], bool $isRevalidation = false): array
    {
        return $this->validateData(
            $data,
            $this->getValidatorsForEdit($data, $record),
            $messages,
            $isRevalidation
        );
    }
    
    public function validateDataForBulkEdit(array $data, array $messages = [], bool $isRevalidation = false): array
    {
        $rules = array_intersect_key($this->getValidatorsForEdit($data, null), $data);
        if (empty($rules)) {
            return [];
        }
        return $this->validateData($data, $rules, $messages, $isRevalidation, true);
    }
    
    /**
     * @param \Closure $callback - function (array $data, $isRevalidation): ?array { return null; }
     * Note: callback MUST return empty value if everything is ok or array with errors
     * @return static
     */
    public function setBeforeValidateCallback(\Closure $callback)
    {
        $this->beforeValidateCallback = $callback;
        return $this;
    }
    
    /**
     * @return array - errors (empty array - no errors)
     */
    public function beforeValidate(array $data, bool $isRevalidation): array
    {
        if (!empty($this->beforeValidateCallback)) {
            $errors = call_user_func($this->beforeValidateCallback, $data, $isRevalidation);
            if (empty($errors)) {
                return [];
            } else if (is_array($errors)) {
                return $errors;
            }
            throw new \UnexpectedValueException(
                'beforeValidateCallback must return array with key-value pairs (errors list) or null (no errors)'
            );
        }
        return [];
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function modifyIncomingDataBeforeValidation(array $data, bool $isCreation, bool $isBulkEdit = false): array
    {
        $inputs = $isBulkEdit ? $this->getBulkEditableColumns() : $this->getFormInputs();
        foreach ($inputs as $inputName => $formInput) {
            if (($isCreation && !$formInput->isShownOnCreate()) || (!$isCreation && !$formInput->isShownOnEdit())) {
                continue;
            }
            if ($formInput::isComplexViewerName($inputName)) {
                $inputName = implode('.', $formInput::splitComplexViewerName($inputName));
            }
            if (Arr::has($data, $inputName)) {
                Arr::set(
                    $data,
                    $inputName,
                    $formInput->modifySubmitedValueBeforeValidation(Arr::get($data, $inputName, ''), $data)
                );
            }
        }
        if ($isBulkEdit) {
            if ($this->incomingDataModifierForBulkEdit) {
                $data = call_user_func($this->incomingDataModifierForBulkEdit, $data, $isCreation, $this);
                if (!is_array($data)) {
                    throw new \UnexpectedValueException('incomingDataModifierForBulkEdit closure must return an array');
                }
            }
        } elseif ($this->incomingDataModifier) {
            $data = call_user_func($this->incomingDataModifier, $data, $isCreation, $this);
            if (!is_array($data)) {
                throw new \UnexpectedValueException('incomingDataModifier closure must return an array');
            }
        }
        return $data;
    }
    
    /**
     * @param \Closure $modifier - function (array $data, $isCreation, FormConfig $formConfig) { return $data; }
     * @return static
     */
    public function setIncomingDataModifier(\Closure $modifier)
    {
        $this->incomingDataModifier = $modifier;
        return $this;
    }
    
    /**
     * @param \Closure $modifier - function (array $data, FormConfig $formConfig) { return $data; }
     * @return static
     */
    public function setIncomingDataModifierForBulkEdit(\Closure $modifier)
    {
        $this->incomingDataModifierForBulkEdit = $modifier;
        return $this;
    }
    
    /**
     * @param array $data
     * @param array $validators - supports inserts in format "{{id}}" where "id" can be any key from $data
     * @param array $messages
     * @param bool $isRevalidation
     * @param bool $isBulkEdit
     * @return array - array: errors (empty array - no errors)
     */
    public function validateData(
        array $data,
        array $validators,
        array $messages = [],
        bool $isRevalidation = false,
        bool $isBulkEdit = false
    ): array {
        $errors = $this->beforeValidate($data, $isRevalidation);
        if (!empty($errors)) {
            return $errors;
        }
        
        if (empty($validators)) {
            return [];
        }
        if (empty($messages)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $messages = $this->translate(null, 'validation');
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
            } elseif (is_array($value)) {
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
        $validator = $this->getValidator()->make($data, $validators, $messages);
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
        
        $errors = $this->onValidationSuccess($data, $isRevalidation, $isBulkEdit);
        if (!empty($errors)) {
            return $errors;
        }
        
        return [];
    }
    
    /**
     * Called after request data validation and before specific callbacks and data saving.
     * Note: if you need to revalidate data after callback - use setRevalidateDataAfterBeforeSaveCallback() method
     * Note: is not applied to bulk edit!
     * @param \Closure $callback = function ($isCreation, array $validatedData, FormConfig $formConfig) { return $validatedData; }
     * @return static
     */
    public function setBeforeSaveCallback(\Closure $callback)
    {
        $this->beforeSaveCallback = $callback;
        return $this;
    }
    
    public function hasBeforeSaveCallback(): bool
    {
        return !empty($this->beforeSaveCallback);
    }
    
    public function getBeforeSaveCallback(): ?\Closure
    {
        return $this->beforeSaveCallback;
    }
    
    /**
     * Called after request data validation and before specific callbacks and data saving.
     * Note: if you need to revalidate data after callback - use setRevalidateDataAfterBeforeSaveCallback() method
     * Note: is not applied to bulk edit!
     * @param \Closure $callback = function (array $validatedData, FormConfig $formConfig) { return $validatedData; }
     * @return static
     */
    public function setBeforeBulkEditDataSaveCallback(\Closure $callback)
    {
        $this->beforeBulkEditDataSaveCallback = $callback;
        return $this;
    }
    
    public function hasBeforeBulkEditDataSaveCallback(): bool
    {
        return !empty($this->beforeBulkEditDataSaveCallback);
    }
    
    public function getBeforeBulkEditDataSaveCallback(): ?\Closure
    {
        return $this->beforeBulkEditDataSaveCallback;
    }
    
    /**
     * @return static
     */
    public function setRevalidateDataAfterBeforeSaveCallback(bool $forCreation, bool $forUpdate)
    {
        $this->revalidateDataAfterBeforeSaveCallbackForCreation = $forCreation;
        $this->revalidateDataAfterBeforeSaveCallbackForUpdate = $forUpdate;
        return $this;
    }
    
    public function shouldRevalidateDataAfterBeforeSaveCallback(bool $isCreation): bool
    {
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
     * @return static
     */
    public function setValidationSuccessCallback(\Closure $calback)
    {
        $this->validationSuccessCallback = $calback;
        return $this;
    }
    
    /**
     * @return array - validation errors (empty array - no errors)
     * @throws \UnexpectedValueException
     */
    protected function onValidationSuccess(array $data, bool $isRevalidation, bool $isBulkEdit): array
    {
        if (!empty($this->validationSuccessCallback)) {
            $errors = call_user_func($this->validationSuccessCallback, $data, $isRevalidation, $isBulkEdit);
            if (empty($errors)) {
                return [];
            } else if (is_array($errors)) {
                return $errors;
            }
            throw new \UnexpectedValueException(
                'validationSuccessCallback must return array with key-value pairs (errors list) or null (no errors)'
            );
        }
        return [];
    }
    
    /**
     * Callback is called after successfully saving data but before model's commit()
     * It must return true if everything is ok or instance of \Symfony\Component\HttpFoundation\JsonResponse
     * Response success detected by HTTP code of \Illuminate\Http\JsonResponse: code < 400 - success; code >= 400 - error
     * @param \Closure $callback - function (bool $isCreation, array $validatedData, RecordInterface $record, FormConfig $formConfig) { return true; }
     * @return static
     */
    public function setAfterSaveCallback(\Closure $callback)
    {
        $this->afterSaveCallback = $callback;
        return $this;
    }
    
    public function hasAfterSaveCallback(): bool
    {
        return !empty($this->afterSaveCallback);
    }
    
    public function getAfterSaveCallback(): ?\Closure
    {
        return $this->afterSaveCallback;
    }
    
    /**
     * Callback is called after successfully saving data but before model's commit()
     * It must return true if everything is ok or instance of \Symfony\Component\HttpFoundation\JsonResponse
     * Response success detected by HTTP code of \Illuminate\Http\JsonResponse: code < 400 - success; code >= 400 - error
     * @param \Closure $callback - function (array $validatedData, FormConfig $formConfig) { return []; }
     * @return static
     */
    public function setBulkEditAfterSaveCallback(\Closure $callback)
    {
        $this->bulkEditAfterSaveCallback = $callback;
        return $this;
    }
    
    public function hasBulkEditAfterSaveCallback(): bool
    {
        return !empty($this->bulkEditAfterSaveCallback);
    }
    
    public function getBulkEditAfterSaveCallback(): ?\Closure
    {
        return $this->bulkEditAfterSaveCallback;
    }
    
    /**
     * @param array|\Closure $arrayOrClosure
     *      - \Closure: funciton (array $defaults, FormConfig $formConfig): array { return $defaults; }
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setDefaultValuesModifier(\Closure $arrayOrClosure)
    {
        $this->defaultValuesModifier = $arrayOrClosure;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function alterDefaultValues(array $defaults): array
    {
        if (!empty($this->defaultValuesModifier)) {
            $defaults = call_user_func($this->defaultValuesModifier, $defaults, $this);
            if (!is_array($defaults)) {
                throw new \UnexpectedValueException('defaultValuesModifier closure must return array');
            }
        }
        return $defaults;
    }
    
    /**
     * @param \Closure $stringOfClosure - function (FormConfig $formConfig): string { return '<div>'; }
     * @return static
     */
    public function setAdditionalHtmlForForm(\Closure $stringOfClosure)
    {
        $this->additionalHtmlForForm = $stringOfClosure;
        return $this;
    }
    
    public function getAdditionalHtmlForForm(): string
    {
        if (empty($this->additionalHtmlForForm)) {
            return '';
        } else {
            $html = call_user_func($this->additionalHtmlForForm, $this);
            if (!is_string($html)) {
                throw new \UnexpectedValueException('additionalHtmlForForm closure must return string');
            }
            return $html;
        }
    }
    
    public function beforeRender(): void
    {
        foreach ($this->getTooltipsForInputs() as $inputName => $tooltip) {
            if ($this->hasFormInput($inputName)) {
                $input = $this->getFormInput($inputName);
                if (!$input->hasTooltip()) {
                    $input->setTooltip($tooltip);
                }
            }
        }
    }
    
    protected function getSectionTranslationsPrefix(?string $subtype = null): string
    {
        return $subtype === 'value_viewer' ? 'form.input' : 'form';
    }
    
}
