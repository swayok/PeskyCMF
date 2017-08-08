<?php

namespace PeskyCMF\CMS\Redirects;

use PeskyCMF\CMS\CmsTable;

/**
 * @method CmsRedirectsTableStructure getTableStructure()
 * @method CmsRedirect newRecord()
 */
class CmsRedirectsTable extends CmsTable {

    static protected $tableStructureClass = CmsRedirectsTableStructure::class;
    static protected $recordClass = CmsRedirect::class;

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsRedirects';
    }

}
