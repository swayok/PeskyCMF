<?php

namespace PeskyCMF\Scaffold\Form;

class WysiwygFormInput extends FormInput {

    /**
     * @var string|null
     */
    protected $relativeImageUploadsFolder;
    /**
     * @var int
     */
    protected $maxImageWidth = 980;
    /**
     * @var int
     */
    protected $maxImageHeight = 2000;
    /**
     * @var null|\Closure
     */
    protected $dataInserts;
    /**
     * @var null|\Closure
     */
    protected $wysiwygConfig;
    /**
     * @var string
     */
    protected $customJsCode = '';
    /**
     * @var
     */
    protected $wysiwygInitializerFunctionName = 'ScaffoldFormHelper.initWysiwyg';

    public function getType() {
        return static::TYPE_WYSIWYG;
    }

    /**
     * @param $folder - relative path to folder inside public_path()
     * @return $this
     */
    public function setRelativeImageUploadsFolder($folder) {
        $this->relativeImageUploadsFolder = trim($folder, ' /\\');
        return $this;
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
     * @return bool
     */
    public function hasImageUploadsFolder() {
        return !empty($this->relativeImageUploadsFolder);
    }

    /**
     * @return int
     */
    public function getMaxImageWidth() {
        return $this->maxImageWidth;
    }

    /**
     * @param int $maxImageWidth
     * @return $this
     */
    public function setMaxImageWidth($maxImageWidth) {
        $this->maxImageWidth = (int)$maxImageWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxImageHeight() {
        return $this->maxImageHeight;
    }

    /**
     * @param int $maxImageHeight
     * @return $this
     */
    public function setMaxImageHeight($maxImageHeight) {
        $this->maxImageHeight = (int)$maxImageHeight;
        return $this;
    }

    /**
     * @param \Closure $loader - must return array of associative arrays with keys:
     *  - 'code' - php code that returns some text content. Code is inserted using Blade's command '{!! your_code_here !!}'
     *  - 'title' - insert's label to display inside wysiwyg editor
     *  - 'is_block' (optional)
     *          - true: inserted data is separate block (<div>, <p> or some other block element);
     *          - false (default): inserted data is inline (simple text, <span>, etc)
     *  For example: [
     *      'code' => 'insertData()',
     *      'title' => 'Test insert',
     *      'is_block' => false
     *  ]
     * @return $this
     */
    public function setDataInserts(\Closure $loader) {
        $this->dataInserts = $loader;
        return $this;
    }

    /**
     * Create valid config for
     * @param $phpCode - php code that returns some text content. Code is inserted using Blade's command '{!! your_code_here !!}'
     * @param $title
     * @param bool $showAsBlock
     * @return array
     * @throws \InvalidArgumentException
     */
    static public function createDataInsertConfig($phpCode, $title, $showAsBlock = false) {
        if (!is_string($phpCode) || empty(trim($phpCode))) {
            throw new \InvalidArgumentException('$phpCode argument must be a not empty string');
        }
        if (!is_string($title) || empty(trim($title))) {
            throw new \InvalidArgumentException('$title argument must be a not empty string');
        }
        return [
            'code' => $phpCode,
            'title' => $title,
            'is_block' => (bool)$showAsBlock
        ];
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getDataInserts() {
        if (!empty($this->dataInserts)) {
            /** @var array $insertsRaw */
            $insertsRaw = call_user_func($this->dataInserts);
            if (!is_array($insertsRaw)) {
                throw new \UnexpectedValueException('InsertsLoader closure must return an array');
            }
            $inserts = [];
            foreach ($insertsRaw as $config) {
                if (!is_array($config) || empty($config['code']) || empty($config['title'])) {
                    throw new \UnexpectedValueException(
                        'InsertsLoader returned invalid data insert config. Confirm that config is array and has keys "code" and "title"'
                    );
                }
                $config['code'] = "{!! {$config['code']} !!}";
                $config['is_block'] = (bool)array_get($config, 'is_block', false);
                $inserts[] = $config;
            }
            return $inserts;
        }
        return [];
    }

    /**
     * @param \Closure $configMaker - must return array
     * @return $this
     */
    public function setWysiwygConfig(\Closure $configMaker) {
        $this->wysiwygConfig = $configMaker;
        return $this;
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getWysiwygConfig() {
        $config = [];
        if (!empty($this->wysiwygConfig)) {
            $config = call_user_func($this->wysiwygConfig);
            if (!is_array($config)) {
                throw new \UnexpectedValueException('WysiwygConfig closure must return an array');
            }
        }
        $config['data_inserts'] = $this->getDataInserts();
        return $config;
    }

    /**
     * @param string $jsCode - valid javascript code. Don't forget to add ';' at the end
     * @return $this
     */
    public function setCustomJsCode($jsCode) {
        $this->customJsCode = (string)$jsCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomJsCode() {
        return $this->customJsCode;
    }

    /**
     * @param string $functionName
     */
    public function setWysiwygInitializerFunctionName($functionName) {
        $this->wysiwygInitializerFunctionName = (string)$functionName;
    }

    /**
     * @return string
     */
    public function getWysiwygInitializerFunctionName() {
        return $this->wysiwygInitializerFunctionName;
    }

}