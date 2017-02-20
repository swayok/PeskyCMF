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
        /** @var CmsText $class */
        $class = app(CmsText::class);
        return $class::newEmptyRecord();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsTexts';
    }

}
