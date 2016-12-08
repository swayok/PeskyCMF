<?php

namespace PeskyCMF\Db\Column\Utils;

class FileConfig {

    const TXT = 'text/plain';
    const PDF = 'application/pdf';
    const DOC = 'application/msword';
    const DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const XLS = 'application/ms-excel';
    const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @var string */
    protected $name;
    /** @var string */
    protected $rootFolderAbsolutePath;
    /** @var string */
    protected $rootRelativeUrl;
    /** @var string */
    protected $subfolder;
    /** @var int */
    protected $minFilesCount = 0;
    /** @var int */
    protected $maxFilesCount = 1;
    /**
     * In kilobytes
     * @var int
     */
    protected $maxFileSize = 20480;
    /**
     * @var array
     */
    protected $allowedFileTypes = [
        self::TXT,
        self::PDF,
        self::DOC,
        self::DOCX,
        self::XLS,
        self::XLSX,
    ];

    /**
     * @var array
     */
    protected $typeToExt = [
        self::TXT => 'txt',
        self::PDF => 'pdf',
        self::DOC => 'doc',
        self::DOCX => 'docx',
        self::XLS => 'xls',
        self::XLSX=> 'xlsx',
    ];

    /**
     * @var null|\Closure
     */
    protected $fileNameBuilder;

    public function __construct($name, $relativeBaseFolder) {
        $this->name = $name;
        $this->rootFolderAbsolutePath = $relativeBaseFolder;
        $this->subfolder = preg_replace('%[a-zA-Z-_]+%', '-', $name);
    }

    /**
     * @param $absolutePath
     * @return $this
     */
    public function setRootFolderAbsolutePath($absolutePath) {
        $this->rootFolderAbsolutePath = rtrim($absolutePath, ' /\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * @param int|string $primaryKeyValue
     * @return string
     */
    protected function getRootFolderAbsolutePath($primaryKeyValue) {
        return $this->rootFolderAbsolutePath . $primaryKeyValue . DIRECTORY_SEPARATOR;
    }

    /**
     * @param int|string $primaryKeyValue
     * @return string
     */
    public function getFolderAbsolutePath($primaryKeyValue) {
        return rtrim($this->getRootFolderAbsolutePath($primaryKeyValue) . $this->getSubfolder(), ' /\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $relativeUrl
     * @return $this
     */
    public function setRootRelativeUrl($relativeUrl) {
        $this->rootRelativeUrl = rtrim($relativeUrl, ' /\\') . '/';
        return $this;
    }

    /**
     * @param int|string $primaryKeyValue
     * @return string
     */
    protected function getRootRelativeUrl($primaryKeyValue) {
        return $this->rootRelativeUrl . preg_replace('%[^a-zA-Z0-9_-]+%', '_', $primaryKeyValue) . '/';
    }

    /**
     * @param int|string $primaryKeyValue
     * @return string
     */
    public function getFolderRelativeUrl($primaryKeyValue) {
        return rtrim($this->getRootRelativeUrl($primaryKeyValue) . $this->getSubfolder(), ' /\\') . '/';
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
     * @return array
     */
    public function getAllowedFileTypes() {
        return $this->allowedFileTypes;
    }

    /**
     * @return array
     */
    public function getAllowedFileExtensions() {
        return array_values(array_intersect_key($this->typeToExt, array_flip($this->allowedFileTypes)));
    }

    /**
     * @param array $allowedFileTypes
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAllowedFileTypes(array $allowedFileTypes = []) {
        $this->allowedFileTypes = $allowedFileTypes;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFilesCount() {
        return $this->maxFilesCount;
    }

    /**
     * @param int $count - 0 for unlimited
     * @return $this
     */
    public function setMaxFilesCount($count) {
        $this->maxFilesCount = max(0, (int)$count);
        return $this;
    }

    /**
     * @return int
     */
    public function getMinFilesCount() {
        return $this->minFilesCount;
    }

    /**
     * @param int $minFilesCount
     * @return $this
     */
    public function setMinFilesCount($minFilesCount) {
        $this->minFilesCount = max(0, (int)$minFilesCount);
        return $this;
    }

    /**
     * @return \Closure|null
     */
    protected function getFileNameBuilder() {
        if (!$this->fileNameBuilder) {
            $this->fileNameBuilder = function (FileConfig $fileConfig, $fileNumber = null) {
                return $fileConfig->getName() . (string)$fileNumber;
            };
        }
        return $this->fileNameBuilder;
    }

    /**
     * Function that will build a name for a new file (without extension)
     * @param \Closure $fileNameBuilder =
     *      function (FileConfig $fileConfig, $fileNumber = null)
     *          { return $fileConfig->getName() . (string)$fileNumber }
     * @return $this
     */
    public function setFileNameBuilder(\Closure $fileNameBuilder) {
        $this->fileNameBuilder = $fileNameBuilder;
        return $this;
    }

    /**
     * @param null|int|string $fileNumber
     * @return string
     * @throws \UnexpectedValueException
     */
    public function makeNewFileName($fileNumber = null) {
        $fileName = call_user_func($this->getFileNameBuilder(), $this, $fileNumber);
        if (empty($fileName) || !is_string($fileName)) {
            throw new \UnexpectedValueException(
                'Value returned from FileConfig->fileNameBuilder must be a not empty string'
            );
        }
        return $fileName;
    }
}