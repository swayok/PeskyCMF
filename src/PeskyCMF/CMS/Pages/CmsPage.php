<?php

namespace PeskyCMF\CMS\Pages;
use PeskyCMF\CMS\CmsRecord;
use PeskyCMF\CMS\Texts\CmsText;
use PeskyORM\ORM\RecordsSet;

/**
 * @property-read int         $id
 * @property-read null|int    $parent_id
 * @property-read null|int    $admin_id
 * @property-read string      $type
 * @property-read string      $title
 * @property-read string      $comment
 * @property-read string      $url_alias
 * @property-read string      $relative_url
 * @property-read null|string $page_code
 * @property-read null|string $images
 * @property-read string      $meta_description
 * @property-read string      $meta_keywords
 * @property-read null|int    $order
 * @property-read string      $with_contact_form
 * @property-read string      $is_published
 * @property-read string      $publish_at
 * @property-read string      $publish_at_as_date
 * @property-read string      $publish_at_as_time
 * @property-read int         $publish_at_as_unix_ts
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read string      $updated_at
 * @property-read string      $updated_at_as_date
 * @property-read string      $updated_at_as_time
 * @property-read int         $updated_at_as_unix_ts
 * @property-read string      $custom_info
 * @property-read CmsPage     $Parent
 * @property-read CmsText[]|RecordsSet   $Texts
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setParentId($value, $isFromDb = false)
 * @method $this    setAdminId($value, $isFromDb = false)
 * @method $this    setType($value, $isFromDb = false)
 * @method $this    setTitle($value, $isFromDb = false)
 * @method $this    setComment($value, $isFromDb = false)
 * @method $this    setUrlAlias($value, $isFromDb = false)
 * @method $this    setPageCode($value, $isFromDb = false)
 * @method $this    setImages($value, $isFromDb = false)
 * @method $this    setMetaDescription($value, $isFromDb = false)
 * @method $this    setMetaKeywords($value, $isFromDb = false)
 * @method $this    setOrder($value, $isFromDb = false)
 * @method $this    setWithContactForm($value, $isFromDb = false)
 * @method $this    setIsPublished($value, $isFromDb = false)
 * @method $this    setPublishAt($value, $isFromDb = false)
 * @method $this    setCustomInfo($value, $isFromDb = false)
 */
class CmsPage extends CmsRecord {

    const TYPE_PAGE = 'page';
    const TYPE_CATEGORY = 'category';
    const TYPE_ITEM = 'item';
    const TYPE_NEWS = 'news';

    static protected $types = [
        self::TYPE_PAGE,
        self::TYPE_NEWS,
        self::TYPE_CATEGORY,
        self::TYPE_ITEM,
    ];

    /**
     * @return CmsPagesTable
     */
    static public function getTable() {
        return app(CmsPagesTable::class);
    }

    static public function getTypes($asOptions = false) {
        return static::toOptions(static::$types, $asOptions, function ($value) {
            return cmfTransCustom('.pages.types.' . $value);
        }, true);
    }

}
