<?php

namespace PeskyCMF\Db\Admins;

use PeskyCMF\Db\CmfDbTable;

class CmfAdminsTable extends CmfDbTable {

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmfAdmins';
    }

    /**
     * @return CmfAdminsTableStructure
     */
    public function getTableStructure() {
        return CmfAdminsTableStructure::getInstance();
    }

    /**
     * @return CmfAdmin
     */
    public function newRecord() {
        return CmfAdmin::newEmptyRecord();
    }


}