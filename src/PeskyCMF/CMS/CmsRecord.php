<?php

namespace PeskyCMF\CMS;

use PeskyCMF\Db\CmfDbRecord;

/**
 * @method static CmsTableStructure getTableStructure()
 */
abstract class CmsRecord extends CmfDbRecord {

    /**
     * @return \PeskyCMF\Config\CmfConfig
     */
    static protected function getCmsConfig() {
        return static::getTableStructure()->getCmsConfig();
    }
}