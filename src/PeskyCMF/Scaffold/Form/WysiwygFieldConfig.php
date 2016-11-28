<?php

namespace PeskyCMF\Scaffold\Form;

class WysiwygFieldConfig extends FormFieldConfig {

    /**
     * @var string|null
     */
    protected $relativeImageUploadsFolder;
    /**
     * @var int
     */
    protected $maxImageWidth = 980;
    /**
     * @var int
     */
    protected $maxImageHeight = 0;

    public function getType() {
        return static::TYPE_WYSIWYG;
    }

    /**
     * @param $folder - relative path to folder inside public_path()
     * @return $this
     */
    public function setRelativeImageUploadsFolder($folder) {
        $this->relativeImageUploadsFolder = trim($folder, ' /\\');
        return $this;
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
     * @return bool
     */
    public function hasImageUploadsFolder() {
        return !empty($this->relativeImageUploadsFolder);
    }

    /**
     * @return int
     */
    public function getMaxImageWidth() {
        return $this->maxImageWidth;
    }

    /**
     * @param int $maxImageWidth
     * @return $this
     */
    public function setMaxImageWidth($maxImageWidth) {
        $this->maxImageWidth = (int)$maxImageWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxImageHeight() {
        return $this->maxImageHeight;
    }

    /**
     * @param int $maxImageHeight
     * @return $this
     */
    public function setMaxImageHeight($maxImageHeight) {
        $this->maxImageHeight = (int)$maxImageHeight;
        return $this;
    }


}