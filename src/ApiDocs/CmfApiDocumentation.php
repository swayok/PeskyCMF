<?php

declare(strict_types=1);

namespace PeskyCMF\ApiDocs;

use Illuminate\Support\Str;
use PeskyCMF\Config\CmfConfig;

/**
 * Extend this class to show description for some topic that is not an API method (like wiki page)
 */
abstract class CmfApiDocumentation
{
    
    // override next properties and methods
    
    /**
     * Position of this method within the group.
     * Used only by CmfConfig::loadApiMethodsDocumentationClassesFromFileSystem().
     * @var int|null
     */
    protected static ?int $position = null;
    
    /**
     * Base path to translations for current api method documentation
     * Mostly used to get descriptions for headers, url params, url query params, post params and errors
     * Format: 'group', 'group.method', 'user.details'
     * @var string
     */
    protected string $translationsBasePath = '';
    
    /**
     * You can use simple string or translation path in format: '{method.some_name.title}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * When null: $this->translationsBasePath . '.title' will be used
     * @var string|null
     */
    protected ?string $title = null;
    
    /**
     * You can use simple string or translation path in format: '{method.some_name.description}'
     * Note that translation path will be passed to CmfConfig::transCustom() so you do not need to add dictionary name
     * to translation path - it will be added automatically using CmfConfig::getPrimary()->custom_dictionary_name().
     * Resulting path will be: 'admin.api_docs.method.some_name.title' if dictionary name is 'admin'
     * When null: $this->translationsBasePath . '.description' will be used
     * @var string|null
     */
    protected ?string $description = null;
    
    protected string $uuid;
    
    protected CmfConfig $cmfConfig;
    
    public static function create(CmfConfig $cmfConfig): static
    {
        return new static($cmfConfig);
    }
    
    public function __construct(CmfConfig $cmfConfig)
    {
        $this->cmfConfig = $cmfConfig;
        $this->uuid = 'doc-' . Str::snake(str_replace('\\', '', get_class($this)), '-');
    }
    
    public function getCmfConfig(): CmfConfig
    {
        return $this->cmfConfig;
    }
    
    public function getErrors(): array
    {
        return [];
    }
    
    public static function getPosition(): ?int
    {
        return static::$position;
    }
    
    public function getTitle(): string
    {
        return $this->title
            ? $this->translateInserts($this->title)
            : $this->translatePath(rtrim($this->translationsBasePath, '.') . '.title');
    }
    
    public function getDescription(): string
    {
        return $this->description
            ? $this->translateInserts($this->description)
            : $this->translatePath(rtrim($this->translationsBasePath, '.') . '.description');
    }
    
    public function hasDescription(): bool
    {
        return trim(preg_replace('%</?[^>]+>%', '', $this->getDescription())) !== '';
    }
    
    public function getUuid(): string
    {
        return $this->uuid;
    }
    
    public function getUrl(): string
    {
        return '';
    }
    
    public function getHttpMethod(): string
    {
        return '';
    }
    
    public function getHeaders(): array
    {
        return [];
    }
    
    public function getUrlParameters(): array
    {
        return [];
    }
    
    public function getUrlQueryParameters(): array
    {
        return [];
    }
    
    public function getPostParameters(): array
    {
        return [];
    }
    
    public function getValidationErrors(): array
    {
        return [];
    }
    
    public function getOnSuccessData(): array
    {
        return [];
    }
    
    /**
     * Translate blocks like "{method.name.title}" placed inside the $string
     */
    protected function translateInserts(string $text): string
    {
        return preg_replace_callback(
            '%\{([^{}]*)}%',
            function ($matches) {
                return $this->translatePath($matches[1]);
            },
            $text
        );
    }
    
    protected function translatePath(string $path): array|string
    {
        return $this->cmfConfig->transApiDoc($path);
    }
    
    public function isMethodDocumentation(): bool
    {
        return false;
    }
    
    public function getConfigForPostman(): ?array
    {
        return null;
    }
    
}
