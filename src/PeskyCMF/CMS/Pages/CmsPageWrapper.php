<?php

namespace PeskyCMF\CMS\Pages;

use PeskyCMF\CMS\Texts\CmsTextWrapper;
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
 * @property-read string      $menu_title
 * @property-read string      $browser_title
 * @property-read string      $meta_description
 * @property-read string      $meta_keywords
 * @property-read string      $url_alias
 * @property-read string      $relative_url
 * @property-read string      $page_html_id
 * @property-read null|string $page_code
 * @property-read null|string $images
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

    /**
     * @return CmsTextWrapper
     */
    public function getTexts() {
        return $this->getPage()->getLocalizedText();
    }

    /**
     * @return string
     */
    protected function getContent() {
        return $this->getTexts()->content(true);
    }

    /**
     * @return string
     */
    protected function getMenuTitle() {
        return $this->getTexts()->menu_title();
    }

    /**
     * @return string
     */
    protected function getTitle() {
        return $this->getTexts()->title();
    }

    /**
     * @return string
     */
    protected function getBrowserTitle() {
        return $this->getTexts()->browser_title();
    }

    /**
     * @return string
     */
    protected function getMetaDescription() {
        return $this->getTexts()->meta_description();
    }

    /**
     * @return string
     */
    protected function getMetaKeywords() {
        return $this->getTexts()->meta_keywords();
    }

    /**
     * @return string
     */
    protected function getPageHtmlId() {
        return strtolower(preg_replace('%[^a-zA-Z0-9_]+%', '-', $this->getPage()->page_code ?: $this->getPage()->relative_url));
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->getPage()->isValid();
    }

    public function sendMetaTagsAndPageTitleSectionToLayout() {
        \View::startSection('meta-description', $this->getMetaDescription());
        \View::startSection('meta-keywords', $this->getMetaKeywords());
        \View::startSection('browser-title', $this->getBrowserTitle());
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        $method = 'get' . StringUtils::classify($property);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->getPage()->$property;
    }
}