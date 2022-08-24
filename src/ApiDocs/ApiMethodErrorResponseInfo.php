<?php

declare(strict_types=1);

namespace PeskyCMF\ApiDocs;

class ApiMethodErrorResponseInfo
{
    
    /**
     * @var int
     */
    protected $httpCode = 500;
    /**
     * @var string
     */
    protected $title = 'No title provided';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var array
     */
    protected $response = [];
    /**
     * @var array
     */
    protected $extraData = [];
    
    /**
     * @return static
     */
    public static function create(?int $httpCode = null)
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
    
    /**
     * @return static
     */
    public function setHttpCode(int $httpCode)
    {
        $this->httpCode = $httpCode;
        return $this;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * @return static
     */
    public function setTitle(string $description)
    {
        $this->title = $description;
        return $this;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * @return static
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }
    
    public function getResponse(): array
    {
        return $this->response;
    }
    
    /**
     * @return static
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
        return $this;
    }
    
    /**
     * Additional data to be added to response info
     * @return static
     */
    public function setExtraData(array $data)
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
