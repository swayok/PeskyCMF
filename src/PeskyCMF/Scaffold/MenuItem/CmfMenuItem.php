<?php

namespace PeskyCMF\Scaffold\MenuItem;

abstract class CmfMenuItem {

    /** @var string */
    protected $url;
    /** @var string */
    protected $title = '';
    /** @var string */
    protected $iconClasses = '';
    /** @var string  */
    protected $iconColorClass = '';
    /** @var string */
    protected $buttonClasses = 'btn btn-default';
    /** @var string|null */
    protected $conditionToDisable;
    /** @var string|null */
    protected $conditionToShow;
    /** @var \Closure|null */
    protected $accessProvider;
    /** @var string|null */
    protected $tooltip;
    /** @var string */
    protected $tooltipPosition = 'top';

    /**
     * @param string $url
     * @return CmfRedirectMenuItem
     */
    static public function redirect(string $url) {
        return new CmfRedirectMenuItem($url);
    }

    /**
     * @param string $url
     * @param string $method
     * @return CmfRequestMenuItem
     * @throws \InvalidArgumentException
     */
    static public function request(string $url, string $method = 'post') {
        return new CmfRequestMenuItem($url, $method);
    }

    /**
     * @param string $url
     * @param string $method
     * @return CmfBulkActionMenuItem
     * @throws \InvalidArgumentException
     */
    static public function bulkActionOnSelectedRows(string $url, string $method = 'post') {
        return new CmfBulkActionMenuItem($url, $method, true);
    }

    /**
     * @param string $url
     * @param string $method
     * @return CmfBulkActionMenuItem
     * @throws \InvalidArgumentException
     */
    static public function bulkActionOnFilteredRows(string $url, string $method = 'post') {
        return new CmfBulkActionMenuItem($url, $method, false);
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
     * @param bool $allowTooltip
     * @return string
     */
    abstract public function renderAsBootstrapDropdownMenuItem(): string;

    /**
     * @param string $url
     */
    protected function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl(): string {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title) {
        $this->title = $title;
        return $this;
    }

    public function hasTitle(): bool {
        return !empty($this->title);
    }

    /**
     * @return string
     */
    public function getIconClasses(): string {
        return $this->iconClasses;
    }

    /**
     * @param string $iconClasses
     * @return $this
     */
    public function setIconClasses(string $iconClasses) {
        $this->iconClasses = $iconClasses;
        return $this;
    }

    public function getIconColorClass(): string {
        return ' ' . $this->iconColorClass . ' ';
    }

    /**
     * Used in renderAsIcon() and renderAsBootstrapDropdownMenuButton() to alter icon color
     * @param string $iconColorClass - example: 'text-primary'
     * @return $this
     */
    public function setIconColorClass(string $iconColorClass) {
        $this->iconColorClass = $iconColorClass;
        return $this;
    }

    public function makeIcon(bool $addColorClass = false): string {
        if ($this->iconClasses) {
            return '<i class="' . $this->iconClasses . ' ' . ($addColorClass ? $this->getIconColorClass() : '') . '"></i>';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getButtonClasses(): string {
        return $this->buttonClasses;
    }

    /**
     * Note: used only in renderAsButton()
     * @param string $buttonClasses
     * @return $this
     */
    public function setButtonClasses(string $buttonClasses) {
        $this->buttonClasses = $buttonClasses;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getConditionToDisable() {
        return $this->conditionToDisable;
    }

    /**
     * Dot.js condition (actually any valid js code) to decide if menu item should be enabled for current item.
     * Use 'it' variable to access current item data. For example: 'it.is_active || it.id'
     * Resulting html will look like: '<button ... {{? it.is_active || it.id }}disabled{{?}} ...>...</button>'
     * or '<a ... class="... {{? it.is_active || it.id }}disabled{{?}}" ...>...</a>'
     * or '<li class="... {{? it.is_active || it.id }}disabled{{?}}">menu item code</li>' for bootstrap dropdown menu.
     * @param string $conditionToDisable
     * @return $this
     */
    public function setConditionToDisable(string $conditionToDisable) {
        $this->conditionToDisable = $conditionToDisable;
        return $this;
    }

    /**
     * @return string
     */
    public function makeConditionalDisabledAttribute(): string {
        return $this->conditionToDisable
            ? " {{? $this->conditionToDisable }}disabled{{?}} "
            : '';
    }

    /**
     * @return null|string
     */
    public function getConditionToShow() {
        return $this->conditionToShow;
    }

    /**
     * Dot.js condition (actually any valid js code) to decide if menu item should be displayed for current item.
     * Use 'it' variable to access current item data. For example: 'it.is_active || it.id'.
     * Resulting html will look like: '{{? it.is_active || it.id }}menu item code{{?}}'.
     * @param string $conditionToShow
     * @return $this
     */
    public function setConditionToShow(string $conditionToShow) {
        $this->conditionToShow = $conditionToShow;
        return $this;
    }

    /**
     * @param string $html
     * @return string
     */
    public function wrapIntoShowCondition(string $html): string {
        return $this->conditionToShow
            ? "{{? $this->conditionToShow }}$html{{?}}"
            : $html;
    }

    /**
     * @return \Closure|null
     */
    public function getAccessProvider() {
        return $this->accessProvider;
    }

    /**
     * Closure to decide if current user can access menu item (if not - item will not be created at all).
     * Note: \Closure receive no arguments and must return boolean value
     * Note: this will avoid usage of condition to show and condition to enable removing item
     * from final template at all.
     * @param \Closure $accessProvider
     * @return $this
     */
    public function setAccessProvider(\Closure $accessProvider) {
        $this->accessProvider = $accessProvider;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAccessible() {
        return $this->accessProvider ? (bool)call_user_func($this->accessProvider) : true;
    }

    /**
     * @return null|string
     */
    public function getTooltip() {
        return $this->tooltip;
    }

    public function hasTooltip(): bool {
        return !empty($this->tooltip);
    }

    /**
     * @return null|string
     */
    public function getTooltipOrTitle() {
        return $this->hasTooltip() ? $this->getTooltip() : $this->getTitle();
    }

    public function hasTooltipOrTitle(): bool {
        return $this->hasTooltip() || $this->hasTitle();
    }

    /**
     * @param string $tooltip
     * @return $this
     */
    public function setTooltip(string $tooltip) {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * @return string
     */
    public function getTooltipPosition(): string {
        return $this->tooltipPosition;
    }

    /**
     * @param string $tooltipPosition
     * @return $this
     */
    public function setTooltipPosition(string $tooltipPosition) {
        $this->tooltipPosition = $tooltipPosition;
        return $this;
    }



}