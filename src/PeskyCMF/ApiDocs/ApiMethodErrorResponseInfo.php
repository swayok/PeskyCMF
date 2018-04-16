<?php

namespace PeskyCMF\ApiDocs;

class ApiMethodErrorResponseInfo {

    /**
     * @var int
     */
    protected $httpCode = 500;
    /**
     * @var string
     */
    protected $description = 'No description provided';
    /**
     * @var array
     */
    protected $response = [];
    /**
     * @var array
     */
    protected $extraData = [];

    /**
     * @param int $httpCode
     * @return static
     */
    static public function create($httpCode = null) {
        return new static($httpCode);
    }

    /**
     * ApiMethodErrorResponseInfo constructor.
     * @param int $httpCode
     */
    public function __construct($httpCode = null) {
        if ($httpCode !== null) {
            $this->setHttpCode($httpCode);
        }
    }

    /**
     * @return int
     */
    public function getHttpCode(): int {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     * @return $this
     */
    public function setHttpCode($httpCode) {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponse(): array {
        return $this->response;
    }

    /**
     * @param array $response
     * @return $this
     */
    public function setResponse(array $response) {
        $this->response = $response;
        return $this;
    }

    /**
     * Additional data to be added to response info
     * @param array $data
     * @return $this
     */
    public function setExtraData(array $data) {
        $this->extraData = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraData() {
        return $this->extraData;
    }

    /**
     * @return array
     */
    public function toArray() {
        $ret = [
            'code' => $this->getHttpCode(),
            'title' => $this->getDescription(),
            'response' => $this->getResponse(),
            'extra' => $this->getExtraData()
        ];
        if (empty($ret['extra'])) {
            unset($ret['extra']);
        }
        return $ret;
    }



}