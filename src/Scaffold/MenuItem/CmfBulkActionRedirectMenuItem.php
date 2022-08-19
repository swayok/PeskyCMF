<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfBulkActionRedirectMenuItem extends CmfRedirectMenuItem
{
    
    protected string $actionType = CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED;
    protected string $primaryKeyColumnName = 'id';
    
    public const ACTION_TYPE_BULK_SELECTED = CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED;
    public const ACTION_TYPE_BULK_FILTERED = CmfBulkActionMenuItem::ACTION_TYPE_BULK_FILTERED;
    
    /**
     * @throws \InvalidArgumentException
     */
    protected function __construct(string $url, string $actionType)
    {
        parent::__construct($url);
        if (!in_array($actionType, [static::ACTION_TYPE_BULK_FILTERED, static::ACTION_TYPE_BULK_SELECTED], true)) {
            throw new \InvalidArgumentException('$actionType argument contains not supported value: ' . $actionType);
        }
        $this->actionType = $actionType;
    }
    
    public function getActionType(): string
    {
        return $this->actionType;
    }
    
    public function getPrimaryKeyColumnName(): string
    {
        return $this->primaryKeyColumnName;
    }
    
    /**
     * Used by 'bulk-selected' action to get primary key values from selected data grid rows
     * @return static
     */
    public function setPrimaryKeyColumnName(string $primaryKeyColumnName)
    {
        $this->primaryKeyColumnName = $primaryKeyColumnName;
        return $this;
    }
    
    protected function addCommonAttributes(Tag $tag): Tag
    {
        parent::addCommonAttributes($tag);
        $tag->setDataAttr('action', $this->getActionType() . '-redirect');
        if ($this->actionType === static::ACTION_TYPE_BULK_SELECTED) {
            $tag->setDataAttr('id-field', $this->getPrimaryKeyColumnName());
        }
        if ($this->openOnNewTab) {
            $tag->setTarget('_blank');
        }
        return $tag;
    }
}