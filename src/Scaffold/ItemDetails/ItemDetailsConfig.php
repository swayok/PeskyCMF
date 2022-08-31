<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\AbstractValueViewer;
use PeskyCMF\Scaffold\ScaffoldSectionConfig;

class ItemDetailsConfig extends ScaffoldSectionConfig
{
    
    protected bool $allowRelationsInValueViewers = true;
    
    protected bool $allowComplexValueViewerNames = true;
    
    protected string $template = 'cmf::scaffold.item_details';
    
    protected array $tabs = [];
    protected ?int $currentTab = null;
    protected array $rowsGroups = [];
    protected ?int $currentRowsGroup = null;
    
    public function getRelationsToRead(): array
    {
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
    
    public function createValueViewer(): ValueCell
    {
        return ValueCell::create();
    }
    
    protected function createValueRenderer(): ItemDetailsValueRenderer
    {
        return ItemDetailsValueRenderer::create();
    }
    
    /**
     * Alias for setValueViewers
     * @param ValueCell[] $valueCells
     * @return static
     */
    public function setValueCells(array $valueCells)
    {
        return $this->setValueViewers($valueCells);
    }
    
    /**
     * @return ValueCell[]|AbstractValueViewer[]
     */
    public function getValueCells(): array
    {
        return $this->getValueViewers();
    }
    
    public function hasValueCell(string $name): bool
    {
        return $this->hasValueViewer($name);
    }
    
    public function getValueCell(string $name): ValueCell
    {
        return $this->getValueViewer($name);
    }
    
    /**
     * @param ValueCell[] $valueCells
     * @return static
     */
    public function setValueViewers(array $valueCells)
    {
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
                $this->normalizeAndAddValueViewer($name, $config);
            }
        }
        return $this;
    }
    
    /**
     * @param string $name
     * @param AbstractValueViewer|null $viewer
     * @param bool $autodetectIfLinkedToDbColumn
     * @return static
     */
    public function addValueViewer(
        string $name,
        AbstractValueViewer &$viewer = null,
        bool $autodetectIfLinkedToDbColumn = false
    ) {
        parent::addValueViewer($name, $viewer, $autodetectIfLinkedToDbColumn);
        if ($this->currentRowsGroup === null) {
            $this->newRowsGroup('');
        }
        $this->rowsGroups[$this->currentRowsGroup]['keys_for_values'][] = $name;
        return $this;
    }
    
    /**
     * @return static
     */
    public function addTab(string $tabLabel, array $formInputs)
    {
        $this->newTab($tabLabel);
        $this->setValueCells($formInputs);
        $this->currentTab = null;
        return $this;
    }
    
    protected function newRowsGroup(string $label): void
    {
        if ($this->currentTab === null) {
            $this->newTab('');
        }
        $this->currentRowsGroup = count($this->rowsGroups);
        $this->tabs[$this->currentTab]['groups'][] = $this->currentRowsGroup;
        $this->rowsGroups[] = [
            'label' => $label,
            'keys_for_values' => [],
        ];
    }
    
    protected function newTab(string $label): void
    {
        $this->currentTab = count($this->tabs);
        $this->tabs[] = [
            'label' => $label,
            'groups' => [],
        ];
        $this->currentRowsGroup = null;
    }
    
    public function getTabs(): array
    {
        return $this->tabs;
    }
    
    public function getRowsGroups(): array
    {
        return $this->rowsGroups;
    }
    
    protected function getSectionTranslationsPrefix(?string $subtype = null): string
    {
        return $subtype === 'value_viewer' ? 'item_details.field' : 'item_details';
    }
    
}
