<?php

namespace PeskyCMF\Db\Column\Utils;

class ImageConfig extends FileConfig {

    const PNG = 'image/png';
    const JPEG = 'image/jpeg';
    const GIF = 'image/gif';
    const SVG = 'image/svg';

    protected $typeToExt = [
        self::PNG => 'png',
        self::JPEG => 'jpg',
        self::GIF => 'gif',
        self::SVG => 'svg',
    ];

    /** @var int */
    protected $maxWidth = 1920;
    /** @var float */
    protected $aspectRatio;

    /**
     * @var array
     */
    protected $allowedFileTypes = [
        self::PNG,
        self::JPEG,
        self::SVG,
        self::GIF,
    ];

    /**
     * List of aliases for file types
     * For example: image/jpeg has alias image/x-jpeg
     * @var array
     */
    protected $fileTypeAliases = [
        self::JPEG => [
            'image/x-jpeg'
        ],
        self::PNG => [
            'image/x-png'
        ]
    ];

    /**
     * @return int
     */
    public function getMaxWidth() {
        return $this->maxWidth;
    }

    /**
     * @param int $maxWidth
     * @return $this
     */
    public function setMaxWidth($maxWidth) {
        $this->maxWidth = (int)$maxWidth;
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
     * @param array $allowedFileTypes - combination of ImageConfig::PNG, ImageConfig::JPEG, ImageConfig::GIF, ImageConfig::SVG
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAllowedFileTypes(...$allowedFileTypes) {
        parent::setAllowedFileTypes($allowedFileTypes);
        $unknownTypes = array_diff(
            $this->allowedFileTypes,
            [static::PNG, static::JPEG, static::GIF, static::SVG],
            $this->allowedFileTypesAliases
        );
        if (count($unknownTypes) > 0) {
            throw new \InvalidArgumentException(
                '$allowedFileTypes argument contains not supported image types: ' . implode(', ', $unknownTypes)
            );
        }
        return $this;
    }

    public function getConfigsArrayForJs() {
        return array_merge(
            parent::getConfigsArrayForJs(),
            [
                'aspect_ratio' => $this->getAspectRatio(),
                'max_width' => $this->getMaxWidth(),
            ]
        );
    }


}