<?php


namespace PeskyCMF\Scaffold;

use App\Db\BaseDbModel;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\DataGrid\DataGridFilterConfig;
use PeskyCMF\Scaffold\Form\FormConfig;
use PeskyCMF\Scaffold\Form\InputRendererConfig;
use PeskyCMF\Scaffold\ItemDetails\DataRendererConfig;
use PeskyCMF\Scaffold\ItemDetails\ItemDetailsConfig;

abstract class ScaffoldSectionConfig {

    /** @var BaseDbModel */
    protected $model;
    /** @var DataGridConfig */
    protected $dataGridConfig = null;
    /** @var DataGridFilterConfig */
    protected $dataGridFilterConfig = null;
    /** @var ItemDetailsConfig */
    protected $itemDetailsConfig = null;
    /** @var FormConfig */
    protected $formConfig = null;

    /** @var bool */
    protected $isItemDetailsAllowed = true;
    /** @var bool */
    protected $isCreateAllowed = true;
    /** @var bool */
    protected $isEditAllowed = true;
    /** @var bool */
    protected $isDeleteAllowed = true;

    /**
     * ScaffoldSectionConfig constructor.
     * @param BaseDbModel $model
     */
    public function __construct(BaseDbModel $model = null) {
        $this->model = empty($model) ? $this->loadModel() : $model;
    }

    /**
     * @throws ScaffoldActionException
     * @return BaseDbModel
     */
    protected function loadModel() {
        throw new ScaffoldActionException(null, 'ScaffoldSectionConfig->loadModel() method not implemented');
    }

    /**
     * @return BaseDbModel
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return string
     * @throws ScaffoldActionException
     */
    public function getConfigs() {
        $configs = ['model' => $this->model];
        $configs['dataGridConfig'] = $this->getDataGridConfig();
        if (!($configs['dataGridConfig'] instanceof DataGridConfig)) {
            throw new ScaffoldActionException(null, 'createDataGridConfig() should return instance of DataGridConfig class');
        }
        $configs['dataGridFilterConfig'] = $this->getDataGridFilterConfig();
        if (!($configs['dataGridFilterConfig'] instanceof DataGridFilterConfig)) {
            throw new ScaffoldActionException(null, 'createDataGridFilterConfig() should return instance of DataGridFilterConfig class');
        }
        $configs['itemDetailsConfig'] = $this->getItemDetailsConfig();
        if (!($configs['itemDetailsConfig'] instanceof ItemDetailsConfig)) {
            throw new ScaffoldActionException(null, 'createItemDetailsConfig() should return instance of ItemDetailsConfig class');
        }
        $configs['formConfig'] = $this->getFormConfig();
        if (!($configs['formConfig'] instanceof FormConfig)) {
            throw new ScaffoldActionException(null, 'createFormConfig() should return instance of FormConfig class');
        }
        return $configs;
    }

    /**
     * @return DataGridConfig
     */
    protected function createDataGridConfig() {
        return DataGridConfig::create($this->model)
            ->setIsCreationAllowed($this->isCreateAllowed)
            ->setIsEditingAllowed($this->isEditAllowed)
            ->setIsDeleteAllowed($this->isDeleteAllowed)
            ->setIsItemDetailsAllowed($this->isItemDetailsAllowed);
    }

    /**
     * @return DataGridFilterConfig
     */
    protected function createDataGridFilterConfig() {
        return DataGridFilterConfig::create($this->model);
    }

    /**
     * @return ItemDetailsConfig
     */
    protected function createItemDetailsConfig() {
        return ItemDetailsConfig::create($this->model)
            ->setIsCreationAllowed($this->isCreateAllowed)
            ->setIsEditingAllowed($this->isEditAllowed)
            ->setIsDeleteAllowed($this->isDeleteAllowed)
            ->setIsItemDetailsAllowed($this->isItemDetailsAllowed)
            ->setDefaultFieldRenderer(function ($field, $actionConfig, array $dataForView) {
                return DataRendererConfig::create('cmf::details/text')->setData($dataForView);
            });
    }

    /**
     * @return FormConfig
     */
    protected function createFormConfig() {
        return FormConfig::create($this->model)
            ->setIsCreationAllowed($this->isCreateAllowed)
            ->setIsEditingAllowed($this->isEditAllowed)
            ->setIsDeleteAllowed($this->isDeleteAllowed)
            ->setIsItemDetailsAllowed($this->isItemDetailsAllowed)
            ->setDefaultFieldRenderer(function () {
                return InputRendererConfig::create('cmf::input/text');
            });
    }

    /**
     * @return DataGridConfig
     */
    public function getDataGridConfig() {
        if (empty($this->dataGridConfig)) {
            $this->dataGridConfig = $this->createDataGridConfig();
        }
        return $this->dataGridConfig;
    }

    /**
     * @return DataGridFilterConfig
     */
    public function getDataGridFilterConfig() {
        if (empty($this->dataGridFilterConfig)) {
            $this->dataGridFilterConfig = $this->createDataGridFilterConfig();
        }
        return $this->dataGridFilterConfig;
    }

    /**
     * @return ItemDetailsConfig
     */
    public function getItemDetailsConfig() {
        if (empty($this->itemDetailsConfig)) {
            $this->itemDetailsConfig = $this->createItemDetailsConfig();
        }
        return $this->itemDetailsConfig;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig() {
        if (empty($this->formConfig)) {
            $this->formConfig = $this->createFormConfig();
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
    public function isItemDetailsAllowed() {
        return $this->isItemDetailsAllowed;
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed() {
        return $this->isDeleteAllowed;
    }


}