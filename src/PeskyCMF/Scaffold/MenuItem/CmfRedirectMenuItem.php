<?php

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfRedirectMenuItem extends CmfMenuItem {

    protected $openOnNewTab = false;

    /**
     * @return $this
     */
    public function openOnNewTab() {
        $this->openOnNewTab = true;
        return $this;
    }

    /**
     * Render menu item as <a>
     * @param bool $withIcon
     * @return string
     */
    public function renderAsButton(bool $withIcon = true): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a(($withIcon ? $this->makeIcon() . ' ' : '') . $this->getTitle())
                    ->setClass($this->getButtonClasses() . ' ' . $this->makeConditionalDisabledAttribute())
                    ->setHref($this->getUrl())
                    ->setTitle($this->getTooltip())
                    ->setTarget($this->openOnNewTab ? '_blank' : null)
                    ->setDataAttr('toggle', $this->hasTooltip() ? 'tooltip' : null)
                    ->setDataAttr('position', $this->hasTooltip() ? $this->getTooltipPosition() : null)
                    ->build()
            );
        } else {
            return '';
        }
    }

    /**
     * Render menu item as <a> icon (title will be used as tooltip)
     * @param string $additionalClasses - classes to add to <a> tag
     * @param bool $allowIconColorClass
     * @return string
     */
    public function renderAsIcon(string $additionalClasses = '', bool $allowIconColorClass = true): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a($this->makeIcon($allowIconColorClass))
                    ->setClass($additionalClasses)
                    ->setHref($this->getUrl())
                    ->setTitle($this->getTooltipOrTitle())
                    ->setTarget($this->openOnNewTab ? '_blank' : null)
                    ->setDataAttr('toggle', $this->hasTooltipOrTitle() ? 'tooltip' : null)
                    ->setDataAttr('position', $this->hasTooltipOrTitle() ? $this->getTooltipPosition() : null)
                    ->build()
            );
        } else {
            return '';
        }
    }

    /**
     * Render menu item as <li><a>...</a></li> or <li><button>...</button></li>
     * @return string
     */
    public function renderAsBootstrapDropdownMenuItem(): string {
        if ($this->isAccessible()) {
            $link = Tag::a($this->makeIcon(true) . ' ' . $this->getTitle())
                ->setHref($this->getUrl())
                ->setTitle($this->getTooltip())
                ->setTarget($this->openOnNewTab ? '_blank' : null)
                ->setDataAttr('toggle', $this->hasTooltip() ? 'tooltip' : null)
                ->setDataAttr('position', $this->hasTooltip() ? $this->getTooltipPosition() : null)
                ->build();
            return $this->wrapIntoShowCondition(
                '<li ' . $this->makeConditionalDisabledAttribute() . '>' . $link . '</li>'
            );
        } else {
            return '';
        }
    }
}