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
     * By default if $localizationKey is empty - cmf::scaffold.templates view
     * will call $this->getLocalizationBasePath($tableNameForRoutes)
     * @return null|string
     */
    protected $viewsLocalizationKey = null;

    /**
     * ScaffoldConfig constructor.
     * @param TableInterface $table
     * @param string $tableNameForRoutes - table name to be used to build routes to resources of the $table
     */
    public function __construct(TableInterface $table, $tableNameForRoutes) {
        $this->table = $table;
        $this->tableNameForRoutes = $tableNameForRoutes;
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
            'model' => $this->getTable(),
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
        return FilterConfig::create($this->getTable());
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
     * Base path to localization of scaffold views for this resource
     * @param $defaultLocalizationKey
     * @return string
     */
    public function getLocalizationBasePath($defaultLocalizationKey) {
        $key = $this->viewsLocalizationKey ?: $defaultLocalizationKey;
        return '.' . $key;
    }

    public function renderTemplates() {
        return view(
            CmfConfig::getInstance()->scaffold_templates_view_for_normal_table(),
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
        if ($addEmptyOption !== false || array_key_exists('', $options)) {
            $ret .= '<option value="">' . (array_key_exists('', $options) ? $options[''] : $addEmptyOption) . '</option>';
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

    abstract public function getRecordsForDataGrid();

    abstract public function getRecordValues($id = null);

    abstract public function getDefaultValuesForFormInputs();

    abstract public function addRecord();

    abstract public function updateRecord();

    abstract public function updateBulkOfRecords();

    abstract public function deleteRecord($id);

    abstract public function deleteBulkOfRecords();

}