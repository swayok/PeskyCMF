<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMLaravel\Db\Column\AsyncImagesColumn;
use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\ImagesColumn;

/**
 * @method ImagesColumn|FilesColumn getTableColumn()
 */
class AsyncImagesFormInput extends AsyncFilesFormInput {

    protected $previewWidth = 200;

    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array|\Closure $imagesGroups - \Closure must return array
     * @return $this
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
     * @param int $previewWidth
     * @return $this
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
    protected function validateLinkedColumnClass() {
        if (!($this->getTableColumn() instanceof AsyncImagesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . AsyncImagesColumn::class
            );
        }
    }

}
