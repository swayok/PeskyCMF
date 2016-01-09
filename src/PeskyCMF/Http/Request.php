<?php

namespace PeskyCMF\Http;

use Illuminate\Support\Arr;

class Request extends \Illuminate\Http\Request {

    private $parsedFilesArrays = null;

    /** @var \Illuminate\Http\Request */
    public $sourceRequest;

    public function __construct(\Illuminate\Http\Request $request) {
        $this->sourceRequest = $request;
        $this->query = $request->query;
        $this->request = $request->request;
        $this->attributes = $request->attributes;
        $this->cookies = $request->cookies;
        $this->files = $request->files;
        $this->server = $request->server;
        $this->headers = $request->headers;
    }

    /**
     * Get POST data with files as uploaded file info arrays
     * @param null|string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function data($key = null, $default = null) {
        $data = array_replace_recursive($this->request->all(), $this->getFixedFilesArrays());
        return Arr::get($data, $key, $default);
    }

    /**
     * Make $_FILES array look like $_POST array (fix multiple files uploading)
     * @return array
     */
    protected function getFixedFilesArrays() {
        if ($this->parsedFilesArrays === null) {
            $walker = function ($arr, $fileInfokey, callable $walker) {
                $ret = array();
                foreach ($arr as $k => $v) {
                    if (is_array($v)) {
                        $ret[$k] = $walker($v, $fileInfokey, $walker);
                    } else {
                        $ret[$k][$fileInfokey] = $v;
                    }
                }
                return $ret;
            };

            $files = array();
            foreach ($_FILES as $name => $values) {
                // init for array_merge
                if (!isset($files[$name])) {
                    $files[$name] = array();
                }
                if (!is_array($values['error'])) {
                    // normal syntax
                    $files[$name] = $values;
                } else {
                    // html array feature
                    foreach ($values as $fileInfoKey => $subArray) {
                        $files[$name] = array_replace_recursive($files[$name], $walker($subArray, $fileInfoKey, $walker));
                    }
                }
            }
            $this->parsedFilesArrays = $files;
        }
        return $this->parsedFilesArrays;
    }

    /**
     * Get POST data with files as \Symfony\Component\HttpFoundation\File\UploadedFile objects
     * @param null|string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function dataWithFilesAsObjects($key = null, $default = null) {
        $data = array_replace_recursive($this->request->all(), $this->file());
        return Arr::get($data, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function all($filesAsObjects = false) {
        return $this->getMethod() == 'GET'
            ? $this->query()
            : ($filesAsObjects ? $this->dataWithFilesAsObjects() : $this->data());
    }

    public function getContent($asResource = false) {
        return $this->sourceRequest->getContent($asResource);
    }

}