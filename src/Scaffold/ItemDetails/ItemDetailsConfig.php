<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Scaffold\ScaffoldActionConfig;

class ItemDetailsConfig extends ScaffoldActionConfig {

    /**
     * @inheritdoc
     */
    public function createFieldConfig($fieldName) {
        $columnConfig = $this->getModel()->getTableColumn($fieldName);
        $config = ItemDetailsFieldConfig::create()
            ->setType($columnConfig->getType());
        return $config;
    }


}