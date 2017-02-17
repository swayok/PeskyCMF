<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTable;

class CmsPagesTable extends CmsTable {

    /**
     * @return CmsPagesTableStructure
     */
    public function getTableStructure() {
        return CmsPagesTableStructure::getInstance();
    }

    /**
     * @return CmsPage
     */
    public function newRecord() {
        return new CmsPage();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsPages';
    }

}
