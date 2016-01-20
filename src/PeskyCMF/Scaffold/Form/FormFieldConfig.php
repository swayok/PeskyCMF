<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Scaffold\ScaffoldActionConfig;
use PeskyCMF\Scaffold\ScaffoldFieldException;
use PeskyCMF\Scaffold\ScaffoldRenderableFieldConfig;
use PeskyORM\DbColumnConfig;

class FormFieldConfig extends ScaffoldRenderableFieldConfig {

    /** @var bool */
    protected $showOnCreate = true;
    /** @var bool */
    protected $showOnEdit = true;
    /** @var string */
    protected $type = self::TYPE_STRING;
    const TYPE_STRING = DbColumnConfig::TYPE_STRING;
    const TYPE_PASSWORD = DbColumnConfig::TYPE_PASSWORD;
    const TYPE_EMAIL = DbColumnConfig::TYPE_EMAIL;
    const TYPE_TEXT = DbColumnConfig::TYPE_TEXT;
    const TYPE_WYSIWYG = 'wysiwyg';
    const TYPE_BOOL = DbColumnConfig::TYPE_BOOL;
    const TYPE_SELECT = 'select';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_DATE = DbColumnConfig::TYPE_DATE;
    const TYPE_DATETIME = DbColumnConfig::TYPE_TIMESTAMP;
    const TYPE_DATE_RANGE = 'daterange';
    const TYPE_DATETIME_RANGE = 'datetimerange';
    const TYPE_TIME = DbColumnConfig::TYPE_TIME;
    const TYPE_TIME_RANGE = 'timerange';

    /**
     * value can be:
     * 1. <array> of pairs:
     *      'option_value' => 'option_label'
     * 3. callable
     * @var null|array|callable
     */
    protected $options = null;

    /** @var callable|null */
    protected $optionsLoader = null;

    /**
     * @return null|array|callable
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param null|array|callable $options
     * @return $this
     * @throws ScaffoldFieldException
     */
    public function setOptions($options) {
        if (!is_array($options) && !is_callable($options)) {
            throw new ScaffoldFieldException($this, '$options should be an array');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * @param callable $loader
     * @return $this
     */
    public function setOptionsLoader(callable $loader) {
        $this->optionsLoader = $loader;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getOptionsLoader() {
        return $this->optionsLoader;
    }

    /**
     * @return bool
     */
    public function hasOptionsLoader() {
        return !empty($this->optionsLoader);
    }

    /**
     * @return array
     * @throws ScaffoldFieldException
     */
    public function loadOptions() {
        if (empty($this->optionsLoader)) {
            throw new ScaffoldFieldException($this, 'FormFieldConfig->optionsLoader is empty');
        }
        return call_user_func_array($this->optionsLoader, [$this, $this->getScaffoldActionConfig()->getModel()]);
    }

    /**
     * @return boolean
     */
    public function isShownOnCreate() {
        return $this->showOnCreate;
    }

    /**
     * @return $this
     */
    public function showOnCreate() {
        $this->showOnCreate = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnCreate() {
        $this->showOnCreate = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShownOnEdit() {
        return $this->showOnEdit;
    }

    /**
     * @return $this
     */
    public function showOnEdit() {
        $this->showOnEdit = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideOnEdit() {
        $this->showOnEdit = false;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        parent::setType($type);
        switch ($this->type) {
            case self::TYPE_BOOL:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/checkbox');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_EMAIL:
            case self::TYPE_PASSWORD:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/text', ['type' => $fieldConfig->getType()]);
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_HIDDEN:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/hidden');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_TEXT:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/textarea');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_SELECT:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/select')
                        ->setOptions($fieldConfig->getOptions());
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_WYSIWYG:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/wysiwyg');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_IMAGE:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/image');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
            case self::TYPE_DATETIME:
                $this->setRenderer(function (FormFieldConfig $fieldConfig, ScaffoldActionConfig $actionConfig, array $dataForView) {
                    $rendererConfig = InputRendererConfig::create('cmf::input/datetime');
                    return $fieldConfig->_configureRendererByColumnConfig($rendererConfig);
                });
                break;
        }
        return $this;
    }

    /**
     * @param InputRendererConfig $rendererConfig
     * @return InputRendererConfig
     */
    public function _configureRendererByColumnConfig(InputRendererConfig $rendererConfig) {
        $isRequired = $this->getTableColumnConfig()->getIsRequired();
        if ($isRequired === DbColumnConfig::ON_ALL) {
            $rendererConfig->isRequired();
        } else if ($isRequired == DbColumnConfig::ON_CREATE) {
            $rendererConfig->isRequiredForCreate();
        } else if ($isRequired = DbColumnConfig::ON_UPDATE) {
            $rendererConfig->isRequiredForEdit();
        }
        return $rendererConfig;
    }

}