<?php

namespace PeskyCMF\Scaffold;

use PeskyORM\DbColumnConfig;

abstract class ScaffoldRenderableFieldConfig extends ScaffoldFieldConfig {

    /**
     * function (FormFieldConfig $config, ScaffoldFormConfig $scaffoldAction) {
     *      return InputRendererConfig::create();
     *      // -- or --
     *      return 'string'; //< rendered input
     * }
     * @var null|callable
     */
    protected $renderer = null;
    /** @var null+callable */
    protected $defaultRendererConfigurator = null;

    /**
     * @return callable|null
     * @throws ScaffoldFieldException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            if (!empty($this->getScaffoldActionConfig()->getDefaultFieldRenderer())) {
                return $this->getScaffoldActionConfig()->getDefaultFieldRenderer();
            }
            throw new ScaffoldFieldException($this, 'FromFieldConfig->renderer is not provided');
        }
        return $this->renderer;
    }

    /**
     * @param callable $renderer - function (ScaffoldRenderableFieldConfig $field, ScaffoldActionConfig $actionConfig, array $dataForView) {}
     * @return $this
     */
    public function setRenderer(callable $renderer) {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @param array $dataForView
     * @return string
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
                'model' => $this->getScaffoldActionConfig()->getModel(),
            ]))->render();
        } else {
            throw new ScaffoldFieldException($this, 'Renderer function returned unsopported result. String or ScaffoldFieldRendererConfig object expected');
        }
    }

    /**
     * @param callable $configurator = function (ScaffoldFieldRendererConfig $renderer, ScaffoldRenderableFieldConfig $field) {}
     * @return $this
     */
    public function setDefaultRendererConfigurator(callable $configurator) {
        $this->defaultRendererConfigurator = $configurator;
        return $this;
    }

    /**
     * @param ScaffoldFieldRendererConfig $renderer
     */
    public function configureDefaultRenderer(ScaffoldFieldRendererConfig $renderer) {
        if (!empty($this->defaultRendererConfigurator)) {
            call_user_func($this->defaultRendererConfigurator, $renderer, $this);
        }
    }

}