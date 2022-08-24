<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\MetadataImagesColumn;

/**
 * @method MetadataImagesColumn getTableColumn()
 * todo: upgrade this to be able to use MetadataImagesColumn
 */
class ImagesFormInput extends FilesFormInput
{
    
    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array|\Closure $imageGroups - \Closure must return array
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setImagesGroupsToUse($imageGroups)
    {
        $this->setFilesGroupsToUse($imageGroups);
        return $this;
    }
    
}
