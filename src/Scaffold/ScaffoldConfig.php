<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Contracts\Auth\Access\Gate as AuthGate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PeskyCMF\CmfUrl;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
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

abstract class ScaffoldConfig implements ScaffoldConfigInterface
{
    
    use DataValidationHelper;
    
    protected CmfConfig $cmfConfig;
    protected AuthGate $authGate;
    
    protected ?DataGridConfig $dataGridConfig = null;
    protected ?FilterConfig $dataGridFilterConfig = null;
    protected ?ItemDetailsConfig $itemDetailsConfig = null;
    protected ?FormConfig $formConfig = null;
    
    protected bool $isDetailsViewerAllowed = true;
    protected bool $isCreateAllowed = true;
    protected bool $isEditAllowed = true;
    protected bool $isCloningAllowed = false;
    protected bool $isDeleteAllowed = true;
    /**
     * Path to localization of views.
     * Usage: see $this->getLocalizationBasePath() method.
     * By default if $viewsBaseTranslationKey is empty - static::getResourceName() will be used
     */
    protected ?string $viewsBaseTranslationKey = null;
    
    protected ?ScaffoldLoggerInterface $logger = null;
    
    /**
     * List of record's columns to log on record usage/modification
     */
    protected ?array $loggableRecordColumns = null;
    
    /**
     * List of record's columns that should not be logged on record usage/modification
     */
    protected ?array $notLoggableRecordColumns = null;
    
    /**
     * Should record's file columns be logged on record usage/modification?
     */
    protected bool $logFileColumns = true;
    
    /**
     * List of record's relations and their columns to log together with record's data
     * Note: relation's data will be logged only when it is already loaded (no additional DB queries wil be done)
     * Format: ['Relation1', 'Relation2' => ['column1', 'column2']]; Default: ['*']
     * Use FALSE to disable logging of relations' data
     * @var null|array|false
     */
    protected $loggableRecordRelations = false;
    
    /**
     * @var RecordInterface|Record|CmfDbRecord|null
     */
    private ?RecordInterface $optimizedTableRecord = null;
    
    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
        $this->authGate = $this->cmfConfig->getAuthModule()->getAuthGate();
        if ($this->viewsBaseTranslationKey === null) {
            $this->viewsBaseTranslationKey = static::getResourceName();
        }
        $this->setLogger($this->cmfConfig->getHttpRequestsLogger());
    }
    
    final public function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }
    
    final public function getAuthGate(): AuthGate
    {
        return $this->authGate;
    }
    
    /**
     * @return Authenticatable|CmfAdmin|ResetsPasswordsViaAccessKey|RecordInterface
     */
    final public function getUser(): RecordInterface
    {
        return $this->cmfConfig->getUser();
    }
    
    public static function getResourceName(): string
    {
        return static::getTable()->getName();
    }
    
    public function getMainMenuItem(): ?array
    {
        $resourceName = static::getResourceName();
        if ($this->authGate->denies('resource.view', [$resourceName])) {
            // access to this menu item was denied
            return null;
        }
        return [
            'label' => $this->cmfConfig->transCustom($resourceName . '.menu_title'),
            'icon' => static::getIconForMenuItem(),
            'url' => $this->getUrlToItemsTable(),
            'counter' => static::getMenuItemCounterName(),
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
    
    public static function getMenuItemCounterValue()
    {
        return null;
    }
    
    public function getUrlToItemsTable(
        array $filters = [],
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemsTable(
            static::getResourceName(),
            $filters,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlCustomAction(
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false
    ): string {
        return CmfUrl::toResourceCustomAction(
            static::getResourceName(),
            $actionId,
            $queryArgs,
            $absolute,
            $this->cmfConfig
        );
    }
    
    public function getUrlCustomPage(
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false
    ): string {
        return CmfUrl::toResourceCustomPage(
            static::getResourceName(),
            $pageId,
            $queryArgs,
            $absolute,
            $this->cmfConfig
        );
    }
    
    public function getUrlToItemDetails(
        string $itemId,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemDetails(
            static::getResourceName(),
            $itemId,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlToItemAddForm(
        array $data = [],
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemAddForm(
            static::getResourceName(),
            $data,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlToItemEditForm(
        string $itemId,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemEditForm(
            static::getResourceName(),
            $itemId,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlToItemCloneForm(
        string $itemId,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemCloneForm(
            static::getResourceName(),
            $itemId,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlToItemDelete(
        string $itemId,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemDelete(
            static::getResourceName(),
            $itemId,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlForTempFileUpload(
        string $inputName,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toTempFileUpload(
            static::getResourceName(),
            $inputName,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlForTempFileDelete(
        string $inputName,
        bool $absolute = false,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toTempFileDelete(
            static::getResourceName(),
            $inputName,
            $absolute,
            $this->cmfConfig,
            $ignoreAccessPolicy
        );
    }
    
    public function getUrlToItemCustomAction(
        string $itemId,
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false
    ): string {
        return CmfUrl::toItemCustomAction(
            static::getResourceName(),
            $itemId,
            $actionId,
            $queryArgs,
            $absolute,
            $this->cmfConfig
        );
    }
    
    public function getUrlToItemCustomPage(
        string $itemId,
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false
    ): string {
        return CmfUrl::toItemCustomPage(
            static::getResourceName(),
            $itemId,
            $pageId,
            $queryArgs,
            $absolute,
            $this->cmfConfig
        );
    }
    
    public function getRequest(): Request
    {
        return $this->cmfConfig->getLaravelApp()->make(Request::class);
    }
    
    public function getOptimizedTableRecord(?array $dbDataForRecord = null): RecordInterface
    {
        if (!$this->optimizedTableRecord) {
            $this->optimizedTableRecord = static::getTable()->newRecord()
                ->enableReadOnlyMode()
                ->enableTrustModeForDbData();
        } else {
            $this->optimizedTableRecord->reset();
        }
        if ($dbDataForRecord) {
            $this->optimizedTableRecord->fromDbData($dbDataForRecord);
        }
        return $this->optimizedTableRecord;
    }
    
    public function getConfigsForTemplatesRendering(): array
    {
        $configs = [
            'table' => static::getTable(),
            'scaffoldConfig' => $this,
        ];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        $configs['formConfig'] = $this->getFormConfig();
        $configs['dataGridConfig']->beforeRender();
        $configs['dataGridFilterConfig']->beforeRender();
        $configs['itemDetailsConfig']->beforeRender();
        $configs['formConfig']->beforeRender();
        return $configs;
    }
    
    protected function createDataGridConfig(): DataGridConfig
    {
        return DataGridConfig::create(static::getTable(), $this);
    }
    
    protected function createDataGridFilterConfig(): FilterConfig
    {
        return FilterConfig::create(static::getTable(), $this);
    }
    
    protected function createItemDetailsConfig(): ItemDetailsConfig
    {
        return ItemDetailsConfig::create(static::getTable(), $this);
    }
    
    protected function createFormConfig(): FormConfig
    {
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
    
    public function getItemDetailsConfig(): ItemDetailsConfig
    {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
            $this->itemDetailsConfig->finish();
        }
        return $this->itemDetailsConfig;
    }
    
    public function getFormConfig(): FormConfig
    {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
            $this->formConfig->finish();
        }
        return $this->formConfig;
    }
    
    public function isSectionAllowed(): bool
    {
        return $this->authGate->allows('resource.view', [static::getResourceName()]);
    }
    
    public function isCreateAllowed(): bool
    {
        return (
            $this->isCreateAllowed
            && $this->isSectionAllowed()
            && $this->authGate->allows('resource.create', [static::getResourceName()])
        );
    }
    
    public function isEditAllowed(): bool
    {
        return $this->isEditAllowed && $this->isSectionAllowed();
    }
    
    public function isCloningAllowed(): bool
    {
        return $this->isCloningAllowed && $this->isCreateAllowed();
    }
    
    public function isDetailsViewerAllowed(): bool
    {
        return $this->isDetailsViewerAllowed && $this->isSectionAllowed();
    }
    
    public function isDeleteAllowed(): bool
    {
        return $this->isDeleteAllowed && $this->isSectionAllowed();
    }
    
    /**
     * Detects if $record deletable or not.
     * Used in child classes to add possibility to disable action depending on record data
     */
    public function isRecordDeleteAllowed(array $record): bool
    {
        return $this->isDeleteAllowed() && $this->authGate->allows('resource.delete', [static::getResourceName(), $record]);
    }
    
    /**
     * Detects if $record editable or not.
     * Used in child classes to add possibility to disable action depending on record data
     */
    public function isRecordEditAllowed(array $record): bool
    {
        return $this->isEditAllowed() && $this->authGate->allows('resource.update', [static::getResourceName(), $record]);
    }
    
    /**
     * Detects if $record details can be displayed or not.
     * Used in child classes to add possibility to disable action depending on record data
     */
    public function isRecordDetailsAllowed(array $record): bool
    {
        return (
            $this->isDetailsViewerAllowed()
            && $this->authGate->allows('resource.details', [static::getResourceName(), $record])
        );
    }
    
    /**
     * @param string $section - main sections are: 'datagrid.column', 'item_details.field', 'form.input'
     * @param AbstractValueViewer $viewer
     * @param string $suffix
     * @param array $parameters
     * @return string|array
     */
    public function translateForViewer(string $section, AbstractValueViewer $viewer, string $suffix = '', array $parameters = [])
    {
        return $this->translate($section, rtrim("{$viewer->getNameForTranslation()}_{$suffix}", '_'), $parameters);
    }
    
    /**
     * @param string $section - main sections are: 'form.tooltip'
     * @param string $suffix
     * @param array $parameters
     * @return string|array
     */
    public function translate(string $section, string $suffix = '', array $parameters = [])
    {
        return $this->cmfConfig->transCustom(
            rtrim(".{$this->viewsBaseTranslationKey}.{$section}.{$suffix}", '.'),
            $parameters
        );
    }
    
    /**
     * Translate general UI elements (button labels, tooltips, messages, etc..)
     * @return string|array
     */
    public function translateGeneral(string $path, array $parameters = [])
    {
        $text = $this->translate($path, '', $parameters);
        if (preg_match('%\.' . preg_quote($path, '%') . '$%', $text)) {
            $text = $this->cmfConfig->transGeneral($path, $parameters);
        }
        return $text;
    }
    
    public function renderTemplates(): string
    {
        if (!$this->isSectionAllowed()) {
            abort($this->makeAccessDeniedReponse($this->translateGeneral('message.access_denied_to_scaffold')));
        }
        return $this->cmfConfig->getUiModule()->renderScaffoldTemplates($this);
    }
    
    public function renderTemplatesAndSplit(): array
    {
        $blocks = [
            'datagrid' => false,
            'itemForm' => false,
            'bulkEditForm' => false,
            'itemDetails' => false,
            'itemFormDefaults' => false,
        ];
        if (!$this instanceof KeyValueTableScaffoldConfig && ($this->isCreateAllowed() || $this->isEditAllowed())) {
            $response = $this->getDefaultValuesForFormInputs();
            $blocks['itemFormDefaults'] = $response->getData(true);
        }
        $html = $this->renderTemplates();
        foreach ($blocks as $block => &$template) {
            if (preg_match("%<!--\s*{$block}\s*start\s*-->(?:\s*\n*)*(.*?)<!--\s*{$block}\s*end\s*-->%is", $html, $matches)) {
                $template = trim(
                    preg_replace(
                        [
                            '%^\s*<(div|script)[^>]+id="(data-grid-tpl|item-form-tpl|item-details-tpl|bulk-edit-form-tpl)"[^>]*>\s*(.*)\s*</\1>\s*$%is',
                            '%^\s+%im',
                        ],
                        ['$3', ' '],
                        $matches[1]
                    )
                );
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
            } elseif (!is_string($options)) {
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
     * @param bool|string $addEmptyOption - false: do not add default empty option | true: add | string: empty option label
     * @return string
     */
    protected function renderOptionsForSelectInput(array $options, $addEmptyOption = false): string
    {
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
    
    protected function makeRecordNotFoundResponse(string $message = null): JsonResponse
    {
        if (empty($message)) {
            $message = (string)$this->translateGeneral('message.resource_item_not_found');
        }
        return CmfJsonResponse::create(HttpCode::NOT_FOUND)
            ->setMessage($message)
            ->goBack($this->getUrlToItemsTable());
    }
    
    protected function makeAccessDeniedReponse(string $message): JsonResponse
    {
        return CmfJsonResponse::create(HttpCode::FORBIDDEN)
            ->setMessage($message)
            ->goBack($this->getUrlToItemsTable());
    }
    
    /**
     * @return static
     */
    public function setLogger(ScaffoldLoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
    
    public function hasLogger(): bool
    {
        return $this->logger !== null;
    }
    
    public function getLogger(): ?ScaffoldLoggerInterface
    {
        return $this->logger;
    }
    
    /**
     * @return static
     */
    public function logDbRecordBeforeChange(RecordInterface $record, ?string $tableName = null)
    {
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
     * @return static
     */
    public function logDbRecordAfterChange(RecordInterface $record)
    {
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
     * @return static
     */
    public function logDbRecordLoad(RecordInterface $record, ?string $tableName = null)
    {
        if ($this->hasLogger()) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getLogger()->logDbRecordUsage($record, $tableName);
        }
        return $this;
    }
    
    protected function getLoggableRecordColumns(RecordInterface $record): ?array
    {
        if (is_array($this->loggableRecordColumns)) {
            $fields = $this->loggableRecordColumns;
        } elseif ($record instanceof TempRecord) {
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
    
    protected function getLoggableRecordRelations(RecordInterface $record): array
    {
        if ($this->loggableRecordRelations === false || $record instanceof TempRecord) {
            return [];
        } elseif (is_array($this->loggableRecordRelations)) {
            return $this->loggableRecordRelations;
        } else {
            return array_keys($record::getTable()->getTableStructure()->getRelations());
        }
    }
    
    public function getCustomData(string $dataId)
    {
        return CmfJsonResponse::create(HttpCode::NOT_FOUND)
            ->setMessage('Handler [' . static::class . '->getCustomData($dataId)] not defined')
            ->goBack($this->getUrlToItemsTable());
    }
    
    public function getCustomPage(string $pageName)
    {
        return $this->callMethodByCustomActionOrPageName($pageName, null);
    }
    
    public function performCustomAjaxAction(string $actionName)
    {
        return $this->callMethodByCustomActionOrPageName($actionName, null);
    }
    
    public function performCustomDirectAction(string $actionName)
    {
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
    
    public function performCustomAjaxActionForRecord(string $itemId, string $actionName)
    {
        return $this->callMethodByCustomActionOrPageName($actionName, $this->getRequestedRecord($itemId));
    }
    
    public function performCustomDirectActionForRecord(string $itemId, string $actionName)
    {
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
    protected function ajaxOnlyCustomAction(): void
    {
        $middleware = new AjaxOnly();
        $response = $middleware->handle($this->getRequest(), function () {
            return null;
        });
        if ($response !== null) {
            abort($response);
        }
    }
    
    protected function getRequestedRecord(string $itemId): RecordInterface
    {
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
    
    /**
     * @return Response|string|View
     */
    protected function callMethodByCustomActionOrPageName(string $methodName, ?RecordInterface $record = null)
    {
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
                return CmfJsonResponse::create(HttpCode::NOT_FOUND)
                    ->setMessage($message)
                    ->goBack($this->getUrlToItemsTable());
            } else {
                return view('cmf::ui.default_page_header', [
                    'header' => $message,
                ]);
            }
        }
    }
    
    public function uploadTempFileForInput(string $inputName): JsonResponse
    {
        $input = $this->getFormConfig()->getValueViewer($inputName);
        if (method_exists($input, 'uploadTempFile')) {
            return $input->uploadTempFile($this->getRequest());
        }
        return CmfJsonResponse::create(HttpCode::FORBIDDEN)
            ->setMessage("Input $inputName does not support temp files uploading: method uploadTempFile does not exist");
    }
    
    public function deleteTempFileForInput(string $inputName): JsonResponse
    {
        $input = $this->getFormConfig()->getValueViewer($inputName);
        if (method_exists($input, 'deleteTempFile')) {
            return $input->deleteTempFile($this->getRequest());
        }
        return CmfJsonResponse::create(HttpCode::FORBIDDEN)
            ->setMessage("Input $inputName does not support temp files delete: method deleteTempFile does not exist");
    }
    
    /**
     * Returns FormConfig for editing form or ItemDetailsConfig for details viewer
     * depending on 'details' URL query arg + validates access
     */
    protected function getScaffoldSectionConfigForRecordInfoAndValidateAccess(): ScaffoldSectionConfig
    {
        $isItemDetails = (bool)$this->getRequest()->query('details', false);
        if ($isItemDetails) {
            $sectionConfig = $this->getItemDetailsConfig();
        } else {
            $sectionConfig = $this->getFormConfig();
        }
        if (
            ($isItemDetails && !$this->isDetailsViewerAllowed())
            || (!$isItemDetails && !$this->isEditAllowed())
        ) {
            abort(
                $this->makeAccessDeniedReponse(
                    $sectionConfig->translateGeneral($isItemDetails ? 'message.forbidden' : 'message.edit.forbidden')
                )
            );
        }
        return $sectionConfig;
    }
    
    protected function validateDataForEdit(FormConfig $formConfig, array $data, RecordInterface $record): void
    {
        $errors = $formConfig->validateDataForEdit($data, $record);
        if (count($errors) !== 0) {
            abort($this->makeValidationErrorsJsonResponse($errors));
        }
        if ($formConfig->hasBeforeSaveCallback()) {
            $data = call_user_func($formConfig->getBeforeSaveCallback(), false, $data, $formConfig);
            if (empty($data)) {
                throw new ScaffoldException('Empty $data received from beforeSave callback');
            }
            if ($formConfig->shouldRevalidateDataAfterBeforeSaveCallback(false)) {
                // revalidate
                $errors = $formConfig->validateDataForEdit($data, $record, [], true);
                if (count($errors) !== 0) {
                    abort($this->makeValidationErrorsJsonResponse($errors));
                }
            }
        }
    }
    
    protected function runAfterSaveCallback(
        FormConfig $formConfig,
        bool $isCreated,
        array $data,
        RecordInterface $record
    ): void {
        if ($formConfig->hasAfterSaveCallback()) {
            $table = static::getTable();
            $success = call_user_func($formConfig->getAfterSaveCallback(), $isCreated, $data, $record, $formConfig);
            if ($success instanceof JsonResponse) {
                if ($success->getStatusCode() < 400) {
                    $table::commitTransaction();
                } else {
                    $table::rollBackTransaction();
                }
                abort($success);
            } elseif ($success !== true) {
                $table::rollBackTransaction();
                throw new ScaffoldException(
                    'afterSaveCallback must return true or instance of \Illuminate\Http\JsonResponse'
                );
            }
        }
    }
    
    protected function runBulkEditDataAfterSaveCallback(FormConfig $formConfig, array $data): void
    {
        if ($formConfig->hasBulkEditAfterSaveCallback()) {
            $table = static::getTable();
            $success = call_user_func($formConfig->getBulkEditAfterSaveCallback(), $data);
            if ($success instanceof JsonResponse) {
                if ($success->getStatusCode() < 400) {
                    $table::commitTransaction();
                } else {
                    $table::rollBackTransaction();
                }
                abort($success);
            } elseif ($success !== true) {
                $table::rollBackTransaction();
                throw new ScaffoldException(
                    'bulkEditAfterSaveCallback must return true or instance of \Illuminate\Http\JsonResponse'
                );
            }
        }
    }
    
}
