<?php

namespace PeskyCMF\Db\Column\Utils;

class ImageConfig {

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

    const PNG = 'png';
    const JPEG = 'jpeg';
    const GIF = 'gif';
    const SVG = 'svg';

    static protected $typeToExt = [
        self::PNG => 'png',
        self::JPEG => 'jpg',
        self::GIF => 'gif',
        self::SVG => 'svg',
    ];

    /** @var string */
    protected $name;
    /** @var string */
    protected $subfolder;
    /** @var int */
    protected $width = 1920;
    /** @var int */
    protected $height = 3840;
    /** @var float */
    protected $aspectRatio;
    /** @var int */
    protected $fitMode = self::RESIZE_LARGER;
    /** @var string|null */
    protected $backgroundColor;
    /** @var int */
    protected $minImagesCount = 0;
    /** @var int */
    protected $maxImagesCount = 1;
    /**
     * In kilobytes
     * @var int
     */
    protected $maxFileSize = 20480;
    /**
     * @var array
     */
    protected $allowedFileTypes = [
        self::PNG,
        self::JPEG,
        self::SVG,
        self::GIF,
    ];

    protected $verticalAlign = self::CENTER;
    protected $horizontalAlign = self::CENTER;

    public function __construct($name) {
        $this->name = $name;
        $this->subfolder = preg_replace('%[a-zA-Z-_]+%', '-', $name);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSubfolder() {
        return $this->subfolder;
    }

    /**
     * @param string $subfolder
     * @return $this
     */
    public function setSubfolder($subfolder) {
        $this->subfolder = preg_replace('%[a-zA-Z-_]+%', '-', $subfolder);
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth() {
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
    public function getHeight() {
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
    public function getFitMode() {
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
    public function getMaxFileSize() {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize - in kilobytes
     * @return $this
     */
    public function setMaxFileSize($maxFileSize) {
        $this->maxFileSize = (int)$maxFileSize;
        return $this;
    }

    /**
     * @return int
     */
    public function getVerticalAlign() {
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
    public function getHorizontalAlign() {
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
     * @return float|null
     */
    public function getAspectRatio() {
        return $this->aspectRatio;
    }

    /**
     * @param int $width - for example: 4, 16
     * @param int $height - for example: 3, 9
     * @return $this
     */
    public function setAspectRatio($width, $height) {
        $this->aspectRatio = (float)$width / (float)$height;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackgroundColor() {
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
     * @return array
     */
    public function getAllowedFileTypes() {
        return $this->allowedFileTypes;
    }

    /**
     * @return array
     */
    public function getAllowedFileExtensions() {
        return array_values(array_intersect_key(static::$typeToExt, array_flip($this->allowedFileTypes)));
    }

    /**
     * @param array $allowedFileTypes - combination of ImageConfig::PNG, ImageConfig::JPEG, ImageConfig::GIF, ImageConfig::SVG
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAllowedFileTypes(array $allowedFileTypes = []) {
        $unknownTypes = array_diff($allowedFileTypes, [static::PNG, static::JPEG, static::GIF, static::SVG]);
        if (count($unknownTypes) > 0) {
            throw new \InvalidArgumentException(
                '$allowedFileTypes argument contains not supported image types: ' . implode(', ', $unknownTypes)
            );
        }
        $this->allowedFileTypes = $allowedFileTypes;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxImagesCount() {
        return $this->maxImagesCount;
    }

    /**
     * @param int $count - 0 for unlimited
     * @return $this
     */
    public function setMaxImagesCount($count) {
        $this->maxImagesCount = max(0, (int)$count);
        return $this;
    }

    /**
     * @return int
     */
    public function getMinImagesCount() {
        return $this->minImagesCount;
    }

    /**
     * @param int $minImagesCount
     * @return $this
     */
    public function setMinImagesCount($minImagesCount) {
        $this->minImagesCount = max(0, (int)$minImagesCount);
        return $this;
    }



}