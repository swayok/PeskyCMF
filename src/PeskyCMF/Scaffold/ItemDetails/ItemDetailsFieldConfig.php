<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Scaffold\ScaffoldRenderableFieldConfig;
use PeskyORM\DbColumnConfig;

class ItemDetailsFieldConfig extends ScaffoldRenderableFieldConfig {
    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        parent::setType($type);
        switch ($this->type) {
            case self::TYPE_BOOL:
                $this->setValueConverter(function ($value) {
                    return CmfConfig::transBase('.item_details.field.bool.' . ($value ? 'yes' : 'no'));
                });
                break;
            case self::TYPE_IMAGE:
                $this->setRenderer(function ($field, $actionConfig, array $dataForView) {
                    return DataRendererConfig::create('cmf::details/image')->setData($dataForView);
                });
                $this->setValueConverter(function ($value, DbColumnConfig $columnConfig, array $record) {
                    if (!empty($value) && is_array($value) && !empty($value['url']) && is_array($value['url'])) {
                        if (count($value['url']) > 0) {
                            unset($value['url']['source']);
                        }
                        $images = [];
                        $translationPath = CmfConfig::getInstance()->custom_dictionary_name() . '.' . $columnConfig->getDbTableConfig()->getName()
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
        return $this;
    }
}