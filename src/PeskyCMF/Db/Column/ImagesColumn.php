<?php

namespace PeskyCMF\Db\Column;

use PeskyCMF\Db\Column\Utils\ImageConfig;
use PeskyORM\ORM\Column;

/**
 * todo: added images saving/getting (for both DB and FS)
 */
class ImagesColumn extends Column implements \Iterator, \ArrayAccess {

    /**
     * @var string
     */
    protected $relativeImageUploadsFolder;
    /**
     * @var ImageConfig[]|\Closure[]
     */
    protected $configs = [];
    /**
     * @var array
     */
    protected $iterator;

    static public function create($name = null, $notUsed = null) {
        return new static($name);
    }

    public function __construct($name) {
        parent::__construct($name, static::TYPE_JSONB);
        $this
            ->convertsEmptyStringToNull()
            ->setDefaultValue('{}');
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
     * @param \Closure $configurator = function (ImageConfig $imageConfig) { //modify $imageConfig }
     * @return $this
     */
    public function addImageConfiguration($name, \Closure $configurator = null) {
        $this->configs[$name] = $configurator;
        $this->iterator = null;
        return $this;
    }

    /**
     * @return ImageConfig[]
     */
    public function getImagesConfigurations() {
        foreach ($this->configs as $name => $config) {
            if (!($config instanceof ImageConfig)) {
                $this->getImageConfiguration($name);
            }
        }
        return $this->configs;
    }

    /**
     * @param string $name
     * @return ImageConfig
     * @throws \UnexpectedValueException
     */
    public function getImageConfiguration($name) {
        if (!array_key_exists($name, $this->configs)) {
            throw new \UnexpectedValueException("There is no configuretion for image called '$name'");
        } else if ($this->configs[$name] instanceof \Closure) {
            $imageConfig = new ImageConfig($name);
            call_user_func($this->configs[$name], $imageConfig);
            $this->configs[$name] = $imageConfig;
        } else if ($this->configs[$name] === null) {
            $this->configs[$name] = new ImageConfig($name);
        }
        return $this->configs[$name];
    }

    public function getIterator() {
        if ($this->iterator === null) {
            $this->iterator = new \ArrayIterator($this->configs);
        }
        return $this->iterator;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return $this->getIterator()->current();
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
     * @return mixed Can return all value types.
     * @throws \UnexpectedValueException
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        return $this->getImageConfiguration($offset);
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
        throw new \BadMethodCallException('You must use addImageConfiguration() method');
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