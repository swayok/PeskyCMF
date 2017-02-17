<?php

namespace PeskyCMF\CMS\Texts;

use PeskyCMF\CMS\CmsTable;

class CmsTextsTable extends CmsTable {

    /**
     * @return CmsTextsTableStructure
     */
    public function getTableStructure() {
        return CmsTextsTableStructure::getInstance();
    }

    /**
     * @return CmsText
     */
    public function newRecord() {
        return new CmsText();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsTexts';
    }

}
