<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldFieldConfig;
use PeskyCMF\Scaffold\ScaffoldFieldRenderer;

class ItemDetailsConfig extends ScaffoldActionConfig {

    protected $view = 'cmf::scaffold/item_details';

    /**
     * @inheritdoc
     */
    public function createFieldConfig() {
        return ValueCell::create();
    }

    protected function createFieldRendererConfig() {
        return ValueCellRenderer::create();
    }

    /**
     * @param ScaffoldFieldRenderer|ValueCellRenderer $rendererConfig
     * @param ScaffoldFieldConfig|ValueCell $fieldConfig
     */
    protected function configureDefaultRenderer(
        ScaffoldFieldRenderer $rendererConfig,
        ScaffoldFieldConfig $fieldConfig
    ) {
        switch ($fieldConfig->getType()) {
            case $fieldConfig::TYPE_IMAGE:
                $rendererConfig->setView('cmf::details.image');
                break;
            case $fieldConfig::TYPE_BOOL:
                $rendererConfig->setView('cmf::details.bool');
                break;
            case $fieldConfig::TYPE_JSON_TREE:
                $rendererConfig->setView('cmf::details.json_tree');
                break;
            default:
                $rendererConfig->setView('cmf::details.text');
        }
    }

}