<?php


namespace PeskyCMF\Db;

use PeskyCMF\Db\DbColumnConfig\FileColumnConfig;
use PeskyCMF\Db\DbColumnConfig\ImageColumnConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\RecordInterface;
use PeskyORM\ORM\RecordValue;
use Swayok\Utils\File;

class DbFileInfo {

    /** @var RecordValue */
    protected $valueContainer;
    /** @var Column|FileColumnConfig|ImageColumnConfig  */
    protected $column;
    /** @var Record */
    protected $record;
    
    protected $fileExtension;
    protected $fileNameWithoutExtension;
    protected $fileNameWithExtension;
    protected $originalFileNameWithExtension;
    protected $originalFileNameWithoutExtension;

    protected $jsonMap = array(
        'file_name' => 'fileNameWithoutExtension',
        'full_file_name' => 'fileNameWithExtension',
        'original_file_name' => 'originalFileNameWithoutExtension',
        'original_full_file_name' => 'originalFileNameWithExtension',
        'ext' => 'fileExtension',
    );

    public function __construct(RecordValue $valueContainer) {
        $this->valueContainer = $valueContainer;
        $this->column = $valueContainer->getColumn();
        $this->record = $valueContainer->getRecord();
        $this->readFileInfo();
    }
    
    public function getRecord(): RecordInterface {
        return $this->record;
    }
    
    /**
     * @return FileColumnConfig|ImageColumnConfig
     */
    public function getColumn(): Column {
        return $this->column;
    }

    public function readFileInfo() {
        if ($this->record->existsInDb()) {
            $infoFilePath = $this->column->getInfoFilePath($this->record);
            if (File::exist($infoFilePath)) {
                $info = File::readJson($infoFilePath);
                if (!empty($info)) {
                    $this->update($info);
                }
            }
        }
        return $this;
    }

    public function saveToFile() {
        if (!$this->record->existsInDb()) {
            throw new \UnexpectedValueException('Unable to save file info json file of non-existing object');
        }
        $data = array();
        foreach ($this->jsonMap as $jsonKey => $paramName) {
            $method = 'get' . ucfirst($paramName);
            $value = $this->$method();
            if ($value !== null) {
                $data[$jsonKey] = $value;
            }
        }
        $infoFilePath = $this->column->getInfoFilePath($this->record);
        File::saveJson($infoFilePath, $data, true);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function update($data) {
        foreach ($this->jsonMap as $jsonKey => $paramName) {
            if (array_key_exists($jsonKey, $data) && $data[$jsonKey] !== null) {
                $method = 'set' . ucfirst($paramName);
                $this->$method($data[$jsonKey]);
            }
        }
        return $this;
    }

    public function getFileExtension(): ?string {
        return $this->fileExtension;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setFileExtension(string $extension) {
        $this->fileExtension = $extension;
        return $this;
    }

    public function getFileNameWithoutExtension(): ?string {
        return $this->fileNameWithoutExtension;
    }

    /**
     * @param string $fileNameWithoutExtension
     * @return $this
     */
    public function setFileNameWithoutExtension(string $fileNameWithoutExtension) {
        $this->fileNameWithoutExtension = rtrim($fileNameWithoutExtension, '.');
        return $this;
    }

    public function getFileNameWithExtension(): ?string {
        return $this->fileNameWithExtension;
    }

    /**
     * @param string $fileNameWithExtension
     * @return $this
     */
    public function setFileNameWithExtension(string $fileNameWithExtension) {
        $this->fileNameWithExtension = rtrim($fileNameWithExtension, '.');
        return $this;
    }

    public function getOriginalFileNameWithExtension(): ?string {
        return empty($this->originalFileNameWithExtension)
            ? $this->getFileNameWithExtension()
            : $this->originalFileNameWithExtension;
    }
    
    public function hasOriginalFileNameWithExtension(): bool {
        return !empty($this->originalFileNameWithExtension);
    }

    /**
     * @param string $fileNameWithExtension
     * @return $this
     */
    public function setOriginalFileNameWithExtension(string $fileNameWithExtension) {
        $this->originalFileNameWithExtension = rtrim($fileNameWithExtension, '.');
        return $this;
    }

    public function getOriginalFileNameWithoutExtension(): ?string {
        return empty($this->originalFileNameWithoutExtension)
            ? $this->getFileNameWithoutExtension()
            : $this->originalFileNameWithoutExtension;
    }

    /**
     * @param string $fileNameWithoutExtension
     * @return $this
     */
    public function setOriginalFileNameWithoutExtension(string $fileNameWithoutExtension) {
        $this->originalFileNameWithoutExtension = rtrim($fileNameWithoutExtension, '.');
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath() {
        return $this->column->getFilePath($this->valueContainer);
    }

    /**
     * @return string
     */
    public function getAbsoluteFileUrl() {
        return $this->column->getAbsoluteFileUrl($this->valueContainer);
    }

    /**
     * @return bool
     */
    public function isFileExists() {
        return $this->column->isFileExists($this->valueContainer);
    }

    /**
     * @return array
     */
    public function toPublicArray() {
        return array(
            'path' => $this->getFilePath(),
            'url' => $this->getAbsoluteFileUrl(),
            'file_name' => $this->getFileNameWithoutExtension(),
            'full_file_name' => $this->getFileNameWithExtension(),
            'ext' => $this->getFileExtension(),
        );
    }

}