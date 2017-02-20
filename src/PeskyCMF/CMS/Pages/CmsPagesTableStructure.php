<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTableStructure;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\CMS\Traits\AdminIdColumn;
use PeskyCMF\Db\Column\ImagesColumn;
use PeskyCMF\Db\Column\Utils\ImageConfig;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsPublishedColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

/**
 * @property-read Column    $id
 * @property-read Column    $parent_id
 * @property-read Column    $admin_id
 * @property-read Column    $text_id
 * @property-read Column    $type
 * @property-read Column    $comment
 * @property-read Column    $url_alias
 * @property-read Column    $page_code
 * @property-read Column    $images
 * @property-read Column    $meta_description
 * @property-read Column    $meta_keywords
 * @property-read Column    $order
 * @property-read Column    $with_contact_form
 * @property-read Column    $is_published
 * @property-read Column    $created_at
 * @property-read Column    $updated_at
 * @property-read Column    $custom_info
 */
class CmsPagesTableStructure extends CmsTableStructure {

    use IdColumn,
        AdminIdColumn,
        IsPublishedColumn,
        TimestampColumns;

    /**
     * @return string
     */
    static public function getTableName() {
        return 'pages';
    }

    private function type() {
        /** @var CmsPage $page */
        $page = app()->offsetGet(CmsPage::class);
        return Column::create(Column::TYPE_ENUM)
            ->disallowsNullValues()
            ->setAllowedValues($page::getTypes())
            ->setDefaultValue($page::TYPE_PAGE);
    }

    private function parent_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function text_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function comment() {
        return Column::create(Column::TYPE_TEXT)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function url_alias() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->convertsEmptyStringToNull();
    }

    private function page_code() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->convertsEmptyStringToNull();
    }

    private function images() {
        return ImagesColumn::create()
            ->setRelativeUploadsFolderPath('assets/pages')
            ->addImageConfiguration('resize', function (ImageConfig $config) {
                $config
                    ->setMinFilesCount(1)
                    ->setMaxWidth(500)
                    ->setAllowedFileTypes([$config::JPEG]);
            })
            ->addImageConfiguration('cover', function (ImageConfig $config) {
                $config
                    ->setMaxWidth(500)
                    ->setAllowedFileTypes([$config::JPEG]);
            })
            ->addImageConfiguration('contain', function (ImageConfig $config) {
                $config
                    ->setMaxFilesCount(3)
                    ->setMaxWidth(500)
                    ->setAllowedFileTypes([$config::PNG]);
            })
            ->addImageConfiguration('aspect', function (ImageConfig $config) {
                $config
                    ->setMaxWidth(500)
                    ->setAspectRatio(4, 3)
                    ->setAllowedFileTypes([$config::JPEG]);
            });
    }

    private function meta_description() {
        return Column::create(Column::TYPE_TEXT)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function meta_keywords() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function order() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function with_contact_form() {
        return Column::create(Column::TYPE_BOOL)
            ->disallowsNullValues()
            ->setDefaultValue(false);
    }

    private function custom_info() {
        return Column::create(Column::TYPE_JSONB)
            ->disallowsNullValues()
            ->setDefaultValue('{}');
    }

    private function Parent() {
        return Relation::create('parent_id', Relation::BELONGS_TO, app(CmsPagesTable::class), 'id')
            ->setDisplayColumnName('title');
    }

    private function PrimaryText() {
        return Relation::create('text_id', Relation::BELONGS_TO, app(CmsTextsTable::class), 'id')
            ->setDisplayColumnName('title');
    }

}
