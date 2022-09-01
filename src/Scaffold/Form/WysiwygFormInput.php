<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Support\Arr;

class WysiwygFormInput extends FormInput
{
    
    protected ?string $relativeImageUploadsFolder = null;
    protected int $maxImageWidth = 980;
    protected int $maxImageHeight = 2000;
    protected ?\Closure $dataInserts = null;
    protected ?\Closure $htmlInserts = null;
    protected ?\Closure $wysiwygConfig = null;
    protected string $customJsCode = '';
    protected string $wysiwygInitializerFunctionName = 'ScaffoldFormHelper.initWysiwyg';
    protected array $customCssFiles = [];
    
    public function getType(): string
    {
        return static::TYPE_WYSIWYG;
    }
    
    /**
     * Set relative path to folder inside public_path()
     */
    public function setRelativeImageUploadsFolder(string $folder): static
    {
        $this->relativeImageUploadsFolder = trim($folder, ' /\\');
        return $this;
    }
    
    public function getAbsoluteImageUploadsFolder(): string
    {
        return public_path($this->relativeImageUploadsFolder) . DIRECTORY_SEPARATOR;
    }
    
    public function getRelativeImageUploadsUrl(): string
    {
        return '/' . str_replace('\\', '/', $this->relativeImageUploadsFolder) . '/';
    }
    
    public function hasImageUploadsFolder(): bool
    {
        return !empty($this->relativeImageUploadsFolder);
    }
    
    public function getMaxImageWidth(): int
    {
        return $this->maxImageWidth;
    }
    
    public function setMaxImageWidth(int $maxImageWidth): static
    {
        $this->maxImageWidth = $maxImageWidth;
        return $this;
    }
    
    public function getMaxImageHeight(): int
    {
        return $this->maxImageHeight;
    }
    
    public function setMaxImageHeight(int $maxImageHeight): static
    {
        $this->maxImageHeight = $maxImageHeight;
        return $this;
    }
    
    /**
     * Signature:
     * function (): array { return [$dataInsert1, $dataInsert2, ...]; }
     * Where $dataInsert is associative array with keys:
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
     */
    public function setDataInserts(\Closure $provider): static
    {
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
    public static function createDataInsertConfig(string $phpCode, string $title, bool $showAsBlock = false): array
    {
        if (empty(trim($phpCode))) {
            throw new \InvalidArgumentException('$phpCode argument must be a not empty string');
        }
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('$title argument must be a not empty string');
        }
        return [
            'code' => $phpCode,
            'title' => $title,
            'is_block' => $showAsBlock,
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
     *          'type' => 'text',
     *          'base64' => true,
     *      ],
     *  )
     *  Types: 'select', 'text', 'checkbox', 'textarea'
     *  Additional options: 'value' (default one), 'checked' (bool, for checkbox), 'base64' (bool, encode value into base64 string)
     *  Resulting insert may look like 'printPageData("home", "content")'
     *  Note that for options loaded via URL, URL will be modified to contain 'pk' URL query argument that holds
     *  current item id loaded into edit form. This is the way to exclude some items from returned options.
     *  For example if we editing item with primary key value '13' then options url will be
     *  'http://domain/admin/api/pages/options_for_inserts?pk=13'
     * @param string|null $widgetTitleTpl - an alternative title template for insert's representation (widget) inside text editor
     *  You can use args from $optionsForArguments to insert into template.
     *  For example template can be like this: ':some_text (:page_code.label / :page_feild.value)'
     *  Args for select is an object with 2 keys: 'label' and 'value', other args are plain text
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function createDataInsertConfigWithArguments(
        string $phpCode,
        string $title,
        bool $showAsBlock = false,
        array $optionsForArguments = [],
        ?string $widgetTitleTpl = null
    ): array {
        $config = static::createDataInsertConfig($phpCode, $title, $showAsBlock);
        $config['args_options'] = $optionsForArguments;
        $config['widget_title_tpl'] = $widgetTitleTpl;
        return $config;
    }
    
    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getDataInserts(): array
    {
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
                $config['is_block'] = (bool)Arr::get($config, 'is_block', false);
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
    public static function createHtmlInsertConfig(string $html, string $title, bool $isBlock = true): array
    {
        if (empty(trim($html))) {
            throw new \InvalidArgumentException('$html argument must be a not empty string');
        }
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('$title argument must be a not empty string');
        }
        return [
            'html' => $html,
            'title' => $title,
            'is_block' => $isBlock,
        ];
    }
    
    public function setHtmlInserts(\Closure $provider): static
    {
        $this->htmlInserts = $provider;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getHtmlInserts(): array
    {
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
     */
    public function addCssFilesToWysiwygEditor(...$cssFiles): static
    {
        $this->customCssFiles = $cssFiles;
        return $this;
    }
    
    /**
     * Signature:
     * function(): array { return []; }
     */
    public function setWysiwygConfig(\Closure $configMaker): static
    {
        $this->wysiwygConfig = $configMaker;
        return $this;
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    public function getWysiwygConfig(): array
    {
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
        $config['uploadUrl'] = $this->getCmfConfig()->route('cmf_ckeditor_upload_image', ['_token' => csrf_token()]);
        $config['filebrowserImageUploadUrl'] = $config['uploadUrl'];
        return $config;
    }
    
    /**
     * @param string $jsCode - valid javascript code. Don't forget to add ';' at the end
     */
    public function setCustomJsCode(string $jsCode): static
    {
        $this->customJsCode = $jsCode;
        return $this;
    }
    
    public function getCustomJsCode(): string
    {
        return $this->customJsCode;
    }
    
    public function setWysiwygInitializerFunctionName(string $functionName): static
    {
        $this->wysiwygInitializerFunctionName = $functionName;
        return $this;
    }
    
    public function getWysiwygInitializerFunctionName(): string
    {
        return $this->wysiwygInitializerFunctionName;
    }
    
}