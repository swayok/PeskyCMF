<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\CMS\CmsTable;

class CmsAdminsTable extends CmsTable {

    /**
     * @return CmsAdminsTableStructure
     */
    public function getTableStructure() {
        return app(CmsAdminsTableStructure::class);
    }

    /**
     * @return CmsAdmin
     */
    public function newRecord() {
        return app(CmsAdmin::class);
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsAdmins';
    }
}