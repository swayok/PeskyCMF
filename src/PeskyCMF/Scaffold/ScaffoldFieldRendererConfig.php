<?php

namespace PeskyCMF\Scaffold;

abstract class ScaffoldFieldRendererConfig {
    /** @var string */
    protected $view = null;
    /** @var array */
    protected $data = [];
    /** @var string */
    protected $jsBlocks = '';

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
            $this->view = $view;
        }
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
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
    public function getView() {
        return $this->view;
    }

    /**
     * @param string $view
     * @return $this
     */
    public function setView($view) {
        $this->view = $view;
        return $this;
    }

    /**
     * @param string $jsBlockContents
     * @return $this
     */
    public function addJavaScriptBlock($jsBlockContents) {
        $this->jsBlocks .= '<script type="application/javascript">' . $jsBlockContents . '</script>';
        return $this;
    }

    /**
     * @return string
     */
    public function getJavaScriptBlocks() {
        return $this->jsBlocks;
    }
}