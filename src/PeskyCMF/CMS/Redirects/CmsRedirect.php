<?php

namespace PeskyCMF\CMS\Redirects;

use PeskyCMF\CMS\Admins\CmsAdmin;
use PeskyCMF\CMS\CmsRecord;
use PeskyCMF\CMS\Pages\CmsPage;

/**
 * @property-read int         $id
 * @property-read null|int    $page_id
 * @property-read null|int    $admin_id
 * @property-read string      $relative_url
 * @property-read string      $is_permanent
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read string      $updated_at
 * @property-read string      $updated_at_as_date
 * @property-read string      $updated_at_as_time
 * @property-read int         $updated_at_as_unix_ts
 * @property-read CmsAdmin    $Admin
 * @property-read CmsPage     $Page
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setPageId($value, $isFromDb = false)
 * @method $this    setAdminId($value, $isFromDb = false)
 * @method $this    setRelativeUrl($value, $isFromDb = false)
 * @method $this    setIsPermanent($value, $isFromDb = false)
 * @method $this    setCreatedAt($value, $isFromDb = false)
 * @method $this    setUpdatedAt($value, $isFromDb = false)
 */
class CmsRedirect extends CmsRecord {

    /**
     * @return CmsRedirectsTable
     */
    static public function getTable() {
        return app(CmsRedirectsTable::class);
    }

}
