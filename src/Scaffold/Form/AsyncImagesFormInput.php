<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\MetadataImagesColumn;

/**
 * @method MetadataImagesColumn getTableColumn()
 * todo: upgrade this to be able to use MetadataFileColumn
 */
class AsyncImagesFormInput extends AsyncFilesFormInput
{
    
    protected int $previewWidth = 200;
    
    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * $imagesGroups as \Closure must return array.
     */
    public function setImagesGroupsToUse(array|\Closure $imagesGroups): static
    {
        $this->setFilesGroupsToUse($imagesGroups);
        return $this;
    }
    
    public function getPreviewWidth(): int
    {
        return $this->previewWidth;
    }
    
    public function setPreviewWidth(int $previewWidth): static
    {
        $this->previewWidth = $previewWidth;
        return $this;
    }
    
    public function getConfigsArrayForJs(string $filesGroupName): array
    {
        return array_merge(
            parent::getConfigsArrayForJs($filesGroupName),
            [
                'preview_width' => $this->getPreviewWidth(),
            ]
        );
    }
    
    /**
     * @throws \BadMethodCallException
     */
    protected function validateLinkedColumnClass(): void
    {
        if (!($this->getTableColumn() instanceof MetadataImagesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . MetadataImagesColumn::class
            );
        }
    }
    
}
