<?php

namespace PeskyCMF\ApiDocs;

use PeskyCMF\Config\CmfConfig;

/**
 * Extend this class to show description for some topic that is not an API method (like wiki page)
 */
abstract class CmfApiDocumentation {

    // override next properties and methods

    /**
     * Position of this method within the group.
     * Used only by CmfConfig::loadApiMethodsDocumentationClassesFromFileSystem().
     * @var int|null
     */
    static protected $position;

    /**
     * You can use simple string or translation path in format: '{method.some_name.title}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * @var string
     */
    protected $title = '';

    /**
     * You can use simple string or translation path in format: '{method.some_name.description}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * @var string
     */
    protected $description = <<<HTML

HTML;

    /**
     * @return array
     */
    public function getErrors() {
        return [];
    }

    static public function create() {
        return new static();
    }

    protected $uuid;

    public function __construct() {
        $this->uuid = 'doc-' . snake_case(str_replace('\\', '', get_class($this)), '-');
    }

    static public function getPosition() {
        return static::$position;
    }

    public function getTitle() {
        return $this->translateInserts($this->title);
    }

    public function getDescription() {
        return $this->description ? $this->translateInserts($this->description) : '';
    }

    public function hasDescription() {
        return trim(preg_replace('%</?[^>]+>%', '', $this->getDescription())) !== '';
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getUrl() {
        return '';
    }

    public function getHttpMethod() {
        return '';
    }

    public function getHeaders() {
        return [];
    }

    public function getUrlParameters() {
        return [];
    }

    public function getUrlQueryParameters() {
        return [];
    }

    public function getPostParameters() {
        return [];
    }

    public function getValidationErrors() {
        return [];
    }

    public function getOnSuccessData() {
        return [];
    }

    /**
     * Translate blocks like "{method.name.title}" placed inside the $string
     * @param string $text
     * @return string
     */
    protected function translateInserts(string $text) {
        return preg_replace_callback(
            '%\{([^{}]*)\}%',
            function ($matches) {
                return $this->translatePath($matches[1]);
            },
            $text
        );
    }

    protected function translatePath(string $path) {
        return CmfConfig::transApiDoc($path);
    }

    public function isMethodDocumentation() {
        return false;
    }

    public function getConfigForPostman() {
        return null;
    }

}
