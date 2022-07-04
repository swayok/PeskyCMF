<?php

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfBulkActionRedirectMenuItem extends CmfRedirectMenuItem {

    /** @var bool */
    protected $sendSelectedItemsList = true;
    /** @var string  */
    protected $primaryKeyColumnName = 'id';
    
    public const ACTION_TYPE_BULK_SELECTED = CmfBulkActionMenuItem::ACTION_TYPE_BULK_SELECTED;
    public const ACTION_TYPE_BULK_FILTERED = CmfBulkActionMenuItem::ACTION_TYPE_BULK_FILTERED;
    
    /**
     * CmfRequestMenuItem constructor.
     * @param string $url
     * @param string $httpMethod
     * @param bool $sendSelectedItemsList
     * @throws \InvalidArgumentException
     */
    protected function __construct(string $url, bool $sendSelectedItemsList = true) {
        parent::__construct($url);
        $this->sendSelectedItemsList = $sendSelectedItemsList;
    }

    /**
     * @return string
     */
    public function getActionType(): string {
        return $this->sendSelectedItemsList ? static::ACTION_TYPE_BULK_SELECTED : static::ACTION_TYPE_BULK_FILTERED;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyColumnName(): string {
        return $this->primaryKeyColumnName;
    }

    /**
     * Used by 'bulk-selected' action to get primary key values from selected data grid rows
     * @param string $primaryKeyColumnName
     * @return $this
     */
    public function setPrimaryKeyColumnName($primaryKeyColumnName) {
        $this->primaryKeyColumnName = $primaryKeyColumnName;
        return $this;
    }

    /**
     * @param Tag $tag
     * @return Tag
     */
    protected function addCommonAttributes(Tag $tag): Tag {
        parent::addCommonAttributes($tag);
        $tag->setDataAttr('action', $this->getActionType() . '-redirect');
        if ($this->sendSelectedItemsList) {
            $tag->setDataAttr('id-field', $this->getPrimaryKeyColumnName());
        }
        if ($this->openOnNewTab) {
            $tag->setTarget('_blank');
        }
        return $tag;
    }
}