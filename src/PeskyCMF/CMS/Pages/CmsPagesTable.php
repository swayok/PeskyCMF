<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTable;
use PeskyCMF\Scaffold\ScaffoldConfig;
use PeskyORM\Core\DbExpr;

/**
 * @method CmsPagesTableStructure getTableStructure()
 * @method CmsPage newRecord()
 */
class CmsPagesTable extends CmsTable {

    static protected $tableStructureClass = CmsPagesTableStructure::class;
    static protected $recordClass = CmsPage::class;

    /**
     * @return string
     */
    public function getTableAlias() {
        return 'CmsPages';
    }

    static public function registerUniquePageUrlValidator(ScaffoldConfig $scaffoldConfig) {
        \Validator::extend('unique_page_url', function () {
            $urlAlias = request()->input('url_alias');
            $parentId = (int)request()->input('parent_id');
            if ($parentId > 0 && $urlAlias === '/') {
                return false;
            } else {
                return static::count([
                    'url_alias' => $urlAlias,
                    'id !=' => (int)request()->input('id'),
                    'parent_id' => $parentId > 0 ? $parentId : null
                ]) === 0;
            }
        });
        \Validator::replacer('unique_page_url', function () use ($scaffoldConfig) {
            $urlAlias = request()->input('url_alias');
            $parentId = (int)request()->input('parent_id');
            if ($parentId > 0 && $urlAlias === '/') {
                $otherPageId = $parentId;
            } else {
                $otherPageId = static::selectValue(
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
