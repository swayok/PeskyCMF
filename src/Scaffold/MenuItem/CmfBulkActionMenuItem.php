<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfBulkActionMenuItem extends CmfRequestMenuItem
{
    
    protected string $actionType = CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED;
    protected string $primaryKeyColumnName = 'id';
    
    public const ACTION_TYPE_BULK_SELECTED = 'bulk-selected';
    public const ACTION_TYPE_BULK_FILTERED = 'bulk-filtered';
    
    /**
     * @throws \InvalidArgumentException
     */
    protected function __construct(string $url, string $httpMethod, string $actionType)
    {
        parent::__construct($url, $httpMethod);
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
    
    protected function modifyTagBeforeRendering(Tag $tag): Tag
    {
        if ($this->actionType === static::ACTION_TYPE_BULK_SELECTED) {
            $tag->setDataAttr('id-field', $this->getPrimaryKeyColumnName());
        }
        return $tag;
    }
}