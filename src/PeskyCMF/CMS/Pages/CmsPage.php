<?php

namespace PeskyCMF\CMS\Pages;
use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\CmsRecord;
use PeskyCMF\CMS\Texts\CmsText;
use PeskyCMF\CMS\Texts\CmsTextWrapper;
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
 * @property-read CmsAdmin    $Admin
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
    const TYPE_TEXT_ELEMENT = 'text_element';
    const TYPE_MENU = 'menu';

    static protected $types = [
        self::TYPE_PAGE,
        self::TYPE_NEWS,
        self::TYPE_CATEGORY,
        self::TYPE_ITEM,
        self::TYPE_MENU,
        self::TYPE_TEXT_ELEMENT,
    ];

    /**
     * Page types that cannot be inserted as link
     * @var array
     */
    static protected $typesWithoutUrls = [
        self::TYPE_TEXT_ELEMENT,
        self::TYPE_MENU
    ];
    /** @var CmsTextWrapper */
    protected $textsWrapper;

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

    static public function getTypesWithoutUrls($asOptions = false) {
        return static::toOptions(static::$typesWithoutUrls, $asOptions, function ($value) {
            return cmfTransCustom('.pages.types.' . $value);
        }, true);
    }

    /**
     * @param bool $ignoreCache - remake CmsTextWrapper
     * @return CmsTextWrapper
     * @throws \InvalidArgumentException
     */
    public function getLocalizedText($ignoreCache = false) {
        if ($ignoreCache || $this->textsWrapper === null) {
            $locale = app()->getLocale();
            $localeRedirects = setting()->fallback_languages();
            if (is_array($localeRedirects) && !empty($localeRedirects[$locale])) {
                // replace current locale by supported one. For example - replace 'ua' locale by 'ru'
                $locale = $localeRedirects[$locale];
            }
            $this->textsWrapper = new CmsTextWrapper($this, $locale, setting()->default_language());
        }
        return $this->textsWrapper;
    }

    /**
     * Check if page is allowed to be displayed to user
     * @return bool
     */
    public function isValid() {
        return $this->existsInDb() && $this->is_published;
    }

    public function reset() {
        $this->textsWrapper = null;
        return parent::reset();
    }

}
