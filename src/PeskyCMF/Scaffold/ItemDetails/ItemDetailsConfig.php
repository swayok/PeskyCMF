<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldFieldConfig;
use PeskyCMF\Scaffold\ScaffoldFieldRendererConfig;

class ItemDetailsConfig extends ScaffoldActionConfig {

    protected $view = 'cmf::scaffold/item_details';

    /**
     * @inheritdoc
     */
    public function createFieldConfig() {
        return ItemDetailsFieldConfig::create();
    }

    protected function createFieldRendererConfig() {
        return DataRendererConfig::create();
    }

    /**
     * @param ScaffoldFieldRendererConfig|DataRendererConfig $rendererConfig
     * @param ScaffoldFieldConfig|ItemDetailsFieldConfig $fieldConfig
     */
    protected function configureDefaultRenderer(
        ScaffoldFieldRendererConfig $rendererConfig,
        ScaffoldFieldConfig $fieldConfig
    ) {
        $rendererConfig->setView('cmf::details/text');
    }

}