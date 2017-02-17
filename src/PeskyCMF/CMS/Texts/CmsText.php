<?php

namespace PeskyCMF\CMS\Texts;

use PeskyCMF\CMS\CmsRecord;

/**
 * @property-read int         $id
 * @property-read null|int    $parent_id
 * @property-read null|int    $admin_id
 * @property-read string      $language
 * @property-read string      $title
 * @property-read string      $browser_title
 * @property-read string      $menu_title
 * @property-read string      $comment
 * @property-read null|string $content
 * @property-read string      $meta_description
 * @property-read string      $meta_keywords
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
 * @method $this    setLanguage($value, $isFromDb = false)
 * @method $this    setTitle($value, $isFromDb = false)
 * @method $this    setBrowserTitle($value, $isFromDb = false)
 * @method $this    setMenuTitle($value, $isFromDb = false)
 * @method $this    setComment($value, $isFromDb = false)
 * @method $this    setContent($value, $isFromDb = false)
 * @method $this    setMetaDescription($value, $isFromDb = false)
 * @method $this    setMetaKeywords($value, $isFromDb = false)
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setUpdatedAt($value, $isFromDb = false)
 * @method $this    setCustomInfo($value, $isFromDb = false)
 */
class CmsText extends CmsRecord {

    /**
     * @return CmsTextsTable
     */
    static public function getTable() {
        return CmsTextsTable::getInstance();
    }

}
