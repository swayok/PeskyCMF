<?php

namespace PeskyCMF\CMS\Pages;
use PeskyCMF\CMS\CmsRecord;

/**
 * @property-read int         $id
 * @property-read null|int    $parent_id
 * @property-read null|int    $admin_id
 * @property-read null|int    $text_id
 * @property-read string      $type
 * @property-read string      $comment
 * @property-read null|string $url_alias
 * @property-read null|string $page_code
 * @property-read null|string $images
 * @property-read string      $meta_description
 * @property-read string      $meta_keywords
 * @property-read null|int    $order
 * @property-read string      $with_contact_form
 * @property-read string      $is_published
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read string      $updated_at
 * @property-read string      $updated_at_as_date
 * @property-read string      $updated_at_as_time
 * @property-read int         $updated_at_as_unix_ts
 * @property-read string      $custom_info
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setParentId($value, $isFromDb = false)
 * @method $this    setAdminId($value, $isFromDb = false)
 * @method $this    setTextId($value, $isFromDb = false)
 * @method $this    setType($value, $isFromDb = false)
 * @method $this    setComment($value, $isFromDb = false)
 * @method $this    setUrlAlias($value, $isFromDb = false)
 * @method $this    setPageCode($value, $isFromDb = false)
 * @method $this    setImages($value, $isFromDb = false)
 * @method $this    setMetaDescription($value, $isFromDb = false)
 * @method $this    setMetaKeywords($value, $isFromDb = false)
 * @method $this    setOrder($value, $isFromDb = false)
 * @method $this    setWithContactForm($value, $isFromDb = false)
 * @method $this    setIsPublished($value, $isFromDb = false)
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setUpdatedAt($value, $isFromDb = false)
 * @method $this    setCustomInfo($value, $isFromDb = false)
 */
class CmsPage extends CmsRecord {

    const TYPE_PAGE = 'page';
    const TYPE_NEWS = 'news';

    static protected $types = [
        self::TYPE_PAGE,
        self::TYPE_NEWS,
    ];

    /**
     * @return CmsPagesTable
     */
    static public function getTable() {
        return app(CmsPagesTable::class);
    }

    static public function getTypes($asOptions = false) {
        return static::toOptions(static::$types, $asOptions, static::getCmsConfig()->custom_dictionary_name() . '.pages.types.', true);
    }

}