<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\ImagesFormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\ORM\TableInterface;
use Swayok\Utils\Set;

class CmsNewsScaffoldConfig extends NormalTableScaffoldConfig {

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
                    'type' => $pageClass::TYPE_NEWS,
                ];
            })
            ->setOrderBy('publish_at', 'desc')
            ->readRelations([
                'Parent' => ['id', 'url_alias', 'parent_id']
            ])
            ->setInvisibleColumns('url_alias')
            ->setColumns([
                'id' => DataGridColumn::create()
                    ->setWidth(40),
                'title',
                'relative_url',
                'is_published',
                'publish_at',
            ])
            ->setFilterIsOpenedByDefault(false);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'title',
                'url_alias',
                'is_published',
                'publish_at',
                'Parent.id',
                'Parent.title',
                'Parent.url_alias',
            ]);
    }

    protected function createItemDetailsConfig() {
        $itemDetailsConfig = parent::createItemDetailsConfig();
        $itemDetailsConfig
            ->setShowAsDialog(true)
            ->readRelations([
                'Parent', 'Admin', 'Texts'
            ])
            ->addTab($this->translate('item_details.tab', 'general'), [
                'id',
                'title',
                'relative_url',
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
//                'order',
//                'custom_info',
                'is_published',
                'publish_at',
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
//        $pagesTable = app(CmsPagesTable::class);
//        if ($pagesTable->getTableStructure()->images->hasImagesConfigurations()) {
//            $itemDetailsConfig->addTab($this->translate('item_details.tab', 'images'), [
//                'images',
//            ]);
//        }
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
            ->setShowAsDialog(true)
            ->addTab($this->translate('form.tab', 'general'), [
                'title',
                'parent_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function () use ($pagesTable, $pageClass) {
                        return $this->getPagesOptions($pagesTable, $pageClass::TYPE_NEWS);
                    })
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->addData('isHidden', true);
                    }),
                'url_alias' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $rendererConfig) use ($formConfig) {
                        $rendererConfig
                            ->setIsRequired(true)
                            ->setPrefixText('<span id="parent-id-url-alias"></span>')
                            ->addAttribute('data-regexp', '^[a-z0-9_/-]+$')
                            ->addAttribute('placeholder', $this->translate('form.input', 'url_alias_placeholder'));
                    })
                    ->setSubmittedValueModifier(function ($value) {
                        return $value === '/' ? $value : preg_replace('%//+%', '/', rtrim($value, '/'));
                    })
                    ->addJavaScriptBlock(function (FormInput $formInput) {
                        return $this->getJsCodeForUrlAliasInput($formInput);
                    }),
                'comment',
                'is_published',
                'publish_at' => FormInput::create()
                    ->setType(FormInput::TYPE_DATE)
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->addData('config', [
                            'minDate' => null,
                            'useCurrent' => true
                        ]);
                    }),
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN),
                'admin_id' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN),
            ])
            ->setValidators(function () use ($cmsSetting, $pagesTable, $pageClass) {
                $pagesTable::registerUniquePageUrlValidator($this);
                $validators = [
                    'is_published' => 'required|boolean',
                    'publish_at' => 'required|date',
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
                    'page_code' => 'regex:%^[a-zA-Z0-9_:-]*$%|unique:pages,page_code',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
                    'url_alias' => 'required|regex:%^/[a-z0-9_/-]*$%|unique_page_url',
                    'page_code' => 'regex:%^[a-zA-Z0-9_:-]*$%|unique:pages,page_code,{{id}},id',
                ];
            })
            ->setRawRecordDataModifier(function (array $record) {
                if (!empty($record['Texts'])) {
                    $record['Texts'] = Set::combine($record['Texts'], '/language', '/');
                }
                return $record;
            })
            ->setIncomingDataModifier(function (array $data) use ($pageClass) {
                if (!empty($data['Texts']) && is_array($data['Texts'])) {
                    foreach ($data['Texts'] as $i => &$textData) {
                        if (empty($textData['id'])) {
                            unset($textData['id']);
                        }
                    }
                }
                unset($textData);
                $data['type'] = $pageClass::TYPE_NEWS;
                $data['admin_id'] = \Auth::guard()->user()->getAuthIdentifier();
                return $data;
            });

//        if ($pagesTable->getTableStructure()->images->hasImagesConfigurations()) {
//            $formConfig->addTab($this->translate('form.tab', 'images'), [
//                'images' => ImagesFormInput::create(),
//            ]);
//        }
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
            parentIdSelectContainer
                .css('height', '32px')
                .addClass('mn')
                .find('.bootstrap-select.form-control')
                    .css('height', '32px')
                    .find('button.dropdown-toggle')
                        .addClass('br-n');
                
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
                'insertPageData(":page_id", "content")',
                $this->translate('form.input.content_inserts', 'text_block'),
                false,
                [
                    'page_id' => [
                        'label' => $this->translate('form.input.content_inserts', 'text_block_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($this->getTableNameForRoutes(), 'text_blocks_for_inserts', true),
                    ]
                ],
                $this->translate('form.input.content_inserts', 'text_block_insert_widget_title_template')
            ),
        ];
    }

    public function getCustomData($dataId) {
        /** @var CmsPage $pageClass */
        $pageClass = app(CmsPage::class);
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        switch ($dataId) {
            case 'text_blocks_for_inserts':
                return $pagesTable::selectAssoc('id', 'title', [
                    'type' => $pageClass::TYPE_TEXT_ELEMENT,
                    'id !=' => (int)request()->query('pk', 0) ?: 0,
                ]);
            case 'pages_for_inserts':
                $pages = $pagesTable::select(['id', 'url_alias', 'type', 'Parent' => ['id', 'url_alias']], [
                    'type !=' => $pageClass::TYPE_TEXT_ELEMENT,
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