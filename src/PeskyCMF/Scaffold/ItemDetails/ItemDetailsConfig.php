<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\ScaffoldActionConfig;

class ItemDetailsConfig extends ScaffoldActionConfig {

    protected $view = 'cmf::scaffold/item_details';

    /**
     * @inheritdoc
     */
    static public function createFieldConfig($fieldName) {
        return ItemDetailsFieldConfig::create();
    }

    /**
     * @return callable|\Closure
     */
    public function getDefaultFieldRenderer() {
        if (!empty($this->defaultFieldRenderer)) {
            return $this->defaultFieldRenderer;
        } else {
            return function ($fieldConfig, $actionConfig, array $dataForView) {
                return $this->_getDefaultFieldRendererConfig($fieldConfig, $actionConfig, $dataForView);
            };
        }
    }

    /**
     * @param ItemDetailsFieldConfig $fieldConfig
     * @param ItemDetailsConfig $actionConfig
     * @param array $dataForView
     * @return $this
     */
    protected function _getDefaultFieldRendererConfig(
        ItemDetailsFieldConfig $fieldConfig,
        ItemDetailsConfig $actionConfig,
        array $dataForView
    ) {
        $rendererConfig = DataRendererConfig::create('cmf::details/text')->setData($dataForView);
        $fieldConfig->configureDefaultRenderer($rendererConfig);
        return $rendererConfig;
    }

}