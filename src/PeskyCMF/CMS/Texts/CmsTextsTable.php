<?php

namespace PeskyCMF\CMS\Texts;

use PeskyCMF\CMS\CmsTable;

/**
 * @method CmsTextsTableStructure getTableStructure()
 * @method CmsText newRecord()
 */
class CmsTextsTable extends CmsTable {

    static protected $tableStructureClass = CmsTextsTableStructure::class;
    static protected $recordClass = CmsText::class;

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsTexts';
    }

}
