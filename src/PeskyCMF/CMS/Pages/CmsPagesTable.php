<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTable;

class CmsPagesTable extends CmsTable {

    /**
     * @return CmsPagesTableStructure
     */
    public function getTableStructure() {
        return app(CmsPagesTableStructure::class);
    }

    /**
     * @return CmsPage
     */
    public function newRecord() {
        /** @var CmsPage $class */
        $class = app(CmsPage::class);
        return $class::newEmptyRecord();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsPages';
    }

}
