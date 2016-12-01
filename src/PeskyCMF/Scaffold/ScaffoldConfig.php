<?php


namespace PeskyCMF\Scaffold;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbTable;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\FilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;

abstract class ScaffoldConfig {

    /** @var CmfDbTable */
    protected $model;
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
     * @param CmfDbTable $model
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     */
    public function __construct(CmfDbTable $model) {
        $this->model = $model;
    }

    /**
     * @return CmfDbTable
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return array
     * @throws ScaffoldSectionException
     */
    public function getConfigs() {
        $configs = [
            'model' => $this->getModel(),
            'scaffoldConfig' => $this
        ];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        if (!($configs['dataGridConfig'] instanceof DataGridConfig)) {
            throw new ScaffoldSectionException(null, 'createDataGridConfig() should return instance of DataGridConfig class');
        }
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        if (!($configs['dataGridFilterConfig'] instanceof FilterConfig)) {
            throw new ScaffoldSectionException(null, 'createDataGridFilterConfig() should return instance of FilterConfig class');
        }
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        if (!($configs['itemDetailsConfig'] instanceof ItemDetailsConfig)) {
            throw new ScaffoldSectionException(null, 'createItemDetailsConfig() should return instance of ItemDetailsConfig class');
        }
        $configs['formConfig'] = $this->getFormConfig();
        if (!($configs['formConfig'] instanceof FormConfig)) {
            throw new ScaffoldSectionException(null, 'createFormConfig() should return instance of FormConfig class');
        }
        return $configs;
    }

    /**
     * @return DataGridConfig
     */
    protected function createDataGridConfig() {
        return DataGridConfig::create($this->getModel(), $this);
    }

    /**
     * @return FilterConfig
     */
    protected function createDataGridFilterConfig() {
        return FilterConfig::create($this->getModel());
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create($this->getModel(), $this);
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create($this->getModel(), $this);
    }

    /**
     * @return DataGridConfig
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

}