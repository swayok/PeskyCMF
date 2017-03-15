<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTable;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\Core\DbExpr;

class CmsPagesTable extends CmsTable {

    /**
     * @return CmsPagesTableStructure
     */
    public function getTableStructure() {
        return app(CmsPagesTableStructure::class);
    }

    /**
     * @return CmsPage
     */
    public function newRecord() {
        /** @var CmsPage $class */
        $class = app(CmsPage::class);
        return $class::newEmptyRecord();
    }

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsPages';
    }

    static public function registerUniquePageUrlValidator(ScaffoldConfig $scaffoldConfig) {
        \Validator::extend('unique_page_url', function () use ($pagesTable) {
            $urlAlias = request()->input('url_alias');
            $parentId = (int)request()->input('parent_id');
            if ($parentId > 0 && $urlAlias === '/') {
                return false;
            } else {
                return $pagesTable::count([
                    'url_alias' => $urlAlias,
                    'id !=' => (int)request()->input('id'),
                    'parent_id' => $parentId > 0 ? $parentId : null
                ]) === 0;
            }
        });
        \Validator::replacer('unique_page_url', function () use ($pagesTable, $scaffoldConfig) {
            $urlAlias = request()->input('url_alias');
            $parentId = (int)request()->input('parent_id');
            if ($parentId > 0 && $urlAlias === '/') {
                $otherPageId = $parentId;
            } else {
                $otherPageId = $pagesTable::selectValue(
                    DbExpr::create('`id`'),
                    [
                        'url_alias' => $urlAlias,
                        'parent_id' => $parentId > 0 ? $parentId : null
                    ]
                );
            }
            return $scaffoldConfig->translate('form.validation', 'unique_page_url', [
                'url' => routeToCmfItemEditForm($scaffoldConfig->getTableNameForRoutes(), $otherPageId)
            ]);
        });
    }

}
