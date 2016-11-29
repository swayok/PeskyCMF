<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;

class ItemDetailsConfig extends ScaffoldActionConfig {

    protected $template = 'cmf::scaffold/item_details';

    /**
     * @inheritdoc
     */
    public function createValueViewer() {
        return ValueCell::create();
    }

    protected function createValueRenderer() {
        return ValueCellRenderer::create();
    }

    /**
     * Alias for setValueViewers
     * @param array $formInputs
     * @return $this
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     */
    public function setValueCells(array $formInputs) {
        return $this->setValueViewers($formInputs);
    }

    /**
     * @return ValueCell[]|AbstractValueViewer[]
     */
    public function getValueCells() {
        return $this->getValueViewers();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasValueCell($name) {
        return $this->hasValueViewer($name);
    }

    /**
     * @param ValueRenderer|ValueCellRenderer $renderer
     * @param AbstractValueViewer|ValueCell $valueCell
     */
    protected function configureDefaultValueRenderer(
        ValueRenderer $renderer,
        AbstractValueViewer $valueCell
    ) {
        switch ($valueCell->getType()) {
            case $valueCell::TYPE_IMAGE:
                $renderer->setTemplate('cmf::details.image');
                break;
            case $valueCell::TYPE_BOOL:
                $renderer->setTemplate('cmf::details.bool');
                break;
            case $valueCell::TYPE_JSON_TREE:
                $renderer->setTemplate('cmf::details.json_tree');
                break;
            default:
                $renderer->setTemplate('cmf::details.text');
        }
    }

}