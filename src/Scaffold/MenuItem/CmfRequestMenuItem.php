<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfRequestMenuItem extends CmfMenuItem
{
    
    protected string $httpMethod = 'get';
    protected array $requestData = [];
    protected string $responseDataType = 'json';
    protected ?string $onSuccess = null;
    protected bool $blockDataGrid = true;
    protected ?string $confirm = null;
    
    protected function __construct(string $url, string $httpMethod)
    {
        parent::__construct($url);
        $this->setHttpMethod($httpMethod);
    }
    
    public function getActionType(): string
    {
        return 'request';
    }
    
    /**
     * @param string $httpMethod - get|post|put|delete
     * @return static
     * @throws \InvalidArgumentException
     */
    protected function setHttpMethod(string $httpMethod)
    {
        $this->httpMethod = strtolower($httpMethod);
        if (!in_array($this->httpMethod, ['get', 'post', 'put', 'delete'], true)) {
            throw new \InvalidArgumentException(
                '$httpMethod argument must be one of: get, post, put, delete. Received: ' . $httpMethod
            );
        }
        return $this;
    }
    
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }
    
    public function getOnSuccess(): ?string
    {
        return $this->onSuccess;
    }
    
    /**
     * JS function name. This function will be called after successful response with 1 argument - response data.
     * Example: 'SomeVar.onSuccess' will be evalueated as SomeVar.onSuccess(responseData)
     * @return static
     */
    public function setOnSuccess(string $onSuccess)
    {
        $this->onSuccess = $onSuccess;
        return $this;
    }
    
    public function getRequestData(): array
    {
        return $this->requestData;
    }
    
    /**
     * Key-value array with data to send with response.
     * You can use Dot.js inserts as values using 'it' variable to access current item data.
     * Example: ['id' => '{{= it.id }}']
     * @return static
     */
    public function setRequestData(array $requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }
    
    public function makeRequestDataForHtmlAttribute(): ?string
    {
        if (empty($this->requestData)) {
            return null;
        } else {
            return http_build_query($this->requestData, '', '&', PHP_QUERY_RFC1738);
        }
    }
    
    public function getResponseDataType(): string
    {
        return $this->responseDataType;
    }
    
    /**
     * @param string $responseDataType - json|text|html
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setResponseDataType(string $responseDataType)
    {
        $this->responseDataType = strtolower($responseDataType);
        if (!in_array($this->responseDataType, ['json', 'text', 'html'], true)) {
            throw new \InvalidArgumentException(
                '$responseDataType argument must be one of: json, text, html. Recieved: ' . $responseDataType
            );
        }
        return $this;
    }
    
    public function isBlockDataGrid(): bool
    {
        return $this->blockDataGrid;
    }
    
    /**
     * Should current data grid be blocked until request's responce received or not?
     * @return static
     */
    public function setBlockDataGrid(bool $blockDataGrid)
    {
        $this->blockDataGrid = $blockDataGrid;
        return $this;
    }
    
    public function getConfirm(): ?string
    {
        return $this->confirm;
    }
    
    /**
     * Request confirmation of action.
     * @param string $confirm - message (question) to display to user
     * @return static
     */
    public function setConfirm(string $confirm)
    {
        $this->confirm = $confirm;
        return $this;
    }
    
    protected function modifyTagBeforeRendering(Tag $tag): Tag
    {
        return $tag;
    }
    
    /**
     * Render menu item as <button>
     */
    public function renderAsButton(bool $withIcon = true): string
    {
        if ($this->isAccessible()) {
            $button = Tag::button(($withIcon ? $this->makeIcon() . ' ' : '') . $this->getTitle())
                ->setClass($this->getButtonClasses())
                ->setType('button')
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
     */
    public function renderAsIcon(string $additionalClasses = '', bool $allowIconColorClass = true): string
    {
        if ($this->isAccessible()) {
            $button = Tag::a($this->makeIcon($allowIconColorClass))
                ->addCustomRenderedAttributeWithValue($this->makeConditionalDisabledAttribute())
                ->setTitle($this->getTooltipOrTitle())
                ->setHref('javascript: void(0)')
                ->setClass($additionalClasses)
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
     * Render menu item as <li><a>...</a></li>
     */
    public function renderAsBootstrapDropdownMenuItem(): string
    {
        if ($this->isAccessible()) {
            $button = Tag::a($this->makeIcon(true) . ' ' . $this->getTitle())
                ->setHref('javascript: void(0)')
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