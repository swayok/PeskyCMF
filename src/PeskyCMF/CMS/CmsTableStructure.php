<?php

namespace PeskyCMF\CMS;

use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\TableStructure;

abstract class CmsTableStructure extends TableStructure {

    /**
     * @var CmfConfig|null
     */
    static private $cmsConfig;

    /**
     * @return CmfConfig
     */
    static public function getCmsConfig() {
        return self::$cmsConfig ?: CmfConfig::getInstance();
    }

    /**
     * @param CmfConfig $config
     */
    static public function setCmsConfig(CmfConfig $config) {
        self::$cmsConfig = $config;
    }
}