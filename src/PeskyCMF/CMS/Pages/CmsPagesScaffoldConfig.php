<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\Scaffold\DataGrid\ColumnFilter;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\ImagesFormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\Core\DbExpr;

class CmsPagesScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->readRelations([
                'Parent' => ['id', 'url_alias', 'parent_id']
            ])
            ->setOrderBy('id', 'asc')
            ->setInvisibleColumns('url_alias')
            ->setColumns([
                'id',
                'type' => DataGridColumn::create()
                    ->setValueConverter(function ($value) {
                        return cmfTransCustom('.pages.types.' . $value);
                    }),
                'relative_url',
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
                'type' => ColumnFilter::create()
                    ->setInputType(ColumnFilter::INPUT_TYPE_MULTISELECT)
                    ->setAllowedValues(function () {
                        return CmsPage::getTypes(true);
                    }),
                'url_alias',
                'page_code',
                'with_contact_form',
                'is_published',
                'Parent.id',
                'Parent.url_alias'
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([
                'Parent', 'Admin'
            ])
            ->setValueCells([
                'id',
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'type' => ValueCell::create()
                    ->setValueConverter(function ($value) {
                        return cmfTransCustom('.pages.types.' . $value);
                    }),
                'relative_url',
                'page_code',
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
        $formConfig = parent::createFormConfig();
        $formConfig
            ->setWidth(80)
            ->addTab(cmfTransCustom('.pages.form.tab.general'), [
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setValueConverter(function () {
                        /** @var CmsPage $pageClass */
                        $pageClass = app(CmsPage::class);
                        return $pageClass::TYPE_PAGE;
                    }),
                'parent_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function () {
                        return $this->getPagesOptions();
                    })
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->addData('isHidden', true);
                    }),
                'url_alias' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $rendererConfig) use ($formConfig) {
                        $rendererConfig
                            ->setIsRequired(true)
                            ->setPrefixText('<span id="parent-id-url-alias"></span>')
                            ->addAttribute('placeholder', cmfTransCustom('.pages.form.input.url_alias_placeholder'));
                    })
                    ->setSubmittedValueModifier(function ($value, array $data) {
                        return $value === '/' ? $value : preg_replace('%//+%', '/', rtrim($value, '/'));
                    })
                    ->addJavaScriptBlock(function () {
                        return $this->getJsCodeForUrlAliasInput();
                    }),
                'page_code',
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
            ->setValidators(function () {
                $this->addUniquePageUrlValidator();
                /** @var CmsPage $pageClass */
                $pageClass = app(CmsPage::class);
                return [
                    'type' => 'required|in:' . implode(',', $pageClass::getTypes()),
                    'is_published' => 'required|bool',
                ];
            })
            ->addValidatorsForCreate(function () {
                return [
                    'url_alias' => 'required|regex:%^/[a-z0-9_/-]*$%|unique_page_url',
                    'page_code' => 'regex:%^/[a-z0-9_-]*$%|unique:pages,page_code,{{parent_id}},parent_id',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
                    'url_alias' => 'required|regex:%^/[a-z0-9_/-]*$%|unique_page_url',
                    'page_code' => 'regex:%^/[a-z0-9_-]*$%|unique:pages,page_code,{{parent_id}},parent_id,{{id}},id',
                ];
            });

        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        if ($pagesTable->getTableStructure()->images->hasImagesConfigurations()) {
            $formConfig->addTab(cmfTransCustom('.pages.form.tab.images'), [
                'images' => ImagesFormInput::create(),
            ]);
        }
        /** @var CmsSetting $cmsSetting */
        $cmsSetting = app(CmsSetting::class);
        /** @var CmsTextsTable $textsTable */
        $textsTable = app(CmsTextsTable::class);
        foreach ($cmsSetting::languages(null, []) as $langId => $langLabel) {
            $formConfig->addTab(cmfTransCustom('.pages.form.tab.texts', ['language' => $langLabel]), [
                "Texts.$langId.title",
                "Texts.$langId.menu_title",
                "Texts.$langId.language" => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                   ->setValueConverter(function () use ($langId) {
                       return $langId;
                   }),
                "Texts.$langId.admin_id" => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
                "Texts.$langId.comment",
                "Texts.$langId.content" => WysiwygFormInput::create()
                    ->setRelativeImageUploadsFolder('/assets/wysiwyg/pages')
                    ->setDataInserts(function () {
                        return $this->getDataInsertsForContentEditor();
                    })
            ]);
        }
        return $formConfig;
    }

    protected function addUniquePageUrlValidator() {
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        \Validator::extend('unique_page_url', function ($attribute, $value, $parameters) use ($pagesTable) {
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
        \Validator::replacer('unique_page_url', function ($message, $attribute, $rule, $parameters) use ($pagesTable) {
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
            return cmfTransCustom('.pages.form.validation.unique_page_url', [
                'url' => routeToCmfItemEditForm($this->getTableNameForRoutes(), $otherPageId)
            ]);
        });
    }

    protected function getPagesOptions() {
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        /** @var CmsPage $pageClass */
        $pageClass = app(CmsPage::class);
        $options = $pagesTable::selectAssoc('id', 'url_alias', [
            'parent_id' => null,
            'type' => $pageClass::TYPE_PAGE,
            'url_alias !=' => '/'
        ]);
        $baseUrl = request()->getSchemeAndHttpHost();
        foreach ($options as $pageId => &$urlAlias) {
            $urlAlias = $baseUrl . $urlAlias;
        }
        return array_merge(['' => $baseUrl], $options);
    }

    protected function getJsCodeForUrlAliasInput() {
        return <<<SCRIPT
            var parentIdSelect = $('#t-pages-c-parent_id-input').parent();
            $('#parent-id-url-alias')
                .append(parentIdSelect)
                .parent()
                    .addClass('pn');
            parentIdSelect
                .css('height', '32px')
                .find('> button.dropdown-toggle')
                    .addClass('br-n');
            
SCRIPT;

    }

    protected function getDataInsertsForContentEditor() {
        return [
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'pageData(":text_id", ":text_field")',
                'Вставить часть другого текста',
                false,
                [
                    'text_id' => [
                        'label' => 'Выберите Текст',
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($this->getTableNameForRoutes(), 'texts_for_inserts', true),
                    ],
                    'text_field' => [
                        'label' => 'Выберите какую часть выбранного Текста вставить',
                        'type' => 'select',
                        'options' => [
                            'title' => cmfTransCustom('.common_texts.form.input.title'),
                            'content' => cmfTransCustom('.common_texts.form.input.content'),
                        ],
                        'value' => 'content'
                    ]
                ],
                cmfTransCustom('.common_texts.form.input.insert_other_text_widget_title_template')
            ),
        ];
    }

    public function getCustomData($dataId) {
        if ($dataId === 'texts_for_inserts') {
            /** @var CmsTextsTable $textsTable */
            $textsTable = app(CmsTextsTable::class);
            return $textsTable::selectAssoc('id', 'title', [
                'type' => null,
                'id !=' => (int)request()->query('pk', 0) ?: 0,
            ]);
        } else {
            return parent::getCustomData($dataId);
        }
    }
}