<?php

namespace PeskyCMF\Scaffold\DataGrid;

use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;

class DataGridColumn extends RenderableValueViewer {

    /**
     * @var bool
     */
    protected $isSortable = true;

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var null|int
     */
    protected $columnWidth = null;
    /**
     * @var array
     */
    protected $additionalOrderBy = [];

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
     * @param string $width - 100, 100px, 25%. No units means that width is in pixels: 100 == 100px
     * @return $this
     */
    public function setWidth($width) {
        if (!preg_match('%^\d+\s*(px|\%|)$%i', $width)) {
            throw new \InvalidArgumentException('$width argument must be in pixels (ex: 100px or 100) or percents (ex: 25%)');
        }
        $this->columnWidth = is_numeric($width) ? $width . 'px' : $width;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCustomWidth() {
        return !empty($this->columnWidth);
    }

    /**
     * @return int|null
     */
    public function getWidth() {
        return $this->columnWidth;
    }

    /**
     * @return \Closure|null
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function getValueConverter() {
        if (empty($this->valueConverter)) {
            switch ($this->getType()) {
                case self::TYPE_BOOL:
                    $this->valueConverter = function ($value) {
                        if (!$this->isLinkedToDbColumn()) {
                            if (!array_has($value, $this->getName())) {
                                return '-';
                            } else {
                                $value = (bool)$value[$this->getName()];
                            }
                        }
                        return cmfTransGeneral('.datagrid.field.bool.' . ($value ? 'yes' : 'no'));
                    };
                    break;
            }
        }
        return $this->valueConverter;
    }

    public function setIsLinkedToDbColumn($isDbColumn) {
        if (!$isDbColumn) {
            $this->setIsSortable(false);
        }
        return parent::setIsLinkedToDbColumn($isDbColumn);
    }

    /**
     * Add additional sorting to ORDER BY when user sorts by this column
     * @param $column
     * @param $isAscending
     * @return $this
     */
    public function addAdditionalOrderBy($column, $isAscending) {
        $this->additionalOrderBy[$column] = $isAscending ? 'ASC' : 'DESC';
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalOrderBy() {
        return $this->additionalOrderBy;
    }

    /**
     * @return int
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     */
    public function getPosition() {
        if ($this->getName() === DataGridConfig::ROW_ACTIONS_COLUMN_NAME && $this->getScaffoldSectionConfig()->isRowActionsColumnFixed()) {
            return (
                ($this->getScaffoldSectionConfig()->isAllowedMultiRowSelection() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isNestedViewEnabled() ? 1 : 0)
            );
        } else {
            return (int)$this->position
                + ($this->getScaffoldSectionConfig()->isAllowedMultiRowSelection() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isRowActionsColumnFixed() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isNestedViewEnabled() ? 1 : 0);
        }
    }

    public function configureDefaultRenderer(ValueRenderer $renderer) {
        parent::configureDefaultRenderer($renderer);
        if (!$renderer->hasTemplate()) {
            if ($this->getType() === static::TYPE_IMAGE) {
                $renderer->setTemplate('cmf::datagrid.image');
            } else {
                $renderer->setTemplate('cmf::datagrid.text');
            }
        }
        return $this;
    }

}