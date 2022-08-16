<?php


namespace PeskyCMF\Scaffold;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\Http\Middleware\AjaxOnly;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TempRecord;

abstract class ScaffoldConfig implements ScaffoldConfigInterface {

    use DataValidationHelper;

    /** @var DataGridConfig */
    protected $dataGridConfig = null;
    /** @var FilterConfig */
    protected $dataGridFilterConfig = null;
    /** @var ItemDetailsConfig */
    protected $itemDetailsConfig = null;
    /** @var FormConfig */
    protected $formConfig = null;

    /** @var bool */
    protected $isDetailsViewerAllowed = true;
    /** @var bool */
    protected $isCreateAllowed = true;
    /** @var bool */
    protected $isEditAllowed = true;
    /** @var bool */
    protected $isCloningAllowed = false;
    /** @var bool */
    protected $isDeleteAllowed = true;
    /**
     * Path to localization of views.
     * Usage: see $this->getLocalizationBasePath() method.
     * By default if $viewsBaseTranslationKey is empty - static::getResourceName() will be used
     * @return null|string
     */
    protected $viewsBaseTranslationKey = null;

    /**
     * @var null|ScaffoldLoggerInterface
     */
    protected $logger;

    /**
     * List of record's columns to log on record usage/modification
     * @var null|array
     */
    protected $loggableRecordColumns = null;

    /**
     * List of record's columns that should not be logged on record usage/modification
     * @var null|array
     */
    protected $notLoggableRecordColumns = null;

    /**
     * Should record's file columns be logged on record usage/modification?
     * @var bool
     */
    protected $logFileColumns = true;

    /**
     * List of record's relations and their columns to log together with record's data
     * Note: relation's data will be logged only when it is already loaded (no additional DB queries wil be done)
     * Format: ['Relation1', 'Relation2' => ['column1', 'column2']]; Default: ['*']
     * Use FALSE to disable logging of relations' data
     * @var null|array|false
     */
    protected $loggableRecordRelations = false;
    
    /**
     * @var RecordInterface|Record|CmfDbRecord
     */
    private $optimizedTableRecord;

    /**
     * ScaffoldConfig constructor.
     */
    public function __construct() {
        if ($this->viewsBaseTranslationKey === null) {
            $this->viewsBaseTranslationKey = static::getResourceName();
        }
        $this->setLogger(static::getCmfConfig()->getHttpRequestsLogger());
    }

    /**
     * @return CmfConfig
     */
    public static function getCmfConfig() {
        return CmfConfig::getPrimary();
    }

    /**
     * @return \App\Db\Admins\Admin|\Illuminate\Contracts\Auth\Authenticatable|\PeskyCMF\Db\Admins\CmfAdmin|\PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey
     */
    public static function getUser() {
        return static::getCmfConfig()->getUser();
    }

    /**
     * @return string
     */
    public static function getResourceName(): string
    {
        return static::getTable()->getName();
    }
    
    /**
     * @return array|null
     */
    public static function getMainMenuItem(): ?array
    {
        $resoureName = static::getResourceName();
        $url = routeToCmfItemsTable(static::getResourceName());
        if ($url === null) {
            // access to this menu item was denied
            return null;
        }
        return [
            'label' => cmfTransCustom($resoureName . '.menu_title'),
            'icon' => static::getIconForMenuItem(),
            'url' => $url,
            'counter' => static::getMenuItemCounterName()
        ];
    }

    protected static function getIconForMenuItem(): ?string
    {
        return null;
    }

    public static function getMenuItemCounterName(): ?string
    {
        return static::getMenuItemCounterValue() ? static::getResourceName() . '_count' : null;
    }

    public static function getMenuItemCounterValue() {
        return null;
    }

    /**
     * @param array $filters
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemsTable(array $filters = [], bool $absolute = false) {
        return routeToCmfItemsTable(static::getResourceName(), $filters, $absolute);
    }

    /**
     * @param string $actionId
     * @param array $queryArgs
     * @param bool $absolute
     * @return string
     */
    public static function getUrlCustomAction($actionId, array $queryArgs = [], bool $absolute = false) {
        return routeToCmfResourceCustomAction(static::getResourceName(), $actionId, $queryArgs, $absolute);
    }

    /**
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @return string
     */
    public static function getUrlCustomPage($pageId, array $queryArgs = [], bool $absolute = false) {
        return routeToCmfResourceCustomPage(static::getResourceName(), $pageId, $queryArgs, $absolute);
    }

    /**
     * @param mixed $itemId
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemDetails($itemId, bool $absolute = false) {
        return routeToCmfItemDetails(static::getResourceName(), $itemId, $absolute);
    }

    /**
     * @param array $data
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemAddForm(array $data = [], bool $absolute = false) {
        return routeToCmfItemAddForm(static::getResourceName(), $data, $absolute);
    }

    /**
     * @param mixed $itemId
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemEditForm($itemId, bool $absolute = false) {
        return routeToCmfItemEditForm(static::getResourceName(), $itemId, $absolute);
    }

    /**
     * @param string $inputName
     * @param bool $absolute
     * @return string
     */
    public static function getUrlForTempFileUpload(string $inputName, bool $absolute = false) {
        return routeForCmfTempFileUpload(static::getResourceName(), $inputName, $absolute);
    }

    /**
     * @param string $inputName
     * @param bool $absolute
     * @return string
     */
    public static function getUrlForTempFileDelete(string $inputName, bool $absolute = false) {
        return routeForCmfTempFileDelete(static::getResourceName(), $inputName, $absolute);
    }

    /**
     * @param mixed $itemId
     * @param string $actionId
     * @param array $queryArgs
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemCustomAction($itemId, $actionId, array $queryArgs = [], bool $absolute = false) {
        return routeToCmfItemCustomAction(static::getResourceName(), $itemId, $actionId, $queryArgs, $absolute);
    }

    /**
     * @param mixed $itemId
     * @param string $pageId
     * @param array $queryArgs
     * @param bool $absolute
     * @return string
     */
    public static function getUrlToItemCustomPage($itemId, $pageId, array $queryArgs = [], bool $absolute = false) {
        return routeToCmfItemCustomPage(static::getResourceName(), $itemId, $pageId, $queryArgs, $absolute);
    }
    
    /**
     * @return Request
     */
    public function getRequest() {
        return request();
    }
    
    public function getOptimizedTableRecord(?array $dbDataForRecord = null): RecordInterface {
        if (!$this->optimizedTableRecord) {
            $this->optimizedTableRecord = static::getTable()->newRecord()->enableReadOnlyMode()->enableTrustModeForDbData();
        } else {
            $this->optimizedTableRecord->reset();
        }
        if ($dbDataForRecord) {
            $this->optimizedTableRecord->fromDbData($dbDataForRecord);
        }
        return $this->optimizedTableRecord;
    }

    /**
     * @return array
     * @throws ScaffoldSectionConfigException
     */
    public function getConfigsForTemplatesRendering() {
        $configs = [
            'table' => static::getTable(),
            'scaffoldConfig' => $this
        ];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        if (!($configs['dataGridConfig'] instanceof DataGridConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createDataGridConfig() should return instance of DataGridConfig class');
        }
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        if (!($configs['dataGridFilterConfig'] instanceof FilterConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createDataGridFilterConfig() should return instance of FilterConfig class');
        }
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        if (!($configs['itemDetailsConfig'] instanceof ItemDetailsConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createItemDetailsConfig() should return instance of ItemDetailsConfig class');
        }
        $configs['formConfig'] = $this->getFormConfig();
        if (!($configs['formConfig'] instanceof FormConfig)) {
            throw new ScaffoldSectionConfigException(null, 'createFormConfig() should return instance of FormConfig class');
        }
        $configs['dataGridConfig']->beforeRender();
        $configs['dataGridFilterConfig']->beforeRender();
        $configs['itemDetailsConfig']->beforeRender();
        $configs['formConfig']->beforeRender();
        return $configs;
    }

    /**
     * @return DataGridConfig
     */
    protected function createDataGridConfig() {
        return DataGridConfig::create(static::getTable(), $this);
    }

    /**
     * @return FilterConfig
     */
    protected function createDataGridFilterConfig() {
        return FilterConfig::create(static::getTable(), $this);
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create(static::getTable(), $this);
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create(static::getTable(), $this);
    }

    public function getDataGridConfig(): DataGridConfig
    {
        if (empty($this->dataGridConfig)) {
            $this->dataGridConfig = $this->createDataGridConfig();
            $this->dataGridConfig->finish();
        }
        return $this->dataGridConfig;
    }

    public function getDataGridFilterConfig(): FilterConfig
    {
        if (empty($this->dataGridFilterConfig)) {
            $this->dataGridFilterConfig = $this->createDataGridFilterConfig();
            $this->dataGridFilterConfig->finish();
        }
        return $this->dataGridFilterConfig;
    }

    /**
     * @return ItemDetailsConfig
     */
    public function getItemDetailsConfig(): ItemDetailsConfig
    {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
            $this->itemDetailsConfig->finish();
        }
        return $this->itemDetailsConfig;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig(): FormConfig
    {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
            $this->formConfig->finish();
        }
        return $this->formConfig;
    }

    /**
     * @return bool
     */
    public function isSectionAllowed(): bool
    {
        return \Gate::allows('resource.view', [static::getResourceName()]);
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed(): bool
    {
        return (
            $this->isCreateAllowed
            && $this->isSectionAllowed()
            && \Gate::allows('resource.create', [static::getResourceName()])
        );
    }

    /**
     * @return boolean
     */
    public function isEditAllowed(): bool
    {
        return $this->isEditAllowed && $this->isSectionAllowed();
    }

    /**
     * @return bool
     */
    public function isCloningAllowed() {
        return $this->isCloningAllowed && $this->isCreateAllowed();
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed(): bool
    {
        return $this->isDetailsViewerAllowed && $this->isSectionAllowed();
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed(): bool
    {
        return $this->isDeleteAllowed && $this->isSectionAllowed();
    }

    /**
     * Detects if $record deletable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDeleteAllowed(array $record): bool
    {
        return $this->isDeleteAllowed() && \Gate::allows('resource.delete', [static::getResourceName(), $record]);
    }

    /**
     * Detects if $record editable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordEditAllowed(array $record): bool
    {
        return $this->isEditAllowed() && \Gate::allows('resource.update', [static::getResourceName(), $record]);
    }

    /**
     * Detects if $record details can be displayed or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDetailsAllowed(array $record): bool
    {
        return (
            $this->isDetailsViewerAllowed()
            && \Gate::allows('resource.details', [static::getResourceName(), $record])
        );
    }

    /**
     * @param string $section - main sections are: 'datagrid.column', 'item_details.field', 'form.input'
     * @param AbstractValueViewer $viewer
     * @param string|array $suffix - pass array here to be used as $parameters but $parameters should be empty
     * @param array $parameters
     * @return string
     */
    public function translateForViewer($section, AbstractValueViewer $viewer, $suffix = '', array $parameters = []) {
        if (is_array($suffix) && empty($parameters)) {
            $parameters = $suffix;
            $suffix = '';
        }
        return $this->translate($section, rtrim("{$viewer->getNameForTranslation()}_{$suffix}", '_'), $parameters);
    }

    /**
     * @param string $section - main sections are: 'form.tooltip'
     * @param string|array $suffix - pass array here to be used as $parameters but $parameters should be empty
     * @param array $parameters
     * @return array|string
     */
    public function translate($section, $suffix = '', array $parameters = []) {
        if (is_array($suffix) && empty($parameters)) {
            $parameters = $suffix;
            $suffix = '';
        }
        return cmfTransCustom(rtrim(".{$this->viewsBaseTranslationKey}.{$section}.{$suffix}", '.'), $parameters);
    }

    /**
     * Translate general UI elements (button labels, tooltips, messages, etc..)
     * @param $path
     * @param array $parameters
     * @return mixed
     */
    public function translateGeneral($path, array $parameters = []) {
        $text = $this->translate($path, '', $parameters);
        if (preg_match('%\.' . preg_quote($path, '%') . '$%', $text)) {
            $text = cmfTransGeneral($path, $parameters);
        }
        return $text;
    }

    public function renderTemplates(): string
    {
        if (!$this->isSectionAllowed()) {
            abort($this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold')));
        }
        return static::getCmfConfig()->getUiModule()->renderScaffoldTemplates($this);
    }

    /**
     * @return array
     */
    public function renderTemplatesAndSplit() {
        $blocks = [
            'datagrid' => false,
            'itemForm' => false,
            'bulkEditForm' => false,
            'itemDetails' => false,
            'itemFormDefaults' => false
        ];
        if (!$this instanceof KeyValueTableScaffoldConfig && ($this->isCreateAllowed() || $this->isEditAllowed())) {
            $response = $this->getDefaultValuesForFormInputs();
            $blocks['itemFormDefaults'] = $response->getData(true);
        }
        $html = $this->renderTemplates();
        foreach ($blocks as $block => &$template) {
            if (preg_match("%<!--\s*{$block}\s*start\s*-->(?:\s*\n*)*(.*?)<!--\s*{$block}\s*end\s*-->%is", $html, $matches)) {
                $template = trim(preg_replace(
                    ['%^\s*<(div|script)[^>]+id="(data-grid-tpl|item-form-tpl|item-details-tpl|bulk-edit-form-tpl)"[^>]*>\s*(.*)\s*</\1>\s*$%is', '%^\s+%im'],
                    ['$3', ' '],
                    $matches[1]
                ));
            }
        }
        return $blocks;
    }

    public function getHtmlOptionsForFormInputs(): JsonResponse
    {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
        }
        if (!$this->isEditAllowed() && !$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse($this->getFormConfig()->translateGeneral('message.edit.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $columnsOptions = $formConfig->loadOptions($this->getRequest()->query('id'));
        foreach ($columnsOptions as $columnName => $options) {
            if (is_array($options)) {
                $columnsOptions[$columnName] = $this->renderOptionsForSelectInput(
                    $options,
                    $formConfig->getValueViewer($columnName)->getEmptyOptionLabel()
                );
            } else if (!is_string($options)) {
                unset($columnsOptions[$columnName]);
            }
        }
        return new CmfJsonResponse($columnsOptions);
    }

    public function getJsonOptionsForFormInput(string $inputName): JsonResponse
    {
        if (!$this->isSectionAllowed()) {
            return $this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold'));
        }
        if (!$this->isEditAllowed() && !$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse($this->getFormConfig()->translateGeneral('message.edit.forbidden'));
        }
        $formConfig = $this->getFormConfig();
        $options = $formConfig->loadOptionsForInput(
            $inputName,
            $this->getRequest()->query('id'),
            $this->getRequest()->query('keywords')
        );
        return new CmfJsonResponse($options);
    }

    /**
     * @param array $options
     * @param bool|string $addEmptyOption - false: do not add default empty option | string: add default empty option
     * @return string
     */
    protected function renderOptionsForSelectInput(array $options, $addEmptyOption = false) {
        $ret = '';
        $hasEmptyOption = array_key_exists('', $options);
        if ($addEmptyOption !== false || $hasEmptyOption) {
            if ($hasEmptyOption) {
                $label = $options[''];
                unset($options['']);
            } else {
                $label = $addEmptyOption === true ? '' : $addEmptyOption;
            }
            $ret .= '<option value="">' . $label . '</option>';
        }
        foreach ($options as $value => $label) {
            if (!is_array($label)) {
                $ret .= '<option value="' . htmlentities($value) . '">' . $label . '</option>';
            } else {
                $ret .= '<optgroup label="' . htmlentities($value) . '">' . $this->renderOptionsForSelectInput($label) . '</optgroup>';
            }
        }
        return $ret;
    }

    /**
     * @param null|string $message
     * @return CmfJsonResponse
     */
    protected function makeRecordNotFoundResponse($message = null) {
        if (empty($message)) {
            $message = (string)$this->translateGeneral('message.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            routeToCmfItemsTable(static::getResourceName()),
            $message
        );
    }

    /**
     * @param string $message
     * @return CmfJsonResponse
     */
    protected function makeAccessDeniedReponse($message) {
        return cmfJsonResponse(HttpCode::FORBIDDEN)
            ->setMessage($message)
            ->goBack(routeToCmfItemsTable(static::getResourceName()));
    }

    /**
     * @param ScaffoldLoggerInterface $logger
     * @return $this
     */
    public function setLogger(ScaffoldLoggerInterface $logger = null) {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLogger() {
        return $this->logger !== null;
    }

    /**
     * @return null|ScaffoldLoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param RecordInterface $record
     * @param null|string $tableName
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record, ? string $tableName = null) {
        if ($this->hasLogger()) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getLogger()->logDbRecordBeforeChange(
                $record,
                $tableName,
                $this->getLoggableRecordColumns($record),
                $this->getLoggableRecordRelations($record)
            );
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record) {
        if ($this->hasLogger()) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getLogger()->logDbRecordAfterChange(
                $record,
                $this->getLoggableRecordColumns($record),
                $this->getLoggableRecordRelations($record)
            );
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @param null|string $tableName
     * @return $this
     */
    public function logDbRecordLoad(RecordInterface $record, ?string $tableName = null) {
        if ($this->hasLogger()) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getLogger()->logDbRecordUsage($record, $tableName);
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return array
     */
    protected function getLoggableRecordColumns(RecordInterface $record) {
        if (is_array($this->loggableRecordColumns)) {
            $fields = $this->loggableRecordColumns;
        } else if ($record instanceof TempRecord) {
            return null;
        } else {
            $fields = array_keys($record::getTable()->getTableStructure()->getColumns());
        }
        if (is_array($this->notLoggableRecordColumns)) {
            $fields = array_diff($fields, $this->notLoggableRecordColumns);
        }
        if (!$this->logFileColumns) {
            $fields = array_diff($fields, array_keys($record::getTable()->getTableStructure()->getFileColumns()));
        }
        $fields[] = $record::getTable()->getTableStructure()->getPkColumnName();
        return array_unique($fields);
    }

    /**
     * @param RecordInterface $record
     * @return array
     */
    protected function getLoggableRecordRelations(RecordInterface $record) {
        if ($this->loggableRecordRelations === false || $record instanceof TempRecord) {
            return [];
        } else if (is_array($this->loggableRecordRelations)) {
            return $this->loggableRecordRelations;
        } else {
            return array_keys($record::getTable()->getTableStructure()->getRelations());
        }
    }

    public function getCustomData(string $dataId)
    {
        return cmfJsonResponseForHttp404(null, 'Handler [' . static::class . '->getCustomData($dataId)] not defined');
    }

    public function getCustomPage(string $pageName)
    {
        return $this->callMethodByCustomActionOrPageName($pageName, null);
    }
    
    public function performCustomAjaxAction($actionName) {
        return $this->callMethodByCustomActionOrPageName($actionName, null);
    }
    
    public function performCustomDirectAction($actionName) {
        $response = $this->callMethodByCustomActionOrPageName($actionName, null);
        if ($response instanceof JsonResponse) {
            // better late then never
            $this->ajaxOnlyCustomAction();
        }
        return $response;
    }

    public function getCustomPageForRecord(string $itemId, string $pageName)
    {
        return $this->callMethodByCustomActionOrPageName($pageName, $this->getRequestedRecord($itemId));
    }
    
    public function performCustomAjaxActionForRecord(string $itemId, string $actionName) {
        return $this->callMethodByCustomActionOrPageName($actionName, $this->getRequestedRecord($itemId));
    }
    
    public function performCustomDirectActionForRecord($itemId, $actionName) {
        $response = $this->callMethodByCustomActionOrPageName($actionName, $this->getRequestedRecord($itemId));
        if ($response instanceof JsonResponse) {
            // better late then never
            $this->ajaxOnlyCustomAction();
        }
        return $response;
    }
    
    /**
     * Check if request comes via ajax and block non-ajax requests
     * Call this method in ajax-only custom actions methods to prevent non-ajax requests
     */
    protected function ajaxOnlyCustomAction() {
        $middleware = new AjaxOnly();
        $response = $middleware->handle($this->getRequest(), function () {
            return null;
        });
        if ($response !== null) {
            abort($response);
        }
    }
    
    protected function getRequestedRecord($itemId): RecordInterface {
        $record = static::getTable()->newRecord()->fetchByPrimaryKey($itemId);
        if (!$record->existsInDb()) {
            if ($this->getRequest()->ajax()) {
                abort($this->makeRecordNotFoundResponse());
            } else {
                abort(response((string)$this->translateGeneral('message.resource_item_not_found'), HttpCode::NOT_FOUND));
            }
        }
        return $record;
    }
    
    protected function callMethodByCustomActionOrPageName(string $methodName, ?RecordInterface $record = null) {
        $methodName = str_replace('-', '_', $methodName);
        if (method_exists($this, $methodName)) {
            return $record ? $this->$methodName($record) : $this->$methodName();
        }
        $camelCaseMethodName = Str::camel($methodName);
        if (method_exists($this, $camelCaseMethodName)) {
            return $record ? $this->$camelCaseMethodName($record) : $this->$camelCaseMethodName();
        } else {
            $args = $record ? '(' . get_class($record) . ' $record)' : '()';
            $message = 'Method [' . static::class . '->' . $methodName . $args . '] or [' . static::class . '->' . $camelCaseMethodName . $args . '] is not defined';
            if ($this->getRequest()->ajax()) {
                return cmfJsonResponseForHttp404(null, $message);
            } else {
                return view('cmf::ui.default_page_header', [
                    'header' => $message,
                ]);
            }
        }
    }

    public function uploadTempFileForInput(string $inputName): \Illuminate\Http\JsonResponse
    {
        $input = $this->getFormConfig()->getValueViewer($inputName);
        if (method_exists($input, 'uploadTempFile')) {
            return $input->uploadTempFile($this->getRequest());
        }
        return cmfJsonResponse(HttpCode::FORBIDDEN)
            ->setMessage("Input $inputName does not support temp files uploading: method uploadTempFile does not exist");
    }

    public function deleteTempFileForInput(string $inputName): \Illuminate\Http\JsonResponse
    {
        $input = $this->getFormConfig()->getValueViewer($inputName);
        if (method_exists($input, 'deleteTempFile')) {
            return $input->deleteTempFile($this->getRequest());
        }
        return cmfJsonResponse(HttpCode::FORBIDDEN)
            ->setMessage("Input $inputName does not support temp files delete: method deleteTempFile does not exist");
    }

}
