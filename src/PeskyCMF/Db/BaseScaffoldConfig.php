<?php

namespace PeskyCMF\Db;

use PeskyCMF\Scaffold\ScaffoldSectionConfig;

class BaseScaffoldConfig extends ScaffoldSectionConfig {

    protected function loadModel() {
        $className = str_replace('ScaffoldConfig', 'Model', get_class($this));
        return $className::getInstance();
    }
}