<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\ImagesColumn;

/**
 * @method ImagesColumn|FilesColumn getTableColumn()
 */
class ImagesFormInput extends FilesFormInput {

    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array|\Closure $imageGroups - \Closure must return array
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImagesGroupsToUse($imageGroups) {
        $this->setFilesGroupsToUse($imageGroups);
        return $this;
    }

}
