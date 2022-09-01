<?php

declare(strict_types=1);

namespace PeskyCMF\ApiDocs;

class ApiMethodErrorResponseInfo
{
    
    protected int $httpCode = 500;
    protected string $title = 'No title provided';
    protected string $description = '';
    protected array $response = [];
    protected array $extraData = [];
    
    public static function create(?int $httpCode = null): static
    {
        return new static($httpCode);
    }
    
    public function __construct(?int $httpCode = null)
    {
        if ($httpCode !== null) {
            $this->setHttpCode($httpCode);
        }
    }
    
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
    
    public function setHttpCode(int $httpCode): static
    {
        $this->httpCode = $httpCode;
        return $this;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function setTitle(string $description): static
    {
        $this->title = $description;
        return $this;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
    
    public function getResponse(): array
    {
        return $this->response;
    }
    
    public function setResponse(array $response): static
    {
        $this->response = $response;
        return $this;
    }
    
    /**
     * Additional data to be added to response info
     */
    public function setExtraData(array $data): static
    {
        $this->extraData = $data;
        return $this;
    }
    
    public function getExtraData(): array
    {
        return $this->extraData;
    }
    
    public function toArray(): array
    {
        $ret = [
            'code' => $this->getHttpCode(),
            'title' => $this->getTitle(),
            'response' => $this->getResponse(),
            'extra' => $this->getExtraData(),
            'description' => $this->getDescription(),
        ];
        if (empty($ret['extra'])) {
            unset($ret['extra']);
        }
        return $ret;
    }
    
    
}
