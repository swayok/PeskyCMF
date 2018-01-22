<?php

namespace PeskyCMF\Scaffold;

use Swayok\Html\Tag;

class CmfRedirectMenuItem extends CmfMenuItem {


    /**
     * Render menu item as <a>
     * @return string
     */
    public function renderAsButton(): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a($this->makeIcon() . ' ' . $this->getTitle())
                    ->setClass($this->getButtonClasses() . ' ' . $this->makeConditionalDisabledAttribute())
                    ->setHref($this->getUrl())
                    ->setTitle($this->getTooltip())
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
     * @return string
     */
    public function renderAsIcon(): string {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                Tag::a($this->makeIcon())
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
     * @return string
     */
    public function renderAsBootstrapDropdownMenuButton(): string {
        if ($this->isAccessible()) {
            $link = Tag::a($this->makeIcon() . ' ' . $this->getTitle())
                ->setClass($this->getButtonClasses())
                ->setHref($this->getUrl())
                ->setTitle($this->getTooltip())
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