<?php

namespace PeskyCMF\Db\Column\Utils;

use Symfony\Component\HttpFoundation\File\File;

class ImageModificationConfig {

    // Note: all fit modes preserve aspect ratio of the original image
    /**
     * Crop image to fit both dimensions with 100% fill + enlarge it if needed (same as css background-size: cover)
     */
    const COVER = 1;
    /**
     * Resize image to fit both dimensions + enlarge it if needed (same as css background-size: contain)
     * Empty space will be filled with specified background color
     */
    const CONTAIN = 2;
    /**
     * Downsize image to fit both dimensions; do nothing for images that already fit both dimensions;
     * Resulting dimensions will be within required dimensions. No enlarge, no background fill.
     */
    const RESIZE_LARGER = 3;

    const TOP = 1;
    const CENTER = 2;
    const BOTTOM = 3;
    const LEFT = 4;
    const RIGHT = 5;

    const PNG = ImageConfig::PNG;
    const JPEG = ImageConfig::JPEG;
    const GIF = ImageConfig::GIF;
    const SVG = ImageConfig::SVG;
    const SAME_AS_ORIGINAL = null;

    static protected $typeToExt = [
        self::PNG => 'png',
        self::JPEG => 'jpg',
        self::GIF => 'gif',
        self::SVG => 'svg',
    ];

    /** @var int */
    protected $width = 1920;
    /** @var int */
    protected $height = 0;
    /** @var int */
    protected $fitMode = self::RESIZE_LARGER;
    /** @var string|null */
    protected $backgroundColor;
    /** @var int */
    protected $verticalAlign = self::CENTER;
    /** @var int */
    protected $horizontalAlign = self::CENTER;
    /**
     * One of ImageModificationConfig::SAME_AS_ORIGINAL, ImageModificationConfig::PNG,
     * ImageModificationConfig::JPEG, ImageModificationConfig::GIF, ImageModificationConfig::SVG
     * @var
     */
    protected $alterImageType = self::SAME_AS_ORIGINAL;

    /**
     * @param string $modificationName - modification name
     * @return static
     * @throws \InvalidArgumentException
     */
    static public function create($modificationName) {
        return new static($modificationName);
    }

    /**
     * @param string $modificationName - modification name
     * @throws \InvalidArgumentException
     */
    public function __construct($modificationName) {
        if (empty($modificationName) || !is_string($modificationName)) {
            throw new \InvalidArgumentException('$modificationName argument must be a not empty string');
        }
        $this->name = $modificationName;
    }

    /**
     * @return string
     */
    protected function getModificationName() {
        return $this->name;
    }

    /**
     * @return int
     */
    protected function getWidth() {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width) {
        $this->width = (int)$width;
        return $this;
    }

    /**
     * @return int
     */
    protected function getHeight() {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height) {
        $this->height = (int)$height;
        return $this;
    }

    /**
     * @return int
     */
    protected function getFitMode() {
        return $this->fitMode;
    }

    /**
     * @param int $fitMode - one of self::COVER, self::CONTAIN, self::RESIZE_LARGER
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFitMode($fitMode) {
        if (!in_array($fitMode, [static::CONTAIN, static::COVER, static::RESIZE_LARGER], true)) {
            throw new \InvalidArgumentException(
                '$fitMode argument must be one of: ImageConfig::COVER, ImageConfig::CONTAIN, ImageConfig::RESIZE_LARGER'
            );
        }
        $this->fitMode = $fitMode;
        return $this;
    }

    /**
     * @return int
     */
    protected function getVerticalAlign() {
        return $this->verticalAlign;
    }

    /**
     * Set vertical align for images with fit mode CONTAIN and COVER
     * @param int $verticalAlign
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setVerticalAlign($verticalAlign) {
        if (!in_array($verticalAlign, [static::TOP, static::CENTER, static::BOTTOM], true)) {
            throw new \InvalidArgumentException(
                '$verticalAlign argument must be one of: ImageConfig::TOP, ImageConfig::CENTER, ImageConfig::BOTTOM'
            );
        }
        $this->verticalAlign = $verticalAlign;
        return $this;
    }

    /**
     * @return int
     */
    protected function getHorizontalAlign() {
        return $this->horizontalAlign;
    }

    /**
     * @param int $horizontalAlign
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setHorizontalAlign($horizontalAlign) {
        if (!in_array($horizontalAlign, [static::LEFT, static::CENTER, static::RIGHT], true)) {
            throw new \InvalidArgumentException(
                '$horizontalAlign argument must be one of: ImageConfig::LEFT, ImageConfig::CENTER, ImageConfig::RIGHT'
            );
        }
        $this->horizontalAlign = $horizontalAlign;
        return $this;
    }

    /**
     * @return string|null
     */
    protected function getBackgroundColor() {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     *      - hex: color. Note: must be without leading '#' (FFFFFF for white bg);
     *      - null: transparency (png) or white bg (not png)
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setBackgroundColor($backgroundColor) {
        if (mb_strlen($backgroundColor) !== 6) {
            throw new \InvalidArgumentException('$backgroundColor argument must have exactly 6 characters');
        } else if (!preg_match('%^[a-fA-F0-9]+$%', $backgroundColor)) {
            throw new \InvalidArgumentException('$backgroundColor argument must contain only hex characters: 0123456789ABCDEF');
        }
        $this->backgroundColor = strtoupper($backgroundColor);
        return $this;
    }

    /**
     * Change image type
     * @param $mimeType - ImageModificationConfig::SAME_AS_ORIGINAL, ImageModificationConfig::PNG,
     * ImageModificationConfig::JPEG, ImageModificationConfig::GIF, ImageModificationConfig::SVG
     * @throws \InvalidArgumentException
     */
    public function setImageType($mimeType) {
        if (!in_array($mimeType, [self::SAME_AS_ORIGINAL, self::GIF, self::PNG, self::JPEG, self::SVG], true)) {
            throw new \InvalidArgumentException("\$mimeType '{$mimeType}' is not supported");
        }
        $this->alterImageType = $mimeType;
    }

    /**
     * @return null|string
     */
    protected function getImageType() {
        return $this->alterImageType;
    }

    /**
     * @param string $absoluteFilePath
     * @param string $modifiedImagesFolder
     * @return File
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function applyModificationTo($absoluteFilePath, $modifiedImagesFolder) {
        $originalFile = new File($absoluteFilePath);
        $newFileExt = $this->getImageType() === null ? $originalFile->getExtension() : static::$typeToExt[$this->getImageType()];
        $baseFileName = preg_replace('%\.' . $originalFile->getExtension() . '$%i', '', $originalFile->getFilename());
        $newFileName = $baseFileName . '-' . $this->getModificationName();
        $newFile = new File(
            rtrim($modifiedImagesFolder. '/\\ ') . DIRECTORY_SEPARATOR . $newFileName . '.' . $newFileExt,
            false
        );
        // todo: implement $originalFile modification and store result to $newFile
        return $newFile;
    }

}