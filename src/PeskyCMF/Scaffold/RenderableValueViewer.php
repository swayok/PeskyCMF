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

    /**
     * @return \Closure
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     * @throws ValueViewerException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            $defaultRenderer = $this->getScaffoldSectionConfig()->getDefaultValueRenderer();
            if (!empty($defaultRenderer)) {
                return $defaultRenderer;
            }
            throw new ValueViewerException($this, 'FromFieldConfig->renderer is not provided');
        }
        return $this->renderer;
    }

    /**
     * @param \Closure $renderer - function (RenderableValueViewer $valueViewer, ScaffoldSectionConfig $actionConfig, array $dataForTemplate) {}
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
     * @throws \PeskyCMF\Scaffold\ScaffoldSectionException
     * @throws \Throwable
     * @throws ValueViewerException
     */
    public function render(array $dataForTemplate = []) {
        $configOrString = call_user_func_array($this->getRenderer(), [$this, $this->getScaffoldSectionConfig(), $dataForTemplate]);
        if (is_string($configOrString)) {
            return $configOrString;
        } else if ($configOrString instanceof ValueRenderer) {
            return view($configOrString->getTemplate(), array_merge($configOrString->getData(), $dataForTemplate, [
                'fieldConfig' => $this,
                'rendererConfig' => $configOrString,
                'actionConfig' => $this->getScaffoldSectionConfig(),
                'model' => $this->getScaffoldSectionConfig()->getTable(),
            ]))->render() . $configOrString->getJavaScriptBlocks();
        } else {
            throw new ValueViewerException($this, 'Renderer function returned unsopported result. String or ValueRenderer object expected');
        }
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

}