<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

abstract class RenderableValueViewer extends AbstractValueViewer
{
    
    /**
     * function (FormInput $config, FormConfig $formConfig) {
     *      return InputRenderer::create();
     *      // -- or --
     *      return 'string'; //< rendered input
     * }
     */
    protected ?\Closure $renderer = null;
    
    protected ?\Closure $defaultRendererConfigurator = null;
    
    protected array $jsBlocks = [];
    protected ?string $varNameForDotJs = null;
    
    protected ?string $templateForDefaultRenderer = null;
    protected array $templateDataForDefaultRenderer = [];
    
    /**
     * @param string $name - something like 'RelationName.column_name' (Do not add 'it.' in the beginning!!!)
     * @return static
     */
    public function setVarNameForDotJs(string $name)
    {
        $this->varNameForDotJs = $name;
        return $this;
    }
    
    /**
     * @param bool $addIt - true: adds 'it.' before var name ('it' is name of var that contains template data in doT.js)
     * @param array $additionalVarNameParts - additional parts of var name
     * @return string
     */
    public function getVarNameForDotJs(bool $addIt = true, array $additionalVarNameParts = []): string
    {
        if ($this->varNameForDotJs === null) {
            /** @noinspection NotOptimalRegularExpressionsInspection */
            $this->varNameForDotJs = preg_replace('%[^a-zA-Z0-9_]+%', '.', $this->getName());
        }
        return ($addIt ? 'it.' : '') . rtrim($this->varNameForDotJs . implode('.', $additionalVarNameParts), '.');
    }
    
    /**
     * @param array $additionalVarNameParts - additional parts of var name
     * @param string|null $type - forces value to be converted to specific type.
     *      Accepted types: 'string', 'array', 'json', null (insert value without conversion)
     * @param string|null $default - default value for cases when value is not provided or null.
     *      Don't forget to wrap strings into quotes:
     *      - "''" - inserts empty string (used instead of null),
     *      - "[]" inserts array
     * @return string
     */
    public function getFailsafeValueForDotJs(
        array $additionalVarNameParts = [],
        string $type = 'string',
        ?string $default = null
    ): string {
        $fullName = $this->getVarNameForDotJs();
        $parts = array_merge(explode('.', $fullName), $additionalVarNameParts);
        $conditions = [];
        $chain = 'it';
        for ($i = 1, $cnt = count($parts); $i < $cnt; $i++) {
            $chain .= '.' . $parts[$i];
            if ($i !== ($cnt - 1)) {
                $conditions[] = "(typeof $chain == 'object')";
            } else {
                $conditions[] = "(typeof $chain !== 'undefined')";
            }
        }
        $fullName = implode('.', $parts);
        $conditions[] = "$fullName !== null";
        switch ($type) {
            case 'string':
                $value = "(typeof $fullName === 'boolean' ? ($fullName ? '1' : '0') : String($fullName))";
                break;
            case 'array':
                $conditions[] = "$.isArray($fullName)";
                $value = "($fullName || [])";
                if ($default === null) {
                    $default = '[]';
                }
                break;
            case 'json':
                $conditions[] = "$.isPlainObject($fullName) || $.isArray($fullName)";
                $value = "($fullName || {})";
                if ($default === null) {
                    $default = '{}';
                }
                break;
            default:
                $value = $fullName;
        }
        if ($default === null) {
            $default = "''";
        }
        return '(' . implode(' && ', $conditions) . " ? $value : $default" . ')';
    }
    
    /**
     * Get failsafe value insert for doT.js
     * Normal insert looks like:
     * {{! it.viewer_name || '' }} or {{= it.viewer_name || '' }} but more complicated to provide failsafe insert
     * @param array $additionalVarNameParts - additional parts of var name
     * @param string $type - @see $this->getFailsafeValueForDotJs();
     *      Special types: 'json_encode', 'array_encode' - both apply JSON.stringify() to
     *      inserted value of type 'json' or 'array' respectively
     * @param string|null $default - default value for cases when value is not provided or null.
     *      Don't forget to wrap strings into quotes:
     *      - "''" - inserts empty string (used instead of null),
     *      - "[]" inserts array
     * @param bool|null $encodeHtml
     *      - true: encode value to allow it to be inserten into HTML arguments;
     *      - false: insert as is
     *      - null: autodetect depending on $type
     * @return string
     */
    public function getDotJsInsertForValue(
        array $additionalVarNameParts = [],
        string $type = 'string',
        ?string $default = null,
        ?bool $encodeHtml = null
    ): string {
        $jsonStringify = false;
        switch ($type) {
            case 'json_encode':
                $jsonStringify = true;
                $type = 'json';
                break;
            case 'array_encode':
                $jsonStringify = true;
                $type = 'array';
                break;
        }
        if ($encodeHtml === null) {
            $encodeHtml = !in_array($type, ['json', 'array'], true);
        }
        $encoding = $encodeHtml ? '!' : '=';
        if ($jsonStringify) {
            return "{{{$encoding} JSON.stringify(" . $this->getFailsafeValueForDotJs($additionalVarNameParts, $type, $default) . ') }}';
        } else {
            return "{{{$encoding} " . $this->getFailsafeValueForDotJs($additionalVarNameParts, $type, $default) . ' }}';
        }
    }
    
    /**
     * Get failsafe conditional value insert for doT.js
     * Conditional insert looks like:
     * {{? !!it.viewer_name }}$thenInsert{{??}}$elseInsert{{?}} but more complicated to provide failsafe insert
     * @param string $thenInsert - insert this data when condition is positive
     * @param string $elseInsert - insert this data when condition is negative
     * @param array $additionalVarNameParts - additional parts of var name
     * @return string
     */
    public function getConditionalDotJsInsertForValue(
        string $thenInsert,
        string $elseInsert,
        array $additionalVarNameParts = []
    ): string {
        $fullName = $this->getVarNameForDotJs();
        $parts = array_merge(explode('.', $fullName), $additionalVarNameParts);
        $conditions = [];
        $chain = 'it';
        for ($i = 1, $cnt = count($parts); $i < $cnt; $i++) {
            $chain .= '.' . $parts[$i];
            $conditions[] = '!!' . $chain;
        }
        return '{{? ' . implode(' && ', $conditions) . '}}' . $thenInsert . '{{??}}' . $elseInsert . '{{?}}';
    }
    
    /**
     * @throws ValueViewerConfigException
     */
    public function getRenderer(): ?\Closure
    {
        return $this->renderer ?: $this->getDefaultRenderer();
    }
    
    protected function getDefaultRenderer(): \Closure
    {
        $defaultRenderer = $this->getScaffoldSectionConfig()->getDefaultValueRenderer();
        if (!empty($defaultRenderer)) {
            return $defaultRenderer;
        }
        throw new ValueViewerConfigException($this, get_class($this) . '->renderer is not provided');
    }
    
    /**
     * @param \Closure $renderer - function (RenderableValueViewer $valueViewer, ScaffoldSectionConfig $sectionConfig, array $dataForTemplate) {}
     *      function may return either string or instance of ValueRenderer
     * @return static
     */
    public function setRenderer(\Closure $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
    
    /**
     * @throws ValueViewerConfigException
     */
    public function render(array $dataForTemplate = []): string
    {
        $configOrString = call_user_func($this->getRenderer(), $this, $this->getScaffoldSectionConfig(), $dataForTemplate);
        if (is_string($configOrString)) {
            $rendered = $configOrString;
        } elseif ($configOrString instanceof ValueRenderer) {
            $rendered = view(
                $configOrString->getTemplate(),
                array_merge($configOrString->getData(), $dataForTemplate, [
                    'valueViewer' => $this,
                    'rendererConfig' => $configOrString,
                    'sectionConfig' => $this->getScaffoldSectionConfig(),
                    'table' => $this->getScaffoldSectionConfig()->getTable(),
                ])
            )->render();
        } else {
            throw new ValueViewerConfigException($this, 'Renderer function returned unsopported result. String or ValueRenderer object expected');
        }
        // replace <script> tags to be able to render that template
        return modifyDotJsTemplateToAllowInnerScriptsAndTemplates($rendered . $this->getJavaScriptBlocks());
    }
    
    /**
     * @param \Closure $configurator = function (ValueRenderer $renderer, RenderableValueViewer $valueViewer) {}
     * @return static
     */
    public function setDefaultRendererConfigurator(\Closure $configurator)
    {
        $this->defaultRendererConfigurator = $configurator;
        return $this;
    }
    
    public function hasDefaultRendererConfigurator(): bool
    {
        return !empty($this->defaultRendererConfigurator);
    }
    
    public function getDefaultRendererConfigurator(): ?\Closure
    {
        return $this->defaultRendererConfigurator;
    }
    
    /**
     * Note: input jQuery object is tored in $input variable
     * @param string|\Closure $jsBlockContents - \Closure: function (RenderableValueViewer $valueViewer) { return 'js code'; }
     * @return static
     */
    public function addJavaScriptBlock($jsBlockContents)
    {
        $this->jsBlocks[] = $jsBlockContents;
        return $this;
    }
    
    public function getJavaScriptBlocks(): string
    {
        if (empty($this->jsBlocks)) {
            return '';
        } else {
            $jsCode = '';
            foreach ($this->jsBlocks as $jsBlock) {
                if ($jsBlock instanceof \Closure) {
                    $jsCode .= $jsBlock($this);
                } else {
                    $jsCode .= $jsBlock;
                }
            }
            return '<script type="application/javascript">'
                . '$(function() {'
                . $jsCode
                . '});'
                . '</script>';
        }
    }
    
    /**
     * @return static
     */
    public function configureDefaultRenderer(ValueRenderer $renderer)
    {
        if ($this->templateForDefaultRenderer) {
            $renderer->setTemplate($this->templateForDefaultRenderer);
        }
        if (!empty($this->templateDataForDefaultRenderer)) {
            $renderer->mergeData($this->templateDataForDefaultRenderer);
        }
        return $this;
    }
    
    /**
     * @param string $template - path to template (in Laravel templates stored in /resources/views by default)
     * @return static
     */
    public function setTemplateForDefaultRenderer(string $template)
    {
        $this->templateForDefaultRenderer = $template;
        return $this;
    }
    
    /**
     * @param array $data
     * @return static
     */
    public function setAdditionalTemplateDataForDefaultRenderer(array $data)
    {
        $this->templateDataForDefaultRenderer = $data;
        return $this;
    }
    
}
