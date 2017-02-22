<?php

namespace PeskyCMF\CMS\Texts;

use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Settings\CmsSetting;
use PeskyCMF\Scaffold\DataGrid\DataGridColumn;
use PeskyCMF\Scaffold\Form\FormInput;
use PeskyCMF\Scaffold\Form\InputRenderer;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ItemDetails\ValueCell;
use PeskyCMF\Scaffold\NormalTableScaffoldConfig;
use PeskyORM\Core\DbExpr;

class CmsTextsForItemsScaffoldConfig extends NormalTableScaffoldConfig {

    protected $isDetailsViewerAllowed = true;
    protected $isCreateAllowed = true;
    protected $isEditAllowed = true;
    protected $isDeleteAllowed = true;
    
    protected function createDataGridConfig() {
        return parent::createDataGridConfig()
            ->setOrderBy('id', 'asc')
            ->setColumns([
                'id',
                'language' => DataGridColumn::create()
                    ->setValueConverter(function ($value) {
                        /** @var CmsSetting $cmsSetting */
                        $cmsSetting = app(CmsSetting::class);
                        $ret = array_get($cmsSetting::languages(null, []), $value, $value);
                        return empty($ret) ? $value : $ret;
                    }),
                'title',
                'menu_title',
            ])
            ->setSpecialConditions(function () {
                /** @var CmsPage $pageClass */
                $pageClass = app(CmsPage::class);
                return [
                    'type' => $pageClass::TYPE_ITEM
                ];
            })
            ->setFilterIsOpenedByDefault(false);
    }
    
    protected function createDataGridFilterConfig() {
        return parent::createDataGridFilterConfig()
            ->setFilters([
                'id',
                'title',
                'menu_title',
                'language',
            ]);
    }

    protected function createItemDetailsConfig() {
        return parent::createItemDetailsConfig()
            ->readRelations([
                'Parent', 'Admin',
            ])
            ->setValueCells([
                'id',
                'parent_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'language',
                'title',
                'browser_title',
                'menu_title',
                'content',
                'meta_description',
                'meta_keywords',
                'custom_info',
                'admin_id' => ValueCell::create()
                    ->setType(ValueCell::TYPE_LINK),
                'created_at',
                'updated_at'
            ]);
    }
    
    protected function createFormConfig() {
        return parent::createFormConfig()
            ->setWidth(100)
            ->addTab(trans('admin.texts.form.tab.general'), [
                'type' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        /** @var CmsPage $pageClass */
                        $pageClass = app(CmsPage::class);
                        return $pageClass::TYPE_ITEM;
                    }),
                'is_translation' => FormInput::create()
                    ->setIsLinkedToDbColumn(false)
                    ->setType(FormInput::TYPE_BOOL)
                    ->setValueConverter(function (array $record) {
                        return !empty($record['id']) && !empty($record['parent_id']);
                    })
                    ->setDisabledUntil('id', '/^$/', true),
                'language' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptions(function () {
                        /** @var CmsSetting $cmsSetting */
                        $cmsSetting = app(CmsSetting::class);
                        return $cmsSetting::languages(null, []);
                    })
                    ->setDefaultRendererConfigurator(function (InputRenderer $renderer) {
                        $renderer->setIsRequired(true);
                    })
                    ->setReadonlyUntil('id', '/^$/', true),
                'parent_id' => FormInput::create()
                    ->setType(FormInput::TYPE_SELECT)
                    ->setOptionsLoader(function ($excludeId) {
                        /** @var CmsPage $pageClass */
                        $pageClass = app(CmsPage::class);
                        return CmsTextsTable::selectAssoc('id', 'title', array_merge(
                            [
                                'type' => $pageClass::TYPE_ITEM,
                                'parent_id' => null,
                                'ORDER' => ['title' => 'asc']
                            ],
                            $excludeId !== null ? ['id !=' => $excludeId] : []
                        ));
                    })
                    ->setDisabledUntil('id', '/^$/', true)
                    ->setDisabledUntil('is_translation', true),
                'title',
                'browser_title',
                'menu_title',
                'meta_description',
                'meta_keywords',
                'admin_id' => FormInput::create()
                    ->setType(FormInput::TYPE_HIDDEN)
                    ->setSubmittedValueModifier(function () {
                        return \Auth::guard()->user()->id;
                    }),
            ])
            ->addTab(trans('admin.texts.form.tab.content'), [
                'comment',
                'content' => WysiwygFormInput::create()
                    ->setRelativeImageUploadsFolder('/assets/wysiwyg/pages')
                    ->setDataInserts(function () {
                        return $this->getDataInsertsForContentEditor();
                    })
            ])
            ->setValidators(function () {
                $this->addSpecialValidators();
                return [
                    'title' => 'required',
                    'language' => 'required:id|unique_language_within_parent_id'
                ];
            });
    }

    public function addSpecialValidators() {
        /** @var CmsTextsTable $textsTable */
        $textsTable = app(CmsTextsTable::class);
        \Validator::extend('unique_language_within_parent_id', function ($attribute, $value, $parameters) use ($textsTable) {
            if (!request()->input('parent_id')) {
                return true;
            } else {
                return $textsTable::count([
                    'OR' => [
                        'id' => request()->input('parent_id'),
                        'parent_id' => request()->input('parent_id'),
                    ],
                    'language' => $value
                ]) === 0;
            }
        });
        \Validator::replacer('unique_language_within_parent_id', function ($message, $attribute, $rule, $parameters) use ($textsTable) {
            return cmfTransCustom('.texts.form.validation.unique_language_within_parent_id', [
                'parent_title' => $textsTable::selectValue(
                    DbExpr::create('`title`'),
                    ['id' => request()->input('parent_id')]
                ),
                'url' => routeToCmfItemEditForm(
                    $this->getTableNameForRoutes(),
                    $textsTable::selectValue(
                        DbExpr::create('`id`'),
                        [
                            'OR' => [
                                'id' => request()->input('parent_id'),
                                'parent_id' => request()->input('parent_id'),
                            ],
                            'language' => request()->input('language')
                        ]
                    )
                )
            ]);
        });
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
                            'title' => cmfTransCustom('.texts.form.input.title'),
                            'content' => cmfTransCustom('.texts.form.input.content'),
                        ],
                        'value' => 'content'
                    ]
                ],
                cmfTransCustom('.texts.form.input.insert_other_text_widget_title_template')
            ),
        ];
    }

    public function getCustomData($dataId) {
        if ($dataId === 'texts_for_inserts') {
            /** @var CmsTextsTable $textsTable */
            $textsTable = app(CmsTextsTable::class);
            /** @var CmsPage $pageClass */
            $pageClass = app(CmsPage::class);
            return $textsTable::selectAssoc('id', 'title', [
                'type' => $pageClass::TYPE_ITEM,
                'id !=' => (int)request()->query('pk', 0) ?: 0,
            ]);
        } else {
            return parent::getCustomData($dataId);
        }
    }



}