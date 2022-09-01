<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfRedirectMenuItem extends CmfMenuItem
{
    
    protected bool $openOnNewTab = false;
    protected ?bool $openInModal = null;
    
    public function openOnNewTab(): static
    {
        $this->openOnNewTab = true;
        return $this;
    }
    
    public function setOpenInModal(bool $openInModal = true): static
    {
        $this->openInModal = $openInModal;
        return $this;
    }
    
    /**
     * Render menu item as <a>
     */
    public function renderAsButton(bool $withIcon = true): string
    {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                $this->addCommonAttributes(Tag::a(($withIcon ? $this->makeIcon() . ' ' : '') . $this->getTitle()))
                    ->setClass($this->getButtonClasses() . ' ' . $this->makeConditionalDisabledAttribute())
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
     * @param string $additionalClasses - classes to add to <a> tag
     * @param bool $allowIconColorClass
     * @return string
     */
    public function renderAsIcon(string $additionalClasses = '', bool $allowIconColorClass = true): string
    {
        if ($this->isAccessible()) {
            return $this->wrapIntoShowCondition(
                $this->addCommonAttributes(Tag::a($this->makeIcon($allowIconColorClass)))
                    ->setClass($additionalClasses)
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
     */
    public function renderAsBootstrapDropdownMenuItem(): string
    {
        if ($this->isAccessible()) {
            $link = $this->addCommonAttributes(Tag::a($this->makeIcon(true) . ' ' . $this->getTitle()))
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
    
    protected function addCommonAttributes(Tag $tag): Tag
    {
        $tag
            ->setHref($this->getUrl())
            ->setTarget($this->openOnNewTab ? '_blank' : null)
            ->setDataAttr('modal', $this->openInModal === null ? null : (string)(int)$this->openInModal);
        return $tag;
    }
}
