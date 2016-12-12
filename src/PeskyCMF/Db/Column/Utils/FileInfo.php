<?php

namespace PeskyCMF\Db\Column\Utils;

use Swayok\Utils\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfo {

    /** @var FileConfig|ImageConfig */
    protected $fileConfig;
    /** @var int|string */
    protected $primaryKeyValue;
    /** @var string */
    protected $fileName;
    /** @var null|int|string */
    protected $fileNumber;
    /** @var string */
    protected $fileExtension;

    /**
     * @param array $fileInfo
     * @param FileConfig|ImageConfig $fileConfig
     * @param int|string $primaryKeyValue
     * @return static
     */
    static public function fromArray(array $fileInfo, FileConfig $fileConfig, $primaryKeyValue) {
        /** @var FileInfo $obj */
        $obj = new static($fileConfig, $primaryKeyValue, array_get($fileInfo, 'number', null));
        $obj
            ->setFileName(array_get($fileInfo, 'name', null))
            ->setFileExtension(array_get($fileInfo, 'extension', null));
        return $obj;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @param FileConfig|ImageConfig $fileConfig
     * @param int|string $primaryKeyValue
     * @param null|int $fileNumber
     * @return static
     */
    static public function fromSplFileInfo(\SplFileInfo $fileInfo, FileConfig $fileConfig, $primaryKeyValue, $fileNumber = null) {
        $obj = new static($fileConfig, $primaryKeyValue, $fileNumber);
        $obj->setFileExtension(
            $fileInfo instanceof UploadedFile ? $fileInfo->getClientOriginalExtension() : $fileInfo->getExtension()
        );
        return $obj;
    }

    /**
     * @param FileConfig $fileConfig
     * @param int|string $primaryKeyValue
     * @param null|int $fileNumber
     */
    protected function __construct(FileConfig $fileConfig, $primaryKeyValue, $fileNumber = null) {
        $this->fileConfig = $fileConfig;
        $this->primaryKeyValue = $primaryKeyValue;
        $this->fileNumber = $fileNumber;
    }

    /**
     * @return int|null|string
     */
    public function getFileNumber() {
        return $this->fileNumber;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getFileName() {
        if (!$this->fileName) {
            $this->fileName = $this->fileConfig->makeNewFileName();
        }
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    protected function setFileName($fileName) {
        if (!empty($fileName)) {
            $this->fileName = $fileName;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getFileExtension() {
        return $this->fileExtension;
    }

    /**
     * @param string $fileExtension
     * @return $this
     */
    protected function setFileExtension($fileExtension) {
        $this->fileExtension = empty($fileExtension) ? null : $fileExtension;
        return $this;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getFileNameWithExtension() {
        return $this->getFileName() . ($this->getFileExtension() ? '.' . $this->getFileExtension() : '');
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getAbsoluteFilePath() {
        return $this->fileConfig->getAbsolutePathToFileFolder($this->primaryKeyValue) . $this->getFileNameWithExtension();
    }

    /**
     * @return bool
     */
    public function exists() {
        return File::exist($this->getAbsoluteFilePath());
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getRelativeUrl() {
        return $this->fileConfig->getRelativeUrlToFileFolder($this->primaryKeyValue) . $this->getFileNameWithExtension();
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getAbsoluteUrl() {
        return url($this->getRelativeUrl());
    }

    /**
     * @param ImageModificationConfig $modificationConfig
     * @return FileInfo;
     * @throws \BadMethodCallException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function getModifiedImage(ImageModificationConfig $modificationConfig) {
        if (!$this->fileConfig instanceof ImageConfig) {
            throw new \BadMethodCallException('Cannot modify files except images');
        }
        return FileInfo::fromSplFileInfo(
            $modificationConfig->applyModificationTo($this->getAbsoluteFilePath()),
            $this->fileConfig,
            $this->primaryKeyValue,
            $this->getFileNumber()
        );
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function collectImageInfoForDb() {
        return [
            'name' => $this->getFileName(),
            'extension' => $this->getFileExtension(),
            'number' => $this->getFileNumber()
        ];
    }

}