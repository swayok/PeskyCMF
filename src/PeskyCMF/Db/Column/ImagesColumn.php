<?php

namespace PeskyCMF\Db\Column;

use PeskyORM\ORM\Column;

class ImagesColumn extends Column {

    /**
     * @var string
     */
    protected $relativeImageUploadsFolder;
    /**
     * @var array
     */
    protected $configs = [];
    /**
     * @var int
     */
    protected $defaultMaxWidth = 1920;
    /**
     * @var int
     */
    protected $defaultMaxHeight = 3840;
    /**
     * In kilobytes
     * @var int
     */
    protected $defaultMaxFileSize = 20480;

    public function __construct($name) {
        parent::__construct($name, static::TYPE_IMAGE);
    }

    public function setRelativeImageUploadsFolder($folder) {
        $this->relativeImageUploadsFolder = trim($folder, ' /\\');
    }

    /**
     * @return string|null
     */
    public function getAbsoluteImageUploadsFolder() {
        return public_path($this->relativeImageUploadsFolder) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getRelativeImageUploadsUrl() {
        return '/' . str_replace('\\', '/', $this->relativeImageUploadsFolder) . '/';
    }

    /**
     * @param string $name - image field name
     * @param null|string $subfolder - subfolder name if $this->getAbsoluteImageUploadsFolder(). Default: sanitized $name
     * @param null|int $maxWidth - max image width
     * @param null|int $maxHeight - max image heigth
     * @param null|int $maxFileSize - max file size in kilobytes
     * @return $this
     */
    public function addImageConfiguration($name, $subfolder = null, $maxWidth = null, $maxHeight = null, $maxFileSize = null) {
        $this->configs[$name] = [
            'name' => $name,
            'subfolder' => preg_replace('%[a-zA-Z-_]+%', '-', (string)($subfolder === null ? $name : $subfolder)),
            'max_width' => (int)($maxWidth === null ? $this->defaultMaxWidth : $maxWidth),
            'max_height' => (int)($maxHeight === null ? $this->defaultMaxHeight : $maxHeight),
            'max_file_size' => (int)($maxFileSize === null ? $this->defaultMaxFileSize : $maxFileSize)
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getImagesConfigurations() {
        return $this->configs;
    }


}