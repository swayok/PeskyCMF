<?php

namespace PeskyCMF\Scaffold;

abstract class ScaffoldRenderableFieldConfig extends ScaffoldFieldConfig {

    /**
     * function (FormFieldConfig $config, ScaffoldFormConfig $scaffoldAction) {
     *      return InputRendererConfig::create();
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
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws ScaffoldFieldException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            $defaultRenderer = $this->getScaffoldActionConfig()->getDefaultFieldRenderer();
            if (!empty($defaultRenderer)) {
                return $defaultRenderer;
            }
            throw new ScaffoldFieldException($this, 'FromFieldConfig->renderer is not provided');
        }
        return $this->renderer;
    }

    /**
     * @param \Closure $renderer - function (ScaffoldRenderableFieldConfig $field, ScaffoldActionConfig $actionConfig, array $dataForView) {}
     *      function may return either string or instance of ScaffoldFieldRendererConfig
     * @return $this
     */
    public function setRenderer(\Closure $renderer) {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @param array $dataForView
     * @return string
     * @throws \PeskyCMF\Scaffold\ScaffoldActionException
     * @throws \Throwable
     * @throws ScaffoldFieldException
     */
    public function render(array $dataForView = []) {
        $configOrString = call_user_func_array($this->getRenderer(), [$this, $this->getScaffoldActionConfig(), $dataForView]);
        if (is_string($configOrString)) {
            return $configOrString;
        } else if ($configOrString instanceof ScaffoldFieldRendererConfig) {
            return view($configOrString->getView(), array_merge($configOrString->getData(), $dataForView, [
                'fieldConfig' => $this,
                'rendererConfig' => $configOrString,
                'actionConfig' => $this->getScaffoldActionConfig(),
                'model' => $this->getScaffoldActionConfig()->getTable(),
            ]))->render() . $configOrString->getJavaScriptBlocks();
        } else {
            throw new ScaffoldFieldException($this, 'Renderer function returned unsopported result. String or ScaffoldFieldRendererConfig object expected');
        }
    }

    /**
     * @param \Closure $configurator = function (ScaffoldFieldRendererConfig $renderer, ScaffoldRenderableFieldConfig $fieldConfig) {}
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