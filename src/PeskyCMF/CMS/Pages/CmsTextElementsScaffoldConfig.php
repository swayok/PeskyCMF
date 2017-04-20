<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use Swayok\Utils\Set;

class CmsTextElementsScaffoldConfig extends NormalTableScaffoldConfig {

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
                    'type' => $pageClass::TYPE_TEXT_ELEMENT,
                ];
            })
            ->setInvisibleColumns('url_alias')
            ->setColumns([
                'id' => DataGridColumn::create()
                    ->setWidth(40),
                'title',
                'page_code',
            ])
            ->setFilterIsOpenedByDefault(false);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'title',
                'page_code',
            ]);
    }

    protected function createItemDetailsConfig() {
        $itemDetailsConfig = parent::createItemDetailsConfig();
        $itemDetailsConfig
            ->setShowAsDialog(true)
            ->readRelations([
                'Admin', 'Texts'
            ])
            ->addTab($this->translate('item_details.tab', 'general'), [
                'id',
                'title',
                'page_code',
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
                'title' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->required();
                    }),
                'page_code' => FormInput::create()
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->addAttribute('data-regexp', '^[a-zA-Z0-9_:-]+$');
                    }),
                'comment',
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN),
                'is_published' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN),
                'admin_id' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
            ])
            ->setValidators(function () use ($cmsSetting, $pagesTable, $pageClass) {
                return [
                    'title' => 'required|string|max:500',
                    'comment' => 'string|max:1000',
                ];
            })
            ->addValidatorsForCreate(function () {
                return [
                    'page_code' => 'regex:%^[a-zA-Z0-9_:-]*$%|unique:pages,page_code',
                ];
            })
            ->addValidatorsForEdit(function () {
                return [
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
                $data['admin_id'] = \Auth::guard()->user()->getAuthIdentifier();
                $data['type'] = $pageClass::TYPE_TEXT_ELEMENT;
                $data['is_published'] = false;
                if (!empty($data['Texts']) && is_array($data['Texts'])) {
                    foreach ($data['Texts'] as $i => &$textData) {
                        if (empty($textData['id'])) {
                            unset($textData['id']);
                        }
                        $textData['admin_id'] = $data['admin_id'];
                    }
                }
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
            ]);
        }
        return $formConfig;
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