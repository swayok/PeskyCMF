<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldFieldConfig;

class DataGridFieldConfig extends ScaffoldFieldConfig {

    /**
     * @var bool
     */
    protected $isSortable = true;

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @return boolean
     */
    public function isSortable() {
        return $this->isSortable;
    }

    /**
     * @param boolean $isSortable
     * @return $this
     */
    public function setIsSortable($isSortable) {
        $this->isSortable = $isSortable;
        return $this;
    }

    /**
     * @return $this
     */
    public function enableSorting() {
        $this->isSortable = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableSorting() {
        $this->isSortable = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVisible() {
        return $this->isVisible;
    }

    /**
     * @param boolean $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible) {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * @return $this
     */
    public function invisible() {
        $this->isVisible = false;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getValueConverter() {
        if (empty($this->valueConverter)) {
            switch ($this->getType()) {
                case self::TYPE_BOOL:
                    return function ($value) {
                        if (!$this->isDbField()) {
                            if (!array_has($value, $this->getName())) {
                                return '-';
                            } else {
                                $value = (bool)$value[$this->getName()];
                            }
                        }
                        return cmfTransGeneral('.datagrid.field.bool.' . ($value ? 'yes' : 'no'));
                    };
            }
        }
        return $this->valueConverter;
    }

    public function setIsDbField($isDbField) {
        if (!$isDbField) {
            $this->setIsSortable(false);
        }
        return parent::setIsDbField($isDbField);
    }

    /**
     * @return int
     */
    public function getPosition() {
        if ($this->getName() === DataGridConfig::ROW_ACTIONS_COLUMN_NAME && $this->getScaffoldActionConfig()->isRowActionsColumnFixed()) {
            return $this->getScaffoldActionConfig()->isAllowedMultiRowSelection() ? 1 : 0;
        } else {
            return (int)$this->position
                + ($this->getScaffoldActionConfig()->isAllowedMultiRowSelection() ? 1 : 0)
                + ($this->getScaffoldActionConfig()->isRowActionsColumnFixed() ? 1 : 0);
        }
    }

}