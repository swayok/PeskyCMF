<?php

namespace PeskyCMF\CMS\Texts;

use PeskyCMF\CMS\CmsTable;

class CmsTextsTable extends CmsTable {

    /**
     * @return CmsTextsTableStructure
     */
    public function getTableStructure() {
        return app(CmsTextsTableStructure::class);
    }

    /**
     * @return CmsText
     */
    public function newRecord() {
        return app(CmsText::class);
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsTexts';
    }

}
