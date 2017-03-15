<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\CmsTableStructure;
use PeskyCMF\CMS\Texts\CmsTextsTable;
use PeskyCMF\CMS\Traits\AdminIdColumn;
use PeskyCMF\Db\Column\ImagesColumn;
use PeskyCMF\Db\Traits\IdColumn;
use PeskyCMF\Db\Traits\IsPublishedColumn;
use PeskyCMF\Db\Traits\TimestampColumns;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordValue;
use PeskyORM\ORM\Relation;

/**
 * @property-read Column    $id
 * @property-read Column    $parent_id
 * @property-read Column    $admin_id
 * @property-read Column    $type
 * @property-read Column    $title
 * @property-read Column    $comment
 * @property-read Column    $url_alias
 * @property-read Column    $page_code
 * @property-read ImagesColumn    $images
 * @property-read Column    $meta_description
 * @property-read Column    $meta_keywords
 * @property-read Column    $order
 * @property-read Column    $with_contact_form
 * @property-read Column    $is_published
 * @property-read Column    $publish_at
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

    private function comment() {
        return Column::create(Column::TYPE_TEXT)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function title() {
        return Column::create(Column::TYPE_STRING)
            ->disallowsNullValues()
            ->setDefaultValue('');
    }

    private function url_alias() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->convertsEmptyStringToNull();
    }

    private function relative_url() {
        return Column::create(Column::TYPE_STRING)
            ->doesNotExistInDb()
            ->valueCannotBeSetOrChanged()
            ->setValueExistenceChecker(function (RecordValue $value) {
                return $value->getRecord()->existsInDb();
            })
            ->setValueGetter(function (RecordValue $value, $format = null) {
                $baseUrl = '';
                /** @var CmsPage $record */
                $record = $value->getRecord();
                if (
                    (
                        $record->hasValue('parent_id', false)
                        && $record->parent_id !== null
                    )
                    || (
                        $record->isRelatedRecordAttached('Parent')
                        && $record->Parent->existsInDb()
                    )
                ) {
                    $baseUrl = $record->Parent->relative_url;
                }
                return $baseUrl . $record->url_alias;
            });
    }

    private function page_code() {
        return Column::create(Column::TYPE_STRING)
            ->uniqueValues()
            ->convertsEmptyStringToNull();
    }

    private function publish_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->disallowsNullValues()
            ->setDefaultValue(function () {
                /** @var CmsPagesTable $pagesTable */
                $pagesTable = app(CmsPagesTable::class);
                return $pagesTable::getCurrentTimeDbExpr();
            });
    }

    private function images() {
        $column = ImagesColumn::create()
            ->setRelativeUploadsFolderPath('assets/pages');
        $this->configureImages($column);
        return $column;
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
            ->setDisplayColumnName('url_alias');
    }

    private function Texts() {
        return Relation::create('id', Relation::HAS_MANY, app(CmsTextsTable::class), 'page_id')
            ->setDisplayColumnName('title');
    }

    protected function configureImages(ImagesColumn $column) {

    }

}
