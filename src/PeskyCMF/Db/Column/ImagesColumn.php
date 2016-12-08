<?php

namespace PeskyCMF\Db\Column;

use PeskyCMF\Db\Column\Utils\ImageConfig;
use PeskyCMF\Db\Column\Utils\ImagesUploadingColumnClosures;

class ImagesColumn extends FilesColumn {

    protected $defaultClosuresClass = ImagesUploadingColumnClosures::class;
    protected $fileConfigClass = ImageConfig::class;

    /**
     * @param string $name - image field name
     * @param \Closure $configurator = function (ImageConfig $imageConfig) { //modify $imageConfig }
     * @return $this
     */
    public function addImageConfiguration($name, \Closure $configurator = null) {
        return $this->addFileConfiguration($name, $configurator);
    }

    /**
     * @return ImageConfig[]
     * @throws \UnexpectedValueException
     */
    public function getImagesConfigurations() {
        return $this->getFilesConfigurations();
    }

    /**
     * @param string $name
     * @return ImageConfig
     * @throws \UnexpectedValueException
     */
    public function getImageConfiguration($name) {
        return $this->getFileConfiguration($name);
    }

}