<?php

namespace PeskyCMF\CMS\Pages;

use PeskyORM\ORM\RecordInterface;
use Swayok\Utils\StringUtils;

/**
 * @property-read int         $id
 * @property-read null|int    $parent_id
 * @property-read null|int    $admin_id
 * @property-read string      $type
 * @property-read string      $title
 * @property-read string      $comment
 * @property-read string      $content
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
 * @property-read CmsPage     $Page
 */
class CmsPageWrapper {

    /** @var CmsPage */
    protected $page;

    /**
     * CmsPageWrapper constructor.
     * @param CmsPage|RecordInterface $page
     */
    public function __construct(RecordInterface $page) {
        $this->page = $page;
    }

    /**
     * @return CmsPage|RecordInterface
     */
    public function getPage() {
        return $this->page;
    }

    public function getContent() {
        // todo: get best fitting Text record using current app language and default app language
    }

    public function __get($property) {
        $method = 'get' . StringUtils::classify($property);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->page->$property;
    }
}