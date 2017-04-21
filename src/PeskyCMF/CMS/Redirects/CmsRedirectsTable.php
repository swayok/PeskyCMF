<?php

namespace PeskyCMF\CMS\Redirects;

use PeskyCMF\CMS\CmsTable;

class CmsRedirectsTable extends CmsTable {

    /**
     * @return CmsRedirectsTableStructure
     */
    public function getTableStructure() {
        return app(CmsRedirectsTableStructure::class);
    }

    /**
     * @return CmsRedirect
     */
    public function newRecord() {
        /** @var CmsRedirect $class */
        $class = app(CmsRedirect::class);
        return $class::newEmptyRecord();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsRedirects';
    }

}
