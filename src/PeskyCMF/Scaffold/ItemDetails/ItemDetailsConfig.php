<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;

class ItemDetailsConfig extends ScaffoldSectionConfig {

    protected $allowRelationsInValueViewers = true;

    protected $template = 'cmf::scaffold/item_details';

    /** @var array */
    protected $tabs = [];
    /** @var null|int */
    protected $currentTab;
    /** @var array */
    protected $rowsGroups = [];
    /** @var null|int */
    protected $currentRowsGroup;
    /** @var bool */
    protected $showInDialog = true;

    public function getRelationsToRead() {
        $relations = parent::getRelationsToRead();
        foreach ($this->getValueCells() as $valueCell) {
            $addRelations = $valueCell->getAdditionalRelationsToRead();
            foreach ($addRelations as $relationName => $relationColumns) {
                if (is_int($relationName)) {
                    $relationName = $relationColumns;
                    $relationColumns = ['*'];
                }
                if (!array_key_exists($relationName, $relations)) {
                    $relations[$relationName] = $relationColumns;
                }
                $index = array_search($relationName, $relations, true);
                if ($index >= 0) {
                    // remove duplicate
                    unset($relations[$index]);
                }
            }
        }
        return $relations;
    }

    /**
     * @return ValueCell;
     */
    public function createValueViewer() {
        return ValueCell::create();
    }

    /**
     * @return ItemDetailsValueRenderer
     */
    protected function createValueRenderer() {
        return ItemDetailsValueRenderer::create();
    }

    /**
     * Alias for setValueViewers
     * @param array $formInputs
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
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
     * @param string $name
     * @return ValueCell
     * @throws \InvalidArgumentException
     */
    public function getValueCell($name) {
        return $this->getValueViewer($name);
    }

    /**
     * @param array $valueCells
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function setValueViewers(array $valueCells) {
        /** @var AbstractValueViewer|null $config */
        foreach ($valueCells as $name => $config) {
            if (is_array($config)) {
                /** @var array $config */
                $this->newRowsGroup(is_int($name) ? '' : $name);
                foreach ($config as $groupInputName => $groupInputConfig) {
                    if (is_int($groupInputName)) {
                        $groupInputName = $groupInputConfig;
                        $groupInputConfig = null;
                    }
                    $this->addValueViewer($groupInputName, $groupInputConfig);
                }
                $this->currentRowsGroup = null;
            } else {
                if (is_int($name)) {
                    $name = $config;
                    $config = null;
                }
                $this->addValueViewer($name, $config);
            }
        }
        return $this;
    }

    /**
     * @param string $name
     * @param AbstractValueViewer|null $viewer
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function addValueViewer($name, AbstractValueViewer $viewer = null) {
        parent::addValueViewer($name, $viewer);
        if ($this->currentRowsGroup === null) {
            $this->newRowsGroup('');
        }
        $this->rowsGroups[$this->currentRowsGroup]['keys_for_values'][] = $name;
        return $this;
    }

    /**
     * @param string $tabLabel
     * @param array $formInputs
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \InvalidArgumentException
     */
    public function addTab($tabLabel, array $formInputs) {
        $this->newTab($tabLabel);
        $this->setValueCells($formInputs);
        $this->currentTab = null;
        return $this;
    }
    
    /**
     * @param $label
     */
    protected function newRowsGroup($label) {
        if ($this->currentTab === null) {
            $this->newTab('');
        }
        $this->currentRowsGroup = count($this->rowsGroups);
        $this->tabs[$this->currentTab]['groups'][] = $this->currentRowsGroup;
        $this->rowsGroups[] = [
            'label' => $label,
            'keys_for_values' => []
        ];
    }
    
    /**
     * @param $label
     */
    protected function newTab($label) {
        $this->currentTab = count($this->tabs);
        $this->tabs[] = [
            'label' => $label,
            'groups' => []
        ];
        $this->currentRowsGroup = null;
    }
    
    /**
     * @return array
     */
    public function getTabs() {
        return $this->tabs;
    }

    /**
     * @return array
     */
    public function getRowsGroups() {
        return $this->rowsGroups;
    }

    protected function getSectionTranslationsPrefix($subtype = null) {
        return $subtype === 'value_viewer' ? 'item_details.column' : 'item_details';
    }

    /**
     * @param bool $useDialog
     * @return $this
     */
    public function setShowAsDialog($useDialog) {
        $this->showInDialog = (bool)$useDialog;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUsingDialog() {
        return $this->showInDialog;
    }

}