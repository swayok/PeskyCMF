<?php

namespace PeskyCMF\Scaffold\MenuItem;

use Swayok\Html\Tag;

class CmfBulkActionMenuItem extends CmfRequestMenuItem {

    /** @var bool */
    protected $sendSelectedItemsList = true;
    /** @var string  */
    protected $primaryKeyColumnName = 'id';
    
    public const ACTION_TYPE_BULK_SELECTED = 'bulk-selected';
    public const ACTION_TYPE_BULK_FILTERED = 'bulk-filtered';
    
    /**
     * CmfRequestMenuItem constructor.
     * @param string $url
     * @param string $httpMethod
     * @param bool $sendSelectedItemsList
     * @throws \InvalidArgumentException
     */
    protected function __construct(string $url, string $httpMethod, bool $sendSelectedItemsList = true) {
        parent::__construct($url, $httpMethod);
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
    protected function modifyTagBeforeRendering(Tag $tag) {
        if ($this->sendSelectedItemsList) {
            $tag->setDataAttr('id-field', $this->getPrimaryKeyColumnName());
        }
        return $tag;
    }
}