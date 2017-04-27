<?php

namespace PeskyCMF\CMS\Redirects;

use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;

class CmsRedirectsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                'Page' => ['*'],
                'Admin' => ['*'],
            ])
            ->setOrderBy('id', 'asc')
            ->setColumns([
                'id' => DataGridColumn::create()
                    ->setWidth(40),
                'relative_url',
                'page_id' => DataGridColumn::create()
                    ->setType(DataGridColumn::TYPE_LINK),
                'is_permanent',
            ])
            ->closeFilterByDefault();
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'relative_url',
                'page_id',
                'is_permanent',
                'admin_id',
                'Page.title',
                'Page.url_alias'
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([
                'Page','Admin',
            ])
            ->setValueCells([
                'id',
                'relative_url',
                'page_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'is_permanent',
                'admin_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'created_at',
                'updated_at'
            ]);
    }
    
    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(50)
            ->setFormInputs([
                'relative_url',
                'page_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function () {
                        return $this->getPagesOptions();
                    }),
                'is_permanent',
                'admin_id' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
            ]);
    }

    protected function getPagesOptions() {
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        /** @var CmsPage $pageClass */
        $pageClass = app(CmsPage::class);
        $pages = $pagesTable::select(
            ['id', 'url_alias', 'type', 'parent_id', 'Parent' => ['id', 'url_alias', 'parent_id']],
            [
                'type !=' => $pageClass::getTypesWithoutUrls(),
                'url_alias IS NOT' => null,
                'url_alias !=' => ''
            ]
        );
        $pages->enableDbRecordInstanceReuseDuringIteration(true);
        $optionsByType = [];
        /** @var CmsPage $page */
        foreach ($pages as $page) {
            if (!array_key_exists($page->type, $optionsByType)) {
                $optionsByType[$page->type] = [];
            }
            $relativeUrl = $page->relative_url;
            $optionsByType[$page->type][$page->id] = $relativeUrl;
        }
        $options = [];
        foreach ($optionsByType as $type => $pages) {
            asort($pages);
            $options[$this->translate('form.input', 'page_types.' . $type)] = $pages;
        }
        return $options;
    }
}