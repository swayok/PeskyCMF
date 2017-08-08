<?php

namespace PeskyCMF\CMS\Admins;

use PeskyCMF\CMS\CmsTable;
use PeskyORM\ORM\RecordInterface;

/**
 * @method CmsAdminsTableStructure getTableStructure()
 * @method CmsAdmin newRecord()
 */
class CmsAdminsTable extends CmsTable {

    static protected $tableStructureClass = CmsAdminsTableStructure::class;
    static protected $recordClass = CmsAdmin::class;

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsAdmins';
    }
}