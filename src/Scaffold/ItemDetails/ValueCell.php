<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\ItemDetails;

use Illuminate\Support\Str;
use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\Column;

class ValueCell extends RenderableValueViewer
{
    
    public const TYPE_JSON_TREE = 'json_collapsed';
    public const TYPE_HTML = 'html';
    
    protected array $valueContainerAttributes = [];
    
    /**
     * Returns list of additional relations to read
     * Designed to be used in custom ValueCells
     */
    public function getAdditionalRelationsToRead(): array
    {
        return [];
    }
    
    public function getValueConverter(): ?\Closure
    {
        if (empty(parent::getValueConverter()) && $this->getType() === static::TYPE_IMAGE) {
            $this->setValueConverter(function ($value, Column $columnConfig) {
                if (!empty($value) && is_array($value) && !empty($value['url']) && is_array($value['url'])) {
                    if (count($value['url']) > 0) {
                        unset($value['url']['source']);
                    }
                    $images = [];
                    $baseTranslationPath = $columnConfig->getTableStructure()->getTableName()
                        . '.item_details.field.' . $columnConfig->getName() . '_version.';
                    foreach ($value['url'] as $key => $url) {
                        $images[] = [
                            'label' => $this->getCmfConfig()->transCustom($baseTranslationPath . $key),
                            'url' => $url,
                        ];
                    }
                    return $images;
                } else {
                    return [];
                }
            });
        }
        return parent::getValueConverter();
    }
    
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record): mixed
    {
        switch ($type) {
            case static::TYPE_TEXT:
                return '<div class="multiline-text">' . parent::doDefaultValueConversionByType($value, $type, $record) . '</div>';
            case static::TYPE_JSON_TREE:
                if (!is_array($value) && $value !== null) {
                    if (is_string($value) || is_numeric($value) || is_bool($value)) {
                        $value = json_decode($value, true);
                        if ($value === null) {
                            $value = 'Failed to decode JSON: ' . print_r($value, true);
                        }
                    } else {
                        $value = 'Invalid value for JSON: ' . print_r($value, true);
                    }
                }
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            default:
                return parent::doDefaultValueConversionByType($value, $type, $record);
        }
    }
    
    public function configureDefaultRenderer(ValueRenderer $renderer): static
    {
        parent::configureDefaultRenderer($renderer);
        if (!$renderer->hasTemplate()) {
            switch ($this->getType()) {
                case static::TYPE_IMAGE:
                    $renderer->setTemplate('cmf::item_details.image');
                    break;
                case static::TYPE_BOOL:
                    $renderer->setTemplate('cmf::item_details.bool');
                    break;
                case static::TYPE_JSON_TREE:
                    $renderer->setTemplate('cmf::item_details.json_tree');
                    break;
                case static::TYPE_HTML:
                    $renderer->setTemplate('cmf::item_details.html');
                    break;
                default:
                    $renderer->setTemplate('cmf::item_details.text');
            }
        }
        return $this;
    }
    
    /**
     * Add custom attributes to HTML element where record's value will be displayed
     */
    public function addAttributesToValueContainer(array $attributes): static
    {
        $this->valueContainerAttributes = $attributes;
        return $this;
    }
    
    public function getValueContainerAttributes(): array
    {
        return $this->valueContainerAttributes;
    }
    
    /**
     * Hide value's label with its container
     */
    public function hideLabel(): static
    {
        return $this->setLabel('');
    }
    
    public function getHtmlElementId(): string
    {
        return Str::slug($this->getScaffoldSectionConfig()->getScaffoldConfig()->getResourceName() . '-value-viewer-' . $this->getName());
    }
    
}