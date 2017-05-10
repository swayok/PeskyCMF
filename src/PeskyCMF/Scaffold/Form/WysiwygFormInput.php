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
    protected $htmlInserts;
    /**
     * @var null|\Closure
     */
    protected $wysiwygConfig;
    /**
     * @var string
     */
    protected $customJsCode = '';
    /**
     * @var string
     */
    protected $wysiwygInitializerFunctionName = 'ScaffoldFormHelper.initWysiwyg';
    /**
     * @var array
    */
    protected $customCssFiles = [];



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
     * @param \Closure $provider - must return array of associative arrays with keys:
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
    public function setDataInserts(\Closure $provider) {
        $this->dataInserts = $provider;
        return $this;
    }

    /**
     * Create valid single item config for wysiwyg's data inserter plugin
     * @param string $phpCode - php code that returns some text content. Code is inserted using Blade's command '{!! your_code_here !!}'
     * @param string $title - insert's label to display inside wysiwyg editor
     * @param bool $showAsBlock -
     *      - true: inserted data is separate block (<div>, <p> or some other block element);
     *      - false (default): inserted data is inline (simple text, <span>, etc)
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
     * Create valid single item config for wysiwyg's data inserter plugin
     * @param string $phpCode - php code that returns some text content. Code is inserted using Blade's command '{!! your_code_here !!}'
     * @param string $title - insert's label to display inside wysiwyg editor
     * @param bool $showAsBlock -
     *  - true: inserted data is separate block (<div>, <p> or some other block element);
     *  - false (default): inserted data is inline (simple text, <span>, etc)
     * @param array $optionsForArguments - list of options for each argument to be passed into $phpCode.
     *  Example: $phpCode = 'printPageData(":page_code", ":page_field")'. Args are: 'page_code' and 'page_field'.
     *  $optionsForArguments should be: array(
     *      'page_code' => [
     *          'label' => 'Page',
     *          'type' => 'select'
     *          'options' => 'http://domain/admin/api/pages/options_for_inserts'
     *      ],
     *      'page_field' => [
     *          'label' => 'Field'
     *          'type' => 'select'
     *          'options' => [
     *              'title' => 'Title',
     *              'content' => 'Text'
     *          ],
     *          'value' => 'content'
     *      ],
     *      'some_text' => [
     *          'label' => 'Page',
     *          'type' => 'text'
     *      ],
     *  )
     *  Types: 'select', 'text', 'checkbox'
     *  Additional options: 'value' (default one), 'checked' (bool, for checkbox)
     *  Resulting insert may look like 'printPageData("home", "content")'
     *  Note that for options loaded via URL, URL will be modified to contain 'pk' URL query argument that holds
     *  current item id loaded into edit form. This is the way to exclude some items from returned options.
     *  For example if we editing item with primary key value '13' then options url will be
     *  'http://domain/admin/api/pages/options_for_inserts?pk=13'
     * @param null|string $widgetTitleTpl - an alternative title template for insert's representation (widget) inside text editor
     *  You can use args from $optionsForArguments to insert into template.
     *  For example template can be like this: ':some_text (:page_code.label / :page_feild.value)'
     *  Args for select is an object with 2 keys: 'label' and 'value', other args are plain text
     * @return array
     * @throws \InvalidArgumentException
     */
    static public function createDataInsertConfigWithArguments($phpCode, $title, $showAsBlock = false, array $optionsForArguments, $widgetTitleTpl = null) {
        $config = static::createDataInsertConfig($phpCode, $title, $showAsBlock);
        $config['args_options'] = $optionsForArguments;
        $config['widget_title_tpl'] = $widgetTitleTpl;
        return $config;
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
                throw new \UnexpectedValueException('InsertsProvider closure must return an array');
            }
            $inserts = [];
            foreach ($insertsRaw as $config) {
                if (!is_array($config) || empty($config['code']) || empty($config['title'])) {
                    throw new \UnexpectedValueException(
                        'InsertsProvider returned invalid data insert config. Confirm that config is array and has keys "code" and "title"'
                    );
                }
                $config['code'] = "@wysiwygInsert({$config['code']})";
                $config['is_block'] = (bool)array_get($config, 'is_block', false);
                $inserts[] = $config;
            }
            return $inserts;
        }
        return [];
    }

    /**
     * @param string $html - HTML code to insert into editor
     * @param string $title - option title
     * @param bool $isBlock
     * @return array
     * @throws \InvalidArgumentException
     */
    static public function createHtmlInsertConfig($html, $title, $isBlock = true) {
        if (!is_string($html) || empty(trim($html))) {
            throw new \InvalidArgumentException('$html argument must be a not empty string');
        }
        if (!is_string($title) || empty(trim($title))) {
            throw new \InvalidArgumentException('$title argument must be a not empty string');
        }
        return [
            'html' => $html,
            'title' => $title,
            'is_block' => (bool)$isBlock
        ];
    }

    /**
     * @param \Closure $provider
     * @return $this
     */
    public function setHtmlInserts(\Closure $provider) {
        $this->htmlInserts = $provider;
        return $this;
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getHtmlInserts() {
        if (!empty($this->htmlInserts)) {
            /** @var array $insertsRaw */
            $insertsRaw = call_user_func($this->htmlInserts);
            if (!is_array($insertsRaw)) {
                throw new \UnexpectedValueException('InsertsProvider closure must return an array');
            }
            $inserts = [];
            foreach ($insertsRaw as $config) {
                if (!is_array($config) || empty($config['html']) || empty($config['title'])) {
                    throw new \UnexpectedValueException(
                        'InsertsProvider returned invalid data insert config. Confirm that config is array and has keys "html" and "title"'
                    );
                }
                $inserts[] = $config;
            }
            return $inserts;
        }
        return [];
    }

    /**
     * This file will be added into wysiwyg editor to allow custom styling inside editor
     * It also allows to display HtmlInserts the same way as on frontend
     * @param array $cssFiles
     * @return $this
     */
    public function addCssFilesToWysiwygEditor(...$cssFiles) {
        $this->customCssFiles = $cssFiles;
        return $this;
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
        $config['html_inserts'] = $this->getHtmlInserts();
        if (!empty($this->customCssFiles)) {
            if (array_key_exists('contentsCss', $config)) {
                $config['contentsCss'] = array_unique(array_merge((array)$config['contentsCss'], $this->customCssFiles));
            } else {
                $config['contentsCss'] = $this->customCssFiles;
            }
        }
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