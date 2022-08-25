<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\MenuItem;

abstract class CmfMenuItem
{
    
    protected string $url;
    protected string $title = '';
    protected string $iconClasses = '';
    protected string $iconColorClass = '';
    protected string $buttonClasses = 'btn btn-default';
    protected ?string $conditionToDisable = null;
    protected ?string $conditionToShow = null;
    protected ?\Closure $accessProvider = null;
    protected ?string $tooltip = null;
    protected string $tooltipPosition = 'top';
    protected ?bool $openInModal = null;
    
    public static function redirect(string $url): CmfRedirectMenuItem
    {
        return new CmfRedirectMenuItem($url);
    }
    
    public static function request(string $url, string $method = 'post'): CmfRequestMenuItem
    {
        return new CmfRequestMenuItem($url, $method);
    }
    
    public static function bulkActionOnSelectedRows(string $url, string $method = 'post'): CmfBulkActionMenuItem
    {
        return new CmfBulkActionMenuItem($url, $method, CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED);
    }
    
    public static function bulkActionOnFilteredRows(string $url, string $method = 'post'): CmfBulkActionMenuItem
    {
        return new CmfBulkActionMenuItem($url, $method, CmfBulkActionMenuItem::ACTION_TYPE_BULK_FILTERED);
    }
    
    public static function bulkActionRedirectOnSelectedRows(string $url): CmfBulkActionRedirectMenuItem
    {
        return new CmfBulkActionRedirectMenuItem($url, CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED);
    }
    
    public static function bulkActionRedirectOnFilteredRows(string $url): CmfBulkActionRedirectMenuItem
    {
        return new CmfBulkActionRedirectMenuItem($url, CmfBulkActionMenuItem::ACTION_TYPE_BULK_FILTERED);
    }
    
    /**
     * Render menu item as <a> or <button>
     * @param bool $withIcon
     * @return string
     */
    abstract public function renderAsButton(bool $withIcon = true): string;
    
    /**
     * Render menu item as <a> icon (title will be used as tooltip)
     * @param string $additionalClasses - classes to add to <a> tag
     * @param bool $allowIconColorClass
     * @return string
     */
    abstract public function renderAsIcon(string $additionalClasses = '', bool $allowIconColorClass = true): string;
    
    /**
     * Render menu item as <li><a>...</a></li>
     * @return string
     */
    abstract public function renderAsBootstrapDropdownMenuItem(): string;
    
    protected function __construct(string $url)
    {
        $this->url = $url;
    }
    
    public function getUrl(): string
    {
        return $this->url;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function hasTitle(): bool
    {
        return !empty($this->title);
    }
    
    public function getIconClasses(): string
    {
        return $this->iconClasses;
    }
    
    /**
     * @return static
     */
    public function setIconClasses(string $iconClasses)
    {
        $this->iconClasses = $iconClasses;
        return $this;
    }
    
    public function getIconColorClass(): string
    {
        return ' ' . $this->iconColorClass . ' ';
    }
    
    /**
     * Used in renderAsIcon() and renderAsBootstrapDropdownMenuButton() to alter icon color
     * @param string $iconColorClass - example: 'text-primary'
     * @return static
     */
    public function setIconColorClass(string $iconColorClass)
    {
        $this->iconColorClass = $iconColorClass;
        return $this;
    }
    
    public function makeIcon(bool $addColorClass = false): string
    {
        if ($this->iconClasses) {
            return '<i class="' . $this->iconClasses . ' ' . ($addColorClass ? $this->getIconColorClass() : '') . '"></i>';
        }
        return '';
    }
    
    public function getButtonClasses(): string
    {
        return $this->buttonClasses;
    }
    
    /**
     * Note: used only in renderAsButton()
     * @param string $buttonClasses - example 'btn btn-primary'
     * @return static
     */
    public function setButtonClasses(string $buttonClasses)
    {
        $this->buttonClasses = $buttonClasses;
        return $this;
    }
    
    public function getConditionToDisable(): ?string
    {
        return $this->conditionToDisable;
    }
    
    /**
     * Dot.js condition (actually any valid js code) to decide if menu item should be enabled for current item.
     * Use 'it' variable to access current item data. For example: 'it.is_active || it.id'
     * Resulting html will look like: '<button ... {{? it.is_active || it.id }}disabled{{?}} ...>...</button>'
     * or '<a ... class="... {{? it.is_active || it.id }}disabled{{?}}" ...>...</a>'
     * or '<li class="... {{? it.is_active || it.id }}disabled{{?}}">menu item code</li>' for bootstrap dropdown menu.
     * @return static
     */
    public function setConditionToDisable(string $conditionToDisable)
    {
        $this->conditionToDisable = $conditionToDisable;
        return $this;
    }
    
    public function makeConditionalDisabledAttribute(): string
    {
        return $this->conditionToDisable
            ? " {{? $this->conditionToDisable }}disabled{{?}} "
            : '';
    }
    
    public function getConditionToShow(): ?string
    {
        return $this->conditionToShow;
    }
    
    /**
     * Dot.js condition (actually any valid js code) to decide if menu item should be displayed for current item.
     * Use 'it' variable to access current item data. For example: 'it.is_active || it.id'.
     * Resulting html will look like: '{{? it.is_active || it.id }}menu item code{{?}}'.
     * @return static
     */
    public function setConditionToShow(string $conditionToShow)
    {
        $this->conditionToShow = $conditionToShow;
        return $this;
    }
    
    public function wrapIntoShowCondition(string $html): string
    {
        return $this->conditionToShow
            ? "{{? $this->conditionToShow }}$html{{?}}"
            : $html;
    }
    
    public function getAccessProvider(): ?\Closure
    {
        return $this->accessProvider;
    }
    
    /**
     * Closure to decide if current user can access menu item (if not - item will not be created at all).
     * Note: \Closure receive no arguments and must return boolean value
     * Note: this will avoid usage of condition to show and condition to enable removing item
     * from final template at all.
     * @return static
     */
    public function setAccessProvider(\Closure $accessProvider)
    {
        $this->accessProvider = $accessProvider;
        return $this;
    }
    
    public function isAccessible(): bool
    {
        return !$this->accessProvider || call_user_func($this->accessProvider);
    }
    
    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }
    
    public function hasTooltip(): bool
    {
        return !empty($this->tooltip);
    }
    
    public function getTooltipOrTitle(): string
    {
        return $this->hasTooltip() ? $this->getTooltip() : $this->getTitle();
    }
    
    public function hasTooltipOrTitle(): bool
    {
        return $this->hasTooltip() || $this->hasTitle();
    }
    
    /**
     * @return static
     */
    public function setTooltip(string $tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }
    
    public function getTooltipPosition(): string
    {
        return $this->tooltipPosition;
    }
    
    /**
     * @param string $tooltipPosition - top|left|bottom|right
     * @return static
     */
    public function setTooltipPosition(string $tooltipPosition)
    {
        $this->tooltipPosition = $tooltipPosition;
        return $this;
    }
    
}
