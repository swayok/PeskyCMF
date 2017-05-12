<?php

namespace PeskyCMF\Scaffold\ItemDetails;

use PeskyCMF\Db\Column\FilesColumn;
use PeskyCMF\Db\Column\ImagesColumn;
use PeskyCMF\Db\Column\Utils\FileConfig;
use PeskyCMF\Scaffold\ValueRenderer;

/**
 * @method ImagesColumn|FilesColumn getTableColumn()
 */
class ImagesValueCell extends ValueCell {

    /** @var FileConfig[]|null */
    protected $fileConfigsToShow;
    /** @var string  */
    protected $templateForDefaultRenderer = 'cmf::item_details.images';

    /**
     * List of image names to display.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array $imageNames
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImagesToShow(...$imageNames) {
        if (empty($imageNames)) {
            throw new \InvalidArgumentException('$imageNames argument cannot be empty');
        }
        if (count($imageNames) === 1 && isset($imageNames[0]) && is_array($imageNames[0])) {
            $imageNames = $imageNames[0];
        }
        $this->fileConfigsToShow = $imageNames;
        return $this;
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        if (!empty($value) && is_array($value)) {
            $filesToShow = $this->fileConfigsToShow ?: array_keys($this->getTableColumn()->getFilesConfigurations());
            $files = array_intersect_key($value, array_flip($filesToShow));
            foreach ($files as $name => $fileInfo) {
                //< todo: make urls to files and return + update view to use this
            }
        }
        return [];
    }

}