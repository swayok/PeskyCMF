<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\CMS\CmsTable;
use PeskyORM\ORM\RecordInterface;

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
        /** @var CmsAdmin $class */
        $class = app(CmsAdmin::class);
        return $class::newEmptyRecord();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsAdmins';
    }
}