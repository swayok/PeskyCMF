<?php

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfRedirectMenuItem extends CmfMenuItem {


    /**
     * Render menu item as <a>
     * @param bool $allowTooltip
     * @return string
     */
    public function renderAsButton(bool $allowTooltip = true): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a($this->makeIcon() . ' ' . $this->getTitle())
                    ->setClass($this->getButtonClasses() . ' ' . $this->makeConditionalDisabledAttribute())
                    ->setHref($this->getUrl())
                    ->setTitle($allowTooltip ? $this->getTooltip() : null)
                    ->setDataAttr('toggle', $allowTooltip && $this->hasTooltip() ? 'tooltip' : null)
                    ->setDataAttr('position', $allowTooltip && $this->hasTooltip() ? $this->getTooltipPosition() : null)
                    ->build()
            );
        } else {
            return '';
        }
    }

    /**
     * Render menu item as <a> icon (title will be used as tooltip)
     * @param string $additionalClasses - classes to add to <a> tag
     * @return string
     */
    public function renderAsIcon(string $additionalClasses = ''): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a($this->makeIcon(true))
                    ->setClass($additionalClasses)
                    ->setHref($this->getUrl())
                    ->setTitle($this->getTooltipOrTitle())
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
     * @param bool $allowTooltip
     * @return string
     */
    public function renderAsBootstrapDropdownMenuItem(bool $allowTooltip = true): string {
        if ($this->isAccessible()) {
            $link = Tag::a($this->makeIcon(true) . ' ' . $this->getTitle())
                ->setHref($this->getUrl())
                ->setTitle($allowTooltip ? $this->getTooltip() : null)
                ->setDataAttr('toggle', $allowTooltip && $this->hasTooltip() ? 'tooltip' : null)
                ->setDataAttr('position', $allowTooltip && $this->hasTooltip() ? $this->getTooltipPosition() : null)
                ->build();
            return $this->wrapIntoShowCondition(
                '<li ' . $this->makeConditionalDisabledAttribute() . '>' . $link . '</li>'
            );
        } else {
            return '';
        }
    }
}