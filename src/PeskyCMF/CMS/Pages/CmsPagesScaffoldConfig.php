<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\DataGrid\DataGridConfig;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\ImagesFormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\TableInterface;
use Swayok\Html\Tag;
use Swayok\Utils\Set;

class CmsPagesScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->setSpecialConditions(function () {
                /** @var CmsPage $pageClass */
                $pageClass = app(CmsPage::class);
                return [
                    'type' => $pageClass::TYPE_PAGE,
                ];
            })
            ->enableNestedView()
            ->readRelations([
                'Parent' => ['id', 'url_alias', 'parent_id']
            ])
            ->setOrderBy('id', 'asc')
            ->setInvisibleColumns('url_alias')
            ->setColumns([
                DataGridConfig::ROW_ACTIONS_COLUMN_NAME,
                'id',
                'title',
                'relative_url' => DataGridColumn::create()
                    ->setIsSortable(false),
                'page_code',
                'is_published',
            ])
            ->setIsRowActionsColumnFixed(false)
            ->setFilterIsOpenedByDefault(false);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'title',
                'url_alias',
                'page_code',
                'is_published',
                'Parent.id',
                'Parent.url_alias',
                'Parent.title'
            ]);
    }

    protected function createItemDetailsConfig() {
        $itemDetailsConfig = parent::createItemDetailsConfig();
        $itemDetailsConfig
            ->readRelations([
                'Parent', 'Admin', 'Texts'
            ])
            ->addTab($this->translate('item_details.tab', 'general'), [
                'id',
                'title',
                'relative_url' => ValueCell::create()
                    ->setValueConverter(function ($value) {
                        $url = request()->getSchemeAndHttpHost() . $value;
                        return Tag::a()
                            ->setHref($url)
                            ->setContent($url)
                            ->setTarget('_blank')
                            ->build();
                    }),
                'page_code',
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
//                'meta_description',
//                'meta_keywords',
//                'order',
//                'custom_info',
                'is_published',
                'created_at',
                'updated_at',
                'admin_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
            ])
            ->setRawRecordDataModifier(function ($record) {
                if (!empty($record['Texts'])) {
                    $record['Texts'] = Set::combine($record['Texts'], '/language', '/');
                }
                return $record;
            });
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        if ($pagesTable->getTableStructure()->images->hasImagesConfigurations()) {
            $itemDetailsConfig->addTab($this->translate('item_details.tab', 'images'), [
                'images',
            ]);
        }
        /** @var CmsSetting $cmsSetting */
        $cmsSetting = app(CmsSetting::class);
        foreach ($cmsSetting::languages(null, []) as $langId => $langLabel) {
            $itemDetailsConfig->addTab($this->translate('item_details.tab', 'texts', ['language' => $langLabel]), [
                "Texts.$langId.id" => ValueCell::create()->setNameForTranslation('Texts.id'),
                "Texts.$langId.language" => ValueCell::create()
                    ->setNameForTranslation('Texts.language')
                    ->setValueConverter(function () use ($langLabel) {
                        return $langLabel;
                    }),
                "Texts.$langId.browser_title" => ValueCell::create()->setNameForTranslation('Texts.browser_title'),
                "Texts.$langId.menu_title" => ValueCell::create()->setNameForTranslation('Texts.menu_title'),
                "Texts.$langId.meta_description" => ValueCell::create()->setNameForTranslation('Texts.meta_description'),
                "Texts.$langId.meta_keywords" => ValueCell::create()->setNameForTranslation('Texts.meta_keywords'),
//                "Texts.$langId.comment" => ValueCell::create()->setNameForTranslation('Texts.comment'),
                "Texts.$langId.content" => ValueCell::create()
                    ->setType(ValueCell::TYPE_HTML)
                    ->setNameForTranslation('Texts.content'),

            ]);
        }
        return $itemDetailsConfig;
    }
    
    protected function createFormConfig() {
        $formConfig = parent::createFormConfig();
        /** @var CmsSetting $cmsSetting */
        $cmsSetting = app(CmsSetting::class);
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        /** @var CmsPage $pageClass */
        $pageClass = app(CmsPage::class);
        $formConfig
            ->setWidth(80)
            ->addTab($this->translate('form.tab', 'general'), [
                'title',
                'parent_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function () use ($pagesTable, $pageClass) {
                        return $this->getPagesOptions($pagesTable, $pageClass::TYPE_PAGE);
                    })
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->addData('isHidden', true);
                    }),
                'url_alias' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $rendererConfig) use ($formConfig) {
                        $rendererConfig
                            ->setIsRequired(true)
                            ->setPrefixText('<span id="parent-id-url-alias"></span>')
                            ->addAttribute('placeholder', $this->translate('form.input', 'url_alias_placeholder'));
                    })
                    ->setSubmittedValueModifier(function ($value) {
                        return $value === '/' ? $value : preg_replace('%//+%', '/', rtrim($value, '/'));
                    })
                    ->addJavaScriptBlock(function (FormInput $valueViewer) {
                        return $this->getJsCodeForUrlAliasInput($valueViewer);
                    }),
                'page_code',
                'comment',
//                'with_contact_form',
                'is_published',
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setValueConverter(function () use ($pageClass) {
                        return $pageClass::TYPE_PAGE;
                    }),
                'admin_id' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setValueConverter(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
            ])
            ->setValidators(function () use ($cmsSetting, $pagesTable, $pageClass) {
                $pagesTable::registerUniquePageUrlValidator($this);
                $validators = [
                    'type' => 'required|in:' . implode(',', $pageClass::getTypes()),
                    'is_published' => 'required|bool',
                    'title' => 'string|max:500',
                    'comment' => 'string|max:1000',
                ];
                foreach ($cmsSetting::languages() as $lang => $lebel) {
                    $validators["Texts.$lang.browser_title"] = "required_with:Texts.$lang.content";
                }
                return $validators;
            })
            ->addValidatorsForCreate(function () {
                return [
                    'url_alias' => 'required|regex:%^/[a-z0-9_/-]*$%|unique_page_url',
                    'page_code' => 'regex:%^[a-z0-9_-]*$%|unique:pages,page_code',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
                    'url_alias' => 'required|regex:%^/[a-z0-9_/-]*$%|unique_page_url',
                    'page_code' => 'regex:%^[a-z0-9_-]*$%|unique:pages,page_code,{{id}},id',
                ];
            })
            ->setRawRecordDataModifier(function (array $record) {
                if (!empty($record['Texts'])) {
                    $record['Texts'] = Set::combine($record['Texts'], '/language', '/');
                }
                return $record;
            })
            ->setIncomingDataModifier(function (array $data) {
                if (!empty($data['Texts']) && is_array($data['Texts'])) {
                    foreach ($data['Texts'] as $i => &$textData) {
                        if (empty($textData['id'])) {
                            unset($textData['id']);
                        }
                    }
                }
                return $data;
            });

        if ($pagesTable->getTableStructure()->images->hasImagesConfigurations()) {
            $formConfig->addTab($this->translate('form.tab', 'images'), [
                'images' => ImagesFormInput::create(),
            ]);
        }
        foreach ($cmsSetting::languages(null, []) as $langId => $langLabel) {
            $formConfig->addTab($this->translate('form.tab', 'texts', ['language' => $langLabel]), [
                "Texts.$langId.id" => FormInput::create()->setType(FormInput::TYPE_HIDDEN),
                "Texts.$langId.browser_title" => FormInput::create()->setNameForTranslation('Texts.browser_title'),
                "Texts.$langId.menu_title" => FormInput::create()->setNameForTranslation('Texts.menu_title'),
                "Texts.$langId.meta_description" => FormInput::create()->setNameForTranslation('Texts.meta_description'),
                "Texts.$langId.meta_keywords" => FormInput::create()->setNameForTranslation('Texts.meta_keywords'),
                "Texts.$langId.comment" => FormInput::create()->setNameForTranslation('Texts.comment'),
                "Texts.$langId.content" => WysiwygFormInput::create()
                    ->setRelativeImageUploadsFolder('/assets/wysiwyg/pages')
                    ->setDataInserts(function () {
                        return $this->getDataInsertsForContentEditor();
                    })
                    ->setNameForTranslation('Texts.content'),
                "Texts.$langId.language" => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () use ($langId) {
                        return $langId;
                    }),
                "Texts.$langId.admin_id" => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return \Auth::guard()->user()->getAuthIdentifier();
                    }),
            ]);
        }
        return $formConfig;
    }

    protected function addUniquePageUrlValidator() {
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
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
        \Validator::replacer('unique_page_url', function () use ($pagesTable) {
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
            return $this->translate('form.validation', 'unique_page_url', [
                'url' => routeToCmfItemEditForm($this->getTableNameForRoutes(), $otherPageId)
            ]);
        });
    }

    /**
     * @param TableInterface|CmsPagesTable $pagesTable
     * @param string $type
     * @return array
     */
    protected function getPagesOptions(TableInterface $pagesTable, $type) {
        $options = $pagesTable::selectAssoc('id', 'url_alias', [
            'parent_id' => null,
            'type' => $type,
            'url_alias !=' => '/'
        ]);
        $baseUrl = request()->getSchemeAndHttpHost();
        foreach ($options as $pageId => &$urlAlias) {
            $urlAlias = $baseUrl . $urlAlias;
        }
        return array_merge(['' => $baseUrl], $options);
    }

    protected function getJsCodeForUrlAliasInput(FormInput $formInput) {
        $parentIdInput = $formInput->getScaffoldSectionConfig()->getFormInput('parent_id');
        return <<<SCRIPT
            var parentIdSelect = $('#{$parentIdInput->getDefaultId()}');
            var parentIdSelectContainer = parentIdSelect.parent().removeClass('hidden');
            $('#parent-id-url-alias')
                .append(parentIdSelectContainer)
                .parent()
                    .addClass('pn');
            parentIdSelect.selectpicker();
            console.log(parentIdSelectContainer.html(), parentIdSelectContainer.find('button.dropdown-toggle'));
            parentIdSelectContainer
                .css('height', '32px')
                .addClass('mn')
                .find('button.dropdown-toggle')
                    .addClass('br-n')
                    .end()
                .find('.dropdown-menu')
                    .css('margin', '0 0 0 -1px');
                
SCRIPT;
    }

    protected function getDataInsertsForContentEditor() {
        return [
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertPageData(":page_id", ":page_field")',
                $this->translate('form.input.content_inserts', 'part_of_other_page'),
                false,
                [
                    'page_id' => [
                        'label' => $this->translate('form.input.content_inserts', 'page_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($this->getTableNameForRoutes(), 'pages_for_inserts', true),
                    ],
                    'page_field' => [
                        'label' => $this->translate('form.input.content_inserts', 'page_field_arg_label'),
                        'type' => 'select',
                        'options' => [
                            'menu_title' => $this->translate('form.input', 'Texts.menu_title'),
                            'content' => $this->translate('form.input', 'Texts.content'),
                        ],
                        'value' => 'content'
                    ]
                ],
                $this->translate('form.input.content_inserts', 'page_insert_widget_title_template')
            ),
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertLinkToPage(":page_id", ":title")',
                $this->translate('form.input.content_inserts', 'link_to_other_page'),
                false,
                [
                    'page_id' => [
                        'label' => $this->translate('form.input.content_inserts', 'page_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($this->getTableNameForRoutes(), 'pages_for_inserts', true),
                    ],
                    'title' => [
                        'label' => $this->translate('form.input.content_inserts', 'page_link_title_arg_label'),
                        'type' => 'text',
                    ]
                ],
                $this->translate('form.input.content_inserts', 'insert_link_to_page_widget_title_template')
            ),
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertTextData(":text_id", ":text_field")',
                $this->translate('form.input.content_inserts', 'part_of_text'),
                false,
                [
                    'text_id' => [
                        'label' => $this->translate('form.input.content_inserts', 'text_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($this->getTableNameForRoutes(), 'texts_for_inserts', true),
                    ],
                    'text_field' => [
                        'label' => $this->translate('form.input.content_inserts', 'text_field_arg_label'),
                        'type' => 'select',
                        'options' => [
                            'title' => $this->translate('form.input', 'Texts.title'),
                            'menu_title' => $this->translate('form.input', 'Texts.menu_title'),
                            'content' => $this->translate('form.input', 'Texts.content'),
                        ],
                        'value' => 'content'
                    ]
                ],
                $this->translate('form.input.content_inserts', 'text_insert_widget_title_template')
            ),
        ];
    }

    public function getCustomData($dataId) {
        switch ($dataId) {
            case 'texts_for_inserts':
                /** @var CmsTextsTable $textsTable */
                $textsTable = app(CmsTextsTable::class);
                return $textsTable::selectAssoc('id', 'title', [
                    'type' => null,
                    'page_id !=' => (int)request()->query('pk', 0) ?: 0,
                ]);
            case 'pages_for_inserts':
                /** @var CmsPagesTable $pagesTable */
                $pagesTable = app(CmsPagesTable::class);
                $pages = $pagesTable::select(['id', 'url_alias', 'type', 'Parent' => ['id', 'url_alias']], [
                    'id !=' => (int)request()->query('pk', 0) ?: 0,
                ]);
                $options = [];
                $typesTrans = [];
                /** @var CmsPage $pageData */
                foreach ($pages as $pageData) {
                    if (!array_key_exists($pageData->type, $typesTrans)) {
                        $typesTrans[$pageData->type] = $this->translate('types', $pageData->type);
                    }
                    if (!array_key_exists($typesTrans[$pageData->type], $options)) {
                        $options[$typesTrans[$pageData->type]] = [];
                    }
                    $options[$typesTrans[$pageData->type]][$pageData->id] = $pageData->relative_url;
                }
                return $options;
            default:
                return parent::getCustomData($dataId);
        }
    }
}