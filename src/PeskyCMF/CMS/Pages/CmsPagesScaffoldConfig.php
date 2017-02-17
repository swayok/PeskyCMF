<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\ImagesFormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;

class CmsPagesScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                'PrimaryText' => ['title', 'id']
            ])
            ->setOrderBy('id', 'asc')
            ->setColumns([
                'id',
                'text_id' => DataGridColumn::create()
                    ->setType(DataGridColumn::TYPE_LINK),
                'type',
                'url_alias',
                'page_code',
                'with_contact_form',
                'is_published',
            ])
            ->setFilterIsOpenedByDefault(false);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'PrimaryText.title',
                'PrimaryText.menu_title',
                'type',
                'url_alias',
                'page_code',
                'with_contact_form',
                'is_published',
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([
                'Parent', 'Admin', 'PrimaryText' => ['id', 'title']
            ])
            ->setValueCells([
                'id',
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'type',
                'url_alias',
                'page_code',
                'text_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'images',
                'meta_description',
                'meta_keywords',
                'order',
                'with_contact_form',
                'custom_info',
                'admin_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'is_published',
                'created_at',
                'updated_at'
            ]);
    }
    
    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(80)
            ->addTab(trans('admin.pages.form.tab.general'), [
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptions(function () {
                        return CmsPage::getTypes(true);
                    }),
//                'parent_id',
                'url_alias' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $rendererConfig) {
                        $rendererConfig->setIsRequired(true);
                    }),
                'page_code',
                'text_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function () {
                        return $this->getTextsOptions();
                    }),
                'comment',
                'meta_description',
                'meta_keywords',
                'with_contact_form',
                'is_published',
                'admin_id' => FormInput::create()
                    ->setRenderer(function () {
                        return InputRenderer::create('cmf::input/hidden');
                    })->setValueConverter(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
            ])
            ->addTab(trans('admin.pages.form.tab.images'), [
                'images' => ImagesFormInput::create(),
            ])
            ->setValidators(function () {
                return [
                    'type' => 'required|in:' . implode(',', CmsPage::getTypes()),
                    'is_published' => 'required|bool',
                ];
            })
            ->addValidatorsForCreate(function () {
                return [
                    'url_alias' => 'required|regex:%^[a-z-_/]+$%|unique:pages,url_alias',
                    'page_code' => 'required|regex:%^[a-z-_]+$%|unique:pages,page_code',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
                    'url_alias' => 'required|regex:%^[a-z-_/]+$%|unique:pages,url_alias,{{id}},id',
                    'page_code' => 'required|regex:%^[a-z-_]+$%|unique:pages,page_code,{{id}},id',
                ];
            });
    }

    protected function getTextsOptions() {
        return CmsTextsTable::selectAssoc('id', 'title', [
            'parent_id' => null
        ]);
    }
}