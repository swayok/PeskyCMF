<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\DataGrid;

use Illuminate\Support\Arr;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;

class DataGridColumn extends RenderableValueViewer
{
    
    protected bool $isSortable = true;
    protected bool $isVisible = true;
    protected ?string $columnWidth = null;
    protected array $additionalOrderBy = [];
    
    public static function convertNameForDataTables(string $name): string
    {
        return str_replace('.', ':', $name);
    }
    
    public function isSortable(): bool
    {
        return $this->isSortable;
    }
    
    public function setIsSortable(bool $isSortable): static
    {
        $this->isSortable = $isSortable;
        return $this;
    }
    
    public function enableSorting(): static
    {
        $this->isSortable = true;
        return $this;
    }
    
    public function disableSorting(): static
    {
        $this->isSortable = false;
        return $this;
    }
    
    public function isVisible(): bool
    {
        return $this->isVisible;
    }
    
    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;
        return $this;
    }
    
    public function invisible(): static
    {
        $this->isVisible = false;
        return $this;
    }
    
    public function setType(string $type): static
    {
        switch ($type) {
            case static::TYPE_TEXT:
            case static::TYPE_MULTILINE:
            case static::TYPE_JSON:
            case static::TYPE_JSONB:
            case static::TYPE_LINK:
            case static::TYPE_IMAGE:
                $this->setIsSortable(false);
                break;
        }
        return parent::setType($type);
    }
    
    /**
     * @param string|int $width - 100, 100px, 25%. No units means that width is in pixels: 100 == 100px
     * @throws \InvalidArgumentException
     */
    public function setWidth(string|int $width): static
    {
        if (!is_int($width) && !preg_match('%^\d+\s*(px|\%|)$%i', $width)) {
            throw new \InvalidArgumentException('$width argument must be in pixels (ex: 100px or 100) or percents (ex: 25%)');
        }
        $this->columnWidth = is_int($width) || preg_match('%^\d+$%', $width) ? $width . 'px' : $width;
        return $this;
    }
    
    public function hasCustomWidth(): bool
    {
        return !empty($this->columnWidth);
    }
    
    public function getWidth(): ?string
    {
        return $this->columnWidth;
    }
    
    public function getValueConverter(): ?\Closure
    {
        if (empty($this->valueConverter) && $this->getType() === self::TYPE_BOOL) {
            $this->valueConverter = function ($value) {
                if (!$this->isLinkedToDbColumn()) {
                    if (!Arr::has($value, $this->getName())) {
                        return '-';
                    } else {
                        $value = (bool)$value[$this->getName()];
                    }
                }
                return $this->getCmfConfig()->transGeneral('datagrid.field.bool.' . ($value ? 'yes' : 'no'));
            };
        }
        return $this->valueConverter;
    }
    
    public function setIsLinkedToDbColumn(bool $isDbColumn): static
    {
        if (!$isDbColumn) {
            $this->setIsSortable(false);
        }
        return parent::setIsLinkedToDbColumn($isDbColumn);
    }
    
    /**
     * Add additional sorting to ORDER BY when user sorts by this column
     */
    public function addAdditionalOrderBy(string $column, bool $isAscending): static
    {
        $this->additionalOrderBy[$column] = $isAscending ? 'ASC' : 'DESC';
        return $this;
    }
    
    public function getAdditionalOrderBy(): array
    {
        return $this->additionalOrderBy;
    }
    
    public function getPosition(): int
    {
        if (
            $this->getName() === DataGridConfig::ROW_ACTIONS_COLUMN_NAME
            && $this->getScaffoldSectionConfig()->isRowActionsEnabled()
            && $this->getScaffoldSectionConfig()->isRowActionsColumnFixed()
        ) {
            return (
                ($this->getScaffoldSectionConfig()->isAllowedMultiRowSelection() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isNestedViewEnabled() ? 1 : 0)
            );
        } else {
            return (int)$this->position
                + ($this->getScaffoldSectionConfig()->isAllowedMultiRowSelection() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isRowActionsEnabled() && $this->getScaffoldSectionConfig()->isRowActionsColumnFixed() ? 1 : 0)
                + ($this->getScaffoldSectionConfig()->isNestedViewEnabled() ? 1 : 0);
        }
    }
    
    public function configureDefaultRenderer(ValueRenderer $renderer): static
    {
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
