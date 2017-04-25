<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Scaffold\ScaffoldConfig;

abstract class CmsPagesScaffoldsHelper {

    static public function getConfigsForWysiwygDataInserts(ScaffoldConfig $scaffold) {
        return [
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertPageData(":page_id", ":page_field")',
                $scaffold->translate('form.input.content_inserts', 'part_of_other_page'),
                false,
                [
                    'page_id' => [
                        'label' => $scaffold->translate('form.input.content_inserts', 'page_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($scaffold->getTableNameForRoutes(), 'pages_for_inserts', true),
                    ],
                    'page_field' => [
                        'label' => $scaffold->translate('form.input.content_inserts', 'page_field_arg_label'),
                        'type' => 'select',
                        'options' => [
                            'menu_title' => $scaffold->translate('form.input', 'Texts.menu_title'),
                            'content' => $scaffold->translate('form.input', 'Texts.content'),
                        ],
                        'value' => 'content'
                    ]
                ],
                $scaffold->translate('form.input.content_inserts', 'page_insert_widget_title_template')
            ),
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertLinkToPage(":page_id", ":title")',
                $scaffold->translate('form.input.content_inserts', 'link_to_other_page'),
                false,
                [
                    'page_id' => [
                        'label' => $scaffold->translate('form.input.content_inserts', 'page_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($scaffold->getTableNameForRoutes(), 'pages_for_inserts', true),
                    ],
                    'title' => [
                        'label' => $scaffold->translate('form.input.content_inserts', 'page_link_title_arg_label'),
                        'type' => 'text',
                    ]
                ],
                $scaffold->translate('form.input.content_inserts', 'insert_link_to_page_widget_title_template')
            ),
            WysiwygFormInput::createDataInsertConfigWithArguments(
                'insertPageData(":page_id", "content")',
                $scaffold->translate('form.input.content_inserts', 'text_block'),
                false,
                [
                    'page_id' => [
                        'label' => $scaffold->translate('form.input.content_inserts', 'text_block_id_arg_label'),
                        'type' => 'select',
                        'options' => routeToCmfTableCustomData($scaffold->getTableNameForRoutes(), 'text_blocks_for_inserts', true),
                    ]
                ],
                $scaffold->translate('form.input.content_inserts', 'text_block_insert_widget_title_template')
            ),
        ];
    }

    /**
     * @param ScaffoldConfig $scaffold
     * @param string $dataId
     * @param null|int $currentPageId
     * @return array|null
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getDataForWysiwygInserts(ScaffoldConfig $scaffold, $dataId, $currentPageId = null) {
        /** @var CmsPage $pageClass */
        $pageClass = app(CmsPage::class);
        /** @var CmsPagesTable $pagesTable */
        $pagesTable = app(CmsPagesTable::class);
        if (empty($currentPageId)) {
            $currentPageId = 0;
        }
        switch ($dataId) {
            case 'text_blocks_for_inserts':
                return $pagesTable::selectAssoc('id', 'title', [
                    'type' => $pageClass::getTypesWithoutUrls(),
                    'id !=' => $currentPageId,
                ]);
            case 'pages_for_inserts':
                $pages = $pagesTable::select(['id', 'url_alias', 'type', 'Parent' => ['id', 'url_alias']], [
                    'type !=' => $pageClass::getTypesWithoutUrls(),
                    'id !=' => $currentPageId,
                ]);
                $options = [];
                $typesTrans = [];
                /** @var CmsPage $pageData */
                foreach ($pages as $pageData) {
                    if (!array_key_exists($pageData->type, $typesTrans)) {
                        $typesTrans[$pageData->type] = $scaffold->translate('types', $pageData->type);
                    }
                    if (!array_key_exists($typesTrans[$pageData->type], $options)) {
                        $options[$typesTrans[$pageData->type]] = [];
                    }
                    $options[$typesTrans[$pageData->type]][$pageData->id] = $pageData->relative_url;
                }
                return $options;
            default:
                return null;
        }
    }
}