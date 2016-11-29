<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\RenderableValueViewer;
use PeskyORM\ORM\Column;

class ValueCell extends RenderableValueViewer {

    const TYPE_JSON_TREE = 'json_collapsed';

    /**
     * @return callable|null
     * @throws \UnexpectedValueException
     */
    public function getValueConverter() {
        if (empty(parent::getValueConverter())) {
            switch ($this->getType()) {
                case static::TYPE_BOOL:
                    $this->setValueConverter(function ($value) {
                        return cmfTransGeneral('.item_details.field.bool.' . ($value ? 'yes' : 'no'));
                    });
                    break;
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

    static public function doDefaultValueConversionByType($value, $type) {
        switch ($type) {
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
                return parent::doDefaultValueConversionByType($value, $type);
        }
    }

}