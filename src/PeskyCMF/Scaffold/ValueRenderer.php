<?php

namespace PeskyCMF\Scaffold;

abstract class ValueRenderer {
    /** @var string */
    protected $template = null;
    /** @var array */
    protected $data = [];

    /**
     * @param null $view
     * @return $this
     */
    static public function create($view = null) {
        $class = get_called_class();
        return new $class($view);
    }

    /**
     * @param string $view
     */
    public function __construct($view = null) {
        if (!empty($view)) {
            $this->template = $view;
        }
    }

    /**
     * @param null|string $key - string: get value for a key or default one | null: get all data
     * @param mixed $default
     * @return array|mixed
     */
    public function getData($key = null, $default = null) {
        return $key === null ? $this->data : array_get($this->data, $key, $default);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @throws ScaffoldException
     */
    public function addData($key, $value) {
        if (empty($key)) {
            throw new ScaffoldException('$key cannot be empty');
        }
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template) {
        $this->template = $template;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTemplate() {
        return !empty($this->template);
    }

}