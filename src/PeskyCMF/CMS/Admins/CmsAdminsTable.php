<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\CMS\CmsTable;

class CmsAdminsTable extends CmsTable {

    /**
     * @return CmsAdminsTableStructure
     */
    public function getTableStructure() {
        return CmsAdminsTableStructure::getInstance();
    }

    /**
     * @return CmsAdmin
     */
    public function newRecord() {
        return new CmsAdmin();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsAdmins';
    }
}