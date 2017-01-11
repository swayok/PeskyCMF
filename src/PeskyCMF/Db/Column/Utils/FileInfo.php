<?php

namespace PeskyCMF\Db\Column\Utils;

use PeskyORM\ORM\RecordInterface;
use Swayok\Utils\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfo {

    /** @var FileConfig|ImageConfig */
    protected $fileConfig;
    /** @var int|string */
    protected $record;
    /** @var string */
    protected $fileName;
    /** @var null|int|string */
    protected $fileSuffix;
    /** @var string */
    protected $fileExtension;
    /** @var array */
    protected $customInfo = [];

    /**
     * @param array $fileInfo
     * @param FileConfig|ImageConfig $fileConfig
     * @param RecordInterface $record
     * @return static
     */
    static public function fromArray(array $fileInfo, FileConfig $fileConfig, RecordInterface $record) {
        /** @var FileInfo $obj */
        $obj = new static($fileConfig, $record, array_get($fileInfo, 'suffix', null));
        $obj
            ->setFileName(array_get($fileInfo, 'name', null))
            ->setFileExtension(array_get($fileInfo, 'extension', null))
            ->setCustomInfo(array_get($fileInfo, 'info', null));
        return $obj;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @param FileConfig|ImageConfig $fileConfig
     * @param RecordInterface $record
     * @param null|int $fileSuffix
     * @return static
     */
    static public function fromSplFileInfo(\SplFileInfo $fileInfo, FileConfig $fileConfig, RecordInterface $record, $fileSuffix = null) {
        $obj = new static($fileConfig, $record, $fileSuffix);
        $obj->setFileExtension(
            $fileInfo instanceof UploadedFile ? $fileInfo->getClientOriginalExtension() : $fileInfo->getExtension()
        );
        return $obj;
    }

    /**
     * @param FileConfig $fileConfig
     * @param RecordInterface $record
     * @param null|int $fileSuffix
     */
    protected function __construct(FileConfig $fileConfig, RecordInterface $record, $fileSuffix = null) {
        $this->fileConfig = $fileConfig;
        $this->record = $record;
        $this->fileSuffix = $fileSuffix;
    }

    /**
     * @return int|null|string
     */
    public function getFileSuffix() {
        return $this->fileSuffix;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getFileName() {
        if (!$this->fileName) {
            $this->fileName = $this->fileConfig->makeNewFileName($this->getFileSuffix());
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
        return $this->fileConfig->getAbsolutePathToFileFolder($this->record) . $this->getFileNameWithExtension();
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getAbsolutePathToModifiedImagesFolder() {
        return $this->fileConfig->getAbsolutePathToFileFolder($this->record) . $this->getFileName();
    }

    /**
     * @param string|array $customInfo
     * @return $this
     */
    public function setCustomInfo($customInfo) {
        if (!is_array($customInfo)) {
            $customInfo = json_decode($customInfo, true);
            if (!is_array($customInfo)) {
                $customInfo = [];
            }
        }
        $this->customInfo = $customInfo;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomInfo() {
        return $this->customInfo;
    }

    /**
     * @return bool
     * @throws \UnexpectedValueException
     */
    public function exists() {
        return File::exist($this->getAbsoluteFilePath());
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getRelativeUrl() {
        return $this->fileConfig->getRelativeUrlToFileFolder($this->record) . $this->getFileNameWithExtension();
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
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function getModifiedImage(ImageModificationConfig $modificationConfig) {
        if (!$this->fileConfig instanceof ImageConfig) {
            throw new \BadMethodCallException('Cannot modify files except images');
        }
        return FileInfo::fromSplFileInfo(
            $modificationConfig->applyModificationTo(
                $this->getAbsoluteFilePath(),
                $this->getAbsolutePathToModifiedImagesFolder()
            ),
            $this->fileConfig,
            $this->record,
            null
        );
    }

    /**
     * @return \SplFileInfo
     */
    public function getSplFileInfo() {
        return new \SplFileInfo($this->getAbsoluteFilePath());
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function collectImageInfoForDb() {
        return [
            'config_name' => $this->fileConfig->getName(),
            'name' => $this->getFileName(), //< file name with suffix but without extension
            'extension' => $this->getFileExtension(),
            'suffix' => $this->getFileSuffix(),
            'info' => $this->getCustomInfo()
        ];
    }

}