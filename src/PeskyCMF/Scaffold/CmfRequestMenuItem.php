<?php

namespace PeskyCMF\Scaffold;

use Swayok\Html\Tag;

class CmfRequestMenuItem extends CmfMenuItem {

    /** @var string */
    protected $httpMethod = 'get';
    /** @var array */
    protected $requestData = [];
    /** @var string */
    protected $responseDataType = 'json';
    /** @var string|null */
    protected $onSuccess;
    /** @var bool */
    protected $blockDataGrid = true;
    /** @var string|null */
    protected $confirm;

    /**
     * CmfRequestMenuItem constructor.
     * @param $url
     * @param $httpMethod
     * @throws \InvalidArgumentException
     */
    protected function __construct(string $url, string $httpMethod) {
        parent::__construct($url);
        $this->setHttpMethod($httpMethod);
    }

    /**
     * @return string
     */
    public function getActionType() {
        return 'request';
    }

    /**
     * @param string $httpMethod
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function setHttpMethod(string $httpMethod) {
        $this->method = strtolower($httpMethod);
        if (!in_array($this->httpMethod, ['get', 'post', 'put', 'delete'], true)) {
            throw new \InvalidArgumentException(
                '$httpMethod argument must be one of: get, post, put, delete. Received: ' . $httpMethod
            );
        }
        return $this;
    }

    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    /**
     * @return string|null
     */
    public function getOnSuccess() {
        return $this->onSuccess;
    }

    /**
     * JS function name. This function will be called after successful response with 1 argument - response data.
     * Example: 'SomeVar.onSuccess' will be evalueated as SomeVar.onSuccess(responseData)
     * @param string $onSuccess
     * @return $this
     */
    public function setOnSuccess(string $onSuccess) {
        $this->onSuccess = $onSuccess;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequestData(): array {
        return $this->requestData;
    }

    /**
     * Key-value array with data to send with response.
     * You can use Dot.js inserts as values using 'it' variable to access current item data.
     * Example: ['id' => '{{= it.id }}']
     * @param array $requestData
     * @return $this
     */
    public function setRequestData(array $requestData) {
        $this->requestData = $requestData;
        return $this;
    }

    /**
     * @return null|string
     */
    public function makeRequestDataForHtmlAttribute() {
        if (empty($this->requestData)) {
            return null;
        } else {
            return http_build_query($this->requestData, null, '&', PHP_QUERY_RFC1738);
        }
    }

    /**
     * @return string
     */
    public function getResponseDataType(): string {
        return $this->responseDataType;
    }

    /**
     * @param string $responseDataType
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setResponseDataType($responseDataType) {
        $this->responseDataType = strtolower($responseDataType);
        if (!in_array($this->responseDataType, ['json', 'text', 'html'], true)) {
            throw new \InvalidArgumentException(
                '$responseDataType argument must be one of: json, text, html. Recieved: ' . $responseDataType
            );
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isBlockDataGrid(): bool {
        return $this->blockDataGrid;
    }

    /**
     * Should current data grid be blocked until request's responce received or not?
     * @param bool $blockDataGrid
     * @return $this
     */
    public function setBlockDataGrid($blockDataGrid) {
        $this->blockDataGrid = $blockDataGrid;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getConfirm() {
        return $this->confirm;
    }

    /**
     * Request confirmation of action.
     * @param string $confirm - message (question) to display to user
     * @return $this
     */
    public function setConfirm(string $confirm) {
        $this->confirm = $confirm;
        return $this;
    }

    /**
     * @param Tag $tag
     * @return Tag
     */
    protected function modifyTagBeforeRendering(Tag $tag) {
        return $tag;
    }

    /**
     * Render menu item as <button>
     * @return string
     */
    public function renderAsButton(): string {
        if ($this->isAccessible()) {
            $button = Tag::button($this->makeIcon() . ' ' . $this->getTitle())
                ->setClass($this->getButtonClasses())
                ->addCustomRenderedAttributeWithValue($this->makeConditionalDisabledAttribute())
                ->setTitle($this->getTooltip())
                ->setDataAttr('toggle', $this->hasTooltip() ? 'tooltip' : null)
                ->setDataAttr('position', $this->hasTooltip() ? $this->getTooltipPosition() : null)
                ->setDataAttr('action', $this->getActionType())
                ->setDataAttr('url', $this->getUrl())
                ->setDataAttr('method', $this->getHttpMethod())
                ->setDataAttr('data', $this->makeRequestDataForHtmlAttribute())
                ->setDataAttr('response-type', $this->getResponseDataType())
                ->setDataAttr('confirm', $this->getConfirm())
                ->setDataAttr('on-success', $this->getOnSuccess())
                ->setDataAttr('block-datagrid', $this->isBlockDataGrid() ? '1' : null);
            return $this->wrapIntoShowCondition($this->modifyTagBeforeRendering($button)->build());
        } else {
            return '';
        }
    }

    /**
     * Render menu item as <a> icon (title will be used as tooltip)
     * @return string
     */
    public function renderAsIcon(): string {
        if ($this->isAccessible()) {
            $button = Tag::a($this->makeIcon())
                ->addCustomRenderedAttributeWithValue($this->makeConditionalDisabledAttribute())
                ->setTitle($this->getTooltipOrTitle())
                ->setHref('javascript: void(0)')
                ->setDataAttr('toggle', $this->hasTooltipOrTitle() ? 'tooltip' : null)
                ->setDataAttr('position', $this->hasTooltipOrTitle() ? $this->getTooltipPosition() : null)
                ->setDataAttr('action', $this->getActionType())
                ->setDataAttr('url', $this->getUrl())
                ->setDataAttr('method', $this->getHttpMethod())
                ->setDataAttr('data', $this->makeRequestDataForHtmlAttribute())
                ->setDataAttr('response-type', $this->getResponseDataType())
                ->setDataAttr('confirm', $this->getConfirm())
                ->setDataAttr('on-success', $this->getOnSuccess())
                ->setDataAttr('block-datagrid', $this->isBlockDataGrid() ? '1' : null);
            return $this->wrapIntoShowCondition($this->modifyTagBeforeRendering($button)->build());
        } else {
            return '';
        }
    }

    /**
     * Render menu item as <li><a>...</a></li> or <li><button>...</button></li>
     * @return string
     */
    public function renderAsBootstrapDropdownMenuButton(): string {
        if ($this->isAccessible()) {
            $button = Tag::a($this->makeIcon() . ' ' . $this->getTitle())
                ->setHref('javascript: void(0)')
                ->setClass($this->getButtonClasses())
                ->addCustomRenderedAttributeWithValue($this->makeConditionalDisabledAttribute())
                ->setTitle($this->getTooltip())
                ->setDataAttr('toggle', $this->hasTooltip() ? 'tooltip' : null)
                ->setDataAttr('position', $this->hasTooltip() ? $this->getTooltipPosition() : null)
                ->setDataAttr('action', $this->getActionType())
                ->setDataAttr('url', $this->getUrl())
                ->setDataAttr('method', $this->getHttpMethod())
                ->setDataAttr('data', $this->makeRequestDataForHtmlAttribute())
                ->setDataAttr('response-type', $this->getResponseDataType())
                ->setDataAttr('confirm', $this->getConfirm())
                ->setDataAttr('on-success', $this->getOnSuccess())
                ->setDataAttr('block-datagrid', $this->isBlockDataGrid() ? '1' : null);
            return $this->wrapIntoShowCondition(
                '<li ' . $this->makeConditionalDisabledAttribute() . '>'
                    . $this->modifyTagBeforeRendering($button)->build()
                . '</li>'
            );
        } else {
            return '';
        }
    }
}