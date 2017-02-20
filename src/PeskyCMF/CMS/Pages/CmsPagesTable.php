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
        return app(CmsPage::class);
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsPages';
    }

}
