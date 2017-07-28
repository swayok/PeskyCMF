<?php


namespace PeskyCMF\Scaffold;

use Illuminate\Http\Request;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\TableInterface;

abstract class ScaffoldConfig {

    use DataValidationHelper;

    /** @var TableInterface */
    protected $table;
    /** @var string */
    protected $tableNameForRoutes;
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
    protected $isDeleteAllowed = true;
    /**
     * Path to localization of views.
     * Usage: see $this->getLocalizationBasePath() method.
     * By default if $viewsBaseTranslationKey is empty - $this->tableNameForRoutes will be used
     * @return null|string
     */
    protected $viewsBaseTranslationKey = null;

    /**
     * @var null|ScaffoldLoggerInterface
     */
    protected $logger;

    /**
     * ScaffoldConfig constructor.
     * @param TableInterface $table
     * @param string $tableNameForRoutes - table name to be used to build routes to resources of the $table
     */
    public function __construct(TableInterface $table, $tableNameForRoutes) {
        $this->table = $table;
        $this->tableNameForRoutes = $tableNameForRoutes;
        if ($this->viewsBaseTranslationKey === null) {
            $this->viewsBaseTranslationKey = $tableNameForRoutes;
        }
        $this->setLogger(CmfConfig::getInstance()->scaffold_requests_logger());
    }

    /**
     * @return TableInterface
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getTableNameForRoutes() {
        return $this->tableNameForRoutes;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return request();
    }

    /**
     * @return array
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws ScaffoldSectionConfigException
     */
    public function getConfigsForTemplatesRendering() {
        $configs = [
            'table' => $this->getTable(),
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
        return DataGridConfig::create($this->getTable(), $this);
    }

    /**
     * @return FilterConfig
     */
    protected function createDataGridFilterConfig() {
        return FilterConfig::create($this->getTable(), $this);
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create($this->getTable(), $this);
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create($this->getTable(), $this);
    }

    /**
     * @return DataGridConfig
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function getDataGridConfig() {
        if (empty($this->dataGridConfig)) {
            $this->dataGridConfig = $this->createDataGridConfig();
            $this->dataGridConfig->finish();
        }
        return $this->dataGridConfig;
    }

    /**
     * @return FilterConfig
     */
    public function getDataGridFilterConfig() {
        if (empty($this->dataGridFilterConfig)) {
            $this->dataGridFilterConfig = $this->createDataGridFilterConfig();
            $this->dataGridFilterConfig->finish();
        }
        return $this->dataGridFilterConfig;
    }

    /**
     * @return ItemDetailsConfig
     */
    public function getItemDetailsConfig() {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
            $this->itemDetailsConfig->finish();
        }
        return $this->itemDetailsConfig;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig() {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
            $this->formConfig->finish();
        }
        return $this->formConfig;
    }

    /**
     * @return boolean
     */
    public function isCreateAllowed() {
        return $this->isCreateAllowed;
    }

    /**
     * @return boolean
     */
    public function isEditAllowed() {
        return $this->isEditAllowed;
    }

    /**
     * @return boolean
     */
    public function isDetailsViewerAllowed() {
        return $this->isDetailsViewerAllowed;
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->isDeleteAllowed;
    }

    /**
     * Detects if $record deletable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDeleteAllowed(array $record) {
        return $this->isDeleteAllowed();
    }

    /**
     * Detects if $record editable or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordEditAllowed(array $record) {
        return $this->isEditAllowed();
    }

    /**
     * Detects if $record details can be displayed or not.
     * Used in child classes to add possibility to disable action depending on record data
     * @param array $record
     * @return bool
     */
    public function isRecordDetailsAllowed(array $record) {
        return $this->isDetailsViewerAllowed();
    }

    /**
     * @param string $section - main sections are: 'datagrid.column', 'item_details.field', 'form.input'
     * @param AbstractValueViewer $viewer
     * @param string $suffix
     * @param array $parameters
     * @return string
     */
    public function translateForViewer($section, AbstractValueViewer $viewer, $suffix = '', array $parameters = []) {
        return $this->translate($section, rtrim("{$viewer->getNameForTranslation()}_{$suffix}", '_'), $parameters);
    }

    /**
     * @param string $section - main sections are: 'form.tooltip'
     * @param string $suffix
     * @param array $parameters
     * @return array|string
     */
    public function translate($section, $suffix = '', array $parameters = []) {
        return cmfTransCustom(rtrim(".{$this->viewsBaseTranslationKey}.{$section}.{$suffix}", '.'), $parameters);
    }

    public function renderTemplates() {
        return view(
            CmfConfig::getPrimary()->scaffold_templates_view_for_normal_table(),
            array_merge(
                $this->getConfigsForTemplatesRendering(),
                ['tableNameForRoutes' => $this->getTableNameForRoutes()]
            )
        )->render();
    }

    public function getHtmlOptionsForFormInputs() {
        if (!$this->isEditAllowed() && !$this->isCreateAllowed()) {
            return $this->makeAccessDeniedReponse(cmfTransGeneral('.action.edit.forbidden'));
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
        return cmfJsonResponse()->setData($columnsOptions);
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
     * @param TableInterface $table
     * @param null|string $message
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    protected function makeRecordNotFoundResponse(TableInterface $table, $message = null) {
        if (empty($message)) {
            $message = cmfTransGeneral('.error.resource_item_not_found');
        }
        return cmfJsonResponseForHttp404(
            routeToCmfItemsTable($this->getTableNameForRoutes()),
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
            ->goBack(routeToCmfItemsTable($this->getTableNameForRoutes()));
    }

    /**
     * @param ScaffoldLoggerInterface $logger
     * @return $this
     */
    public function setLogger(ScaffoldLoggerInterface $logger) {
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
     * @return $this
     */
    public function logDbRecordBeforeChange(RecordInterface $record) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordBeforeChange($record);
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordAfterChange(RecordInterface $record) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordAfterChange($record);
        }
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @return $this
     */
    public function logDbRecordLoad(RecordInterface $record) {
        if ($this->hasLogger()) {
            $this->getLogger()->logDbRecordUsage($record);
        }
        return $this;
    }

    abstract public function getRecordsForDataGrid();

    abstract public function getRecordValues($id = null);

    abstract public function getDefaultValuesForFormInputs();

    abstract public function addRecord();

    abstract public function updateRecord();

    abstract public function updateBulkOfRecords();

    abstract public function deleteRecord($id);

    abstract public function deleteBulkOfRecords();

    public function getCustomData($dataId) {
        return cmfJsonResponse(HttpCode::NOT_FOUND);
    }

}