<?php

namespace PeskyCMF\Scaffold;

abstract class RenderableValueViewer extends AbstractValueViewer {

    /**
     * function (FormInput $config, FormConfig $formConfig) {
     *      return InputRenderer::create();
     *      // -- or --
     *      return 'string'; //< rendered input
     * }
     * @var null|\Closure
     */
    protected $renderer = null;
    /** @var null|\Closure */
    protected $defaultRendererConfigurator = null;
    /** @var array  */
    protected $jsBlocks = [];

    /**
     * @return \Closure
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     * @throws ValueViewerConfigException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            $defaultRenderer = $this->getScaffoldSectionConfig()->getDefaultValueRenderer();
            if (!empty($defaultRenderer)) {
                return $defaultRenderer;
            }
            throw new ValueViewerConfigException($this, get_class($this) . '->renderer is not provided');
        }
        return $this->renderer;
    }

    /**
     * @param \Closure $renderer - function (RenderableValueViewer $valueViewer, ScaffoldSectionConfig $sectionConfig, array $dataForTemplate) {}
     *      function may return either string or instance of ValueRenderer
     * @return $this
     */
    public function setRenderer(\Closure $renderer) {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @param array $dataForTemplate
     * @return string
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionConfigException
     * @throws \Throwable
     * @throws ValueViewerConfigException
     */
    public function render(array $dataForTemplate = []) {
        $configOrString = call_user_func_array($this->getRenderer(), [$this, $this->getScaffoldSectionConfig(), $dataForTemplate]);
        if (is_string($configOrString)) {
            $rendered = $configOrString;
        } else if ($configOrString instanceof ValueRenderer) {
            $rendered = view($configOrString->getTemplate(), array_merge($configOrString->getData(), $dataForTemplate, [
                'valueViewer' => $this,
                'rendererConfig' => $configOrString,
                'sectionConfig' => $this->getScaffoldSectionConfig(),
                'table' => $this->getScaffoldSectionConfig()->getTable(),
            ]))->render();
        } else {
            throw new ValueViewerConfigException($this, 'Renderer function returned unsopported result. String or ValueRenderer object expected');
        }
        // replace <script> tags to be able to render that template
        return modifyDotJsTemplateToAllowInnerScriptsAndTemplates($rendered . $this->getJavaScriptBlocks());
    }

    /**
     * @param \Closure $configurator = function (ValueRenderer $renderer, RenderableValueViewer $valueViewer) {}
     * @return $this
     */
    public function setDefaultRendererConfigurator(\Closure $configurator) {
        $this->defaultRendererConfigurator = $configurator;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDefaultRendererConfigurator() {
        return !empty($this->defaultRendererConfigurator);
    }

    /**
     * @return null|\Closure
     */
    public function getDefaultRendererConfigurator() {
        return $this->defaultRendererConfigurator;
    }

    /**
     * Note: input jQuery object is tored in $input variable
     * @param string|\Closure $jsBlockContents - \Closure: function (RenderableValueViewer $valueViewer) { return 'js code'; }
     * @return $this
     */
    public function addJavaScriptBlock($jsBlockContents) {
        $this->jsBlocks[] = $jsBlockContents;
        return $this;
    }

    /**
     * @return string
     */
    public function getJavaScriptBlocks() {
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

}