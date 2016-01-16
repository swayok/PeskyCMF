<?php

namespace PeskyCMF;

use Illuminate\Contracts\Support\Arrayable;
use Traversable;

abstract class ConfigsContainer implements \IteratorAggregate, \Countable, Arrayable {

    private $array = null;
    static protected $instances = [];

    public function __construct() {
        self::$instances[get_called_class()] = $this;
    }

    /**
     * Returns instance of config class it was called from
     * Note: method excluded from toArray() results but key "config_instance" added instead of it
     * @return $this
     */
    static public function getInstance() {
        $class = get_called_class();
        if (empty(self::$instances[$class])) {
            self::$instances[$class] = new $class;
        }
        return self::$instances[$class];
    }

    static public function replaceConfigInstance($classToReplace, $instance) {
        self::$instances[$classToReplace] = $instance;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count() {
        return count($this->toArray());
    }

    /**
     * Get the instance as an array.
     * Note 1: it collects returns from all "static public" methods that do not have parameters
     * Collecting done only once. There should be no need to do it more then once.
     * Note 2: array does not contain key "getInstance" but contains key "config_instance" instead
     * @return array
     */
    public function toArray() {
        if ($this->array === null) {
            $this->array = [];
            $reflection = new \ReflectionClass($this);
            /*
             * You may ask why haven't I used \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC here.
             * The answer is: it is not working in php 5.6.12 or maybe in other versions too
             */
            $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
            foreach ($methods as $method) {
                if (
                    $method->isPublic()
                    && $method->getNumberOfParameters() === 0
                    && $method->getName() !== 'getInstance'
                ) {
                    $this->array[$method->getName()] = $method->invoke(null);
                }
            }
            $this->array['config_instance'] = $this;
        }
        return $this->array;
    }
}