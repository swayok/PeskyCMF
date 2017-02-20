<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;
use PeskyCMF\Scaffold\ValueRenderer;

class ItemDetailsConfig extends ScaffoldSectionConfig {

    protected $allowRelationsInValueViewers = true;

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
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
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
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \UnexpectedValueException
     */
    protected function configureDefaultValueRenderer(ValueRenderer $renderer, AbstractValueViewer $valueCell) {
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