<?php

namespace PeskyCMF\Db\Column;

use PeskyCMF\Db\Column\Utils\FileConfig;
use PeskyCMF\Db\Column\Utils\ImageConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\RecordInterface;
use PeskyORMLaravel\Db\KeyValueTableUtils\KeyValueTableInterface;

class FilesColumn extends Column implements \Iterator, \ArrayAccess {

    //protected $defaultClosuresClass = ImagesUploadingColumnClosures::class;
    /**
     * @var string
     */
    protected $relativeUploadsFolderPath;
    /**
     * @var ImageConfig[]|FileConfig[]|\Closure[]
     */
    protected $configs = [];
    /**
     * @var array
     */
    protected $iterator;
    /**
     * @var string
     */
    protected $fileConfigClass = FileConfig::class;

    const VALUE_MUST_BE_ARRAY = 'value_must_be_array';
    const IMAGE_TYPE_IS_NOT_ALLOWED = 'invalid_image_type';
    const FILE_TYPE_IS_NOT_ALLOWED = 'invalid_file_type';
    const FILE_SIZE_IS_TOO_LARGE = 'file_size_is_too_large';
    const FILE_IS_NOT_A_VALID_IMAGE = 'file_is_not_a_valid_image';

    static protected $additionalValidationErrorsLocalization = [
        self::VALUE_MUST_BE_ARRAY => 'Value must be an array',
        self::IMAGE_TYPE_IS_NOT_ALLOWED => 'Uploaded image type \'%s\' is not allowed for \'%s\'. Allowed file types: %s',
        self::FILE_TYPE_IS_NOT_ALLOWED => 'Uploaded file type \'%s\' is not allowed for \'%s\'. Allowed file types: %s',
        self::FILE_SIZE_IS_TOO_LARGE => 'Uploaded file size is too large for \'%s\'. Maximum file size is %s kilobytes.',
        self::FILE_IS_NOT_A_VALID_IMAGE => 'Uploaded file for \'%s\' is corrupted or it is not a valid image',
    ];

    /**
     * @param null|string $name
     * @param null $notUsed
     * @return static
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function create($name = null, $notUsed = null) {
        return new static($name);
    }

    /**
     * @param string|null $name
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function __construct($name) {
        parent::__construct($name, static::TYPE_JSONB);
        $this
            ->convertsEmptyStringToNull()
            ->setDefaultValue('{}');
    }

    public function isItAFile() {
        return true;
    }

    /**
     * Path to folder is relative to public_path()
     * @param string|\Closure $folder - function (RecordInterface $record, FileConfig $fileConfig) { return 'path/to/folder'; }
     * @return $this
     */
    public function setRelativeUploadsFolderPath($folder) {
        $this->relativeUploadsFolderPath = $folder;
        return $this;
    }

    /**
     * @param RecordInterface $record
     * @param FileConfig $fileConfig
     * @return string
     */
    protected function getRelativeUploadsFolderPath(RecordInterface $record, FileConfig $fileConfig) {
        if ($this->relativeUploadsFolderPath instanceof \Closure) {
            return call_user_func($this->relativeUploadsFolderPath, $record, $fileConfig);
        } else {
            return $this->buildRelativeUploadsFolderPathForRecordAndFileConfig($record, $fileConfig);
        }
    }

    /**
     * @param RecordInterface $record
     * @param FileConfig $fileConfig
     * @return string
     */
    protected function buildRelativeUploadsFolderPathForRecordAndFileConfig(RecordInterface $record, FileConfig $fileConfig) {
        $table = $record::getTable();
        if ($table instanceof KeyValueTableInterface) {
            $fkName = $table->getMainForeignKeyColumnName();
            $subfolder = empty($fkName) ? '' : $record->getValue($fkName);
        } else {
            $subfolder = $record->getPrimaryKeyValue();
        }
        $subfolder = preg_replace('%[^a-zA-Z0-9_-]+%', '_', $subfolder);
        return $this->relativeUploadsFolderPath . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . $fileConfig->getName();
    }

    /**
     * @param string $path
     * @return string
     */
    static protected function normalizeFolderPath($path) {
        return preg_replace('%[/\\\]+%', DIRECTORY_SEPARATOR, rtrim($path, ' /\\')) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param RecordInterface $record
     * @param FileConfig $fileConfig
     * @return string
     */
    public function getAbsoluteFileUploadsFolder(RecordInterface $record, FileConfig $fileConfig) {
        return static::normalizeFolderPath(public_path($this->getRelativeUploadsFolderPath($record, $fileConfig)));
    }

    /**
     * @param RecordInterface $record
     * @param FileConfig $fileConfig
     * @return string
     */
    public function getRelativeFileUploadsUrl(RecordInterface $record, FileConfig $fileConfig) {
        return static::normalizeFolderUrl($this->getRelativeUploadsFolderPath($record, $fileConfig));
    }

    /**
     * @param string $url
     * @return string
     */
    static protected function normalizeFolderUrl($url) {
        return '/' . trim(preg_replace('%[\\\]+%', '/', $url), ' /') . '/';
    }

    /**
     * @param string $name - image field name
     * @param \Closure $configurator = function (FileConfig $imageConfig) { //modify $imageConfig }
     * @return $this
     */
    public function addFileConfiguration($name, \Closure $configurator = null) {
        $this->configs[$name] = $configurator;
        $this->iterator = null;
        return $this;
    }

    /**
     * @return FileConfig[]|ImageConfig[]
     * @throws \UnexpectedValueException
     */
    public function getFilesConfigurations() {
        foreach ($this->configs as $name => $config) {
            if (!(get_class($config) === $this->fileConfigClass)) {
                $this->getFileConfiguration($name);
            }
        }
        return $this->configs;
    }

    /**
     * @param string $name
     * @return FileConfig|ImageConfig
     * @throws \UnexpectedValueException
     */
    public function getFileConfiguration($name) {
        if (!$this->hasFileConfiguration($name)) {
            throw new \UnexpectedValueException("There is no configuration for file called '$name'");
        } else if (!is_object($this->configs[$name]) || get_class($this->configs[$name]) !== $this->fileConfigClass) {
            $class = $this->fileConfigClass;
            /** @var FileConfig $fileConfig */
            $fileConfig = new $class($name);
            $fileConfig
                ->setAbsolutePathToFileFolder(function (RecordInterface $record, FileConfig $fileConfig) {
                    return $this->getAbsoluteFileUploadsFolder($record, $fileConfig);
                })
                ->setRelativeUrlToFileFolder(function (RecordInterface $record, FileConfig $fileConfig) {
                    return $this->getRelativeFileUploadsUrl($record, $fileConfig);
                });
            if ($this->configs[$name] instanceof \Closure) {
                call_user_func($this->configs[$name], $fileConfig);
            }
            $this->configs[$name] = $fileConfig;
        }
        return $this->configs[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFileConfiguration($name) {
        return array_key_exists($name, $this->configs);
    }

    /**
     * @return bool
     */
    public function hasFilesConfigurations() {
        return !empty($this->configs);
    }

    /**
     * @return array
     */
    static public function getValidationErrorsLocalization() {
        return array_merge(parent::getValidationErrorsLocalization(), static::$additionalValidationErrorsLocalization);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator() {
        if ($this->iterator === null) {
            $this->iterator = new \ArrayIterator($this->configs);
        }
        return $this->iterator;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return ImageConfig|FileConfig
     * @throws \UnexpectedValueException
     * @since 5.0.0
     */
    public function current() {
        return $this->getFileConfiguration($this->getIterator()->key());
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        $this->getIterator()->next();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return $this->getIterator()->key();
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return $this->getIterator()->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->getIterator()->rewind();
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->configs);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return FileConfig
     * @throws \UnexpectedValueException
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        return $this->getFileConfiguration($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @throws \BadMethodCallException
     * @since 5.0.0
     */
    public function offsetSet($offset, $value) {
        throw new \BadMethodCallException('You must use special setter method add*Configuration()');
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @throws \BadMethodCallException
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        throw new \BadMethodCallException('Removing image configuration is forbidden');
    }

}