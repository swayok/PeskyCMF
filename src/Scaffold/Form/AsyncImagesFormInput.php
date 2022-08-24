<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\MetadataImagesColumn;

/**
 * @method MetadataImagesColumn getTableColumn()
 * todo: upgrade this to be able to use MetadataFileColumn
 */
class AsyncImagesFormInput extends AsyncFilesFormInput {

    protected $previewWidth = 200;

    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array|\Closure $imagesGroups - \Closure must return array
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setImagesGroupsToUse($imagesGroups) {
        $this->setFilesGroupsToUse($imagesGroups);
        return $this;
    }

    /**
     * @return int
     */
    public function getPreviewWidth(): int {
        return $this->previewWidth;
    }

    /**
     * @return static
     */
    public function setPreviewWidth(int $previewWidth) {
        $this->previewWidth = $previewWidth;
        return $this;
    }

    public function getConfigsArrayForJs(string $filesGroupName): array {
        return array_merge(
            parent::getConfigsArrayForJs($filesGroupName),
            [
                'preview_width' => $this->getPreviewWidth()
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
