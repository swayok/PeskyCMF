<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyCMF\Scaffold\ValueRenderer;
use PeskyORM\ORM\Column;

class ValueCell extends RenderableValueViewer {

    const TYPE_JSON_TREE = 'json_collapsed';
    const TYPE_HTML = 'html';
    /** @var array  */
    protected $valueContainerAttributes = [];

    /**
     * Returns list of additional relations to read
     * Designed to be used in custom ValueCells
     * @return array
     */
    public function getAdditionalRelationsToRead() {
        return [];
    }

    /**
     * @return \Closure|null
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     */
    public function getValueConverter() {
        if (empty(parent::getValueConverter())) {
            switch ($this->getType()) {
                case static::TYPE_IMAGE:
                    $this->setValueConverter(function ($value, Column $columnConfig, array $record) {
                        if (!empty($value) && is_array($value) && !empty($value['url']) && is_array($value['url'])) {
                            if (count($value['url']) > 0) {
                                unset($value['url']['source']);
                            }
                            $images = [];
                            $translationPath = cmfTransCustom('.' . $columnConfig->getTableStructure()->getTableName())
                                . '.item_details.field.' . $columnConfig->getName() . '_version.';
                            foreach ($value['url'] as $key => $url) {
                                $images[] = [
                                    'label' => trans($translationPath . $key),
                                    'url' => $url
                                ];
                            }
                            return $images;
                        } else {
                            return [];
                        }
                    });
                    break;
            }
        }
        return parent::getValueConverter();
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        switch ($type) {
            case static::TYPE_TEXT:
                return '<div class="multiline-text">' . parent::doDefaultValueConversionByType($value, $type, $record) .  '</div>';
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

    public function configureDefaultRenderer(ValueRenderer $renderer) {
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
     * @param array $attributes
     * @return $this
     */
    public function addAttributesToValueContainer(array $attributes) {
        $this->valueContainerAttributes = $attributes;
        return $this;
    }

    /**
     * @return array
     */
    public function getValueContainerAttributes() {
        return $this->valueContainerAttributes;
    }

    /**
     * Hide value's label with its container
     * @return $this
     */
    public function hideLabel() {
        return $this->setLabel('');
    }

}