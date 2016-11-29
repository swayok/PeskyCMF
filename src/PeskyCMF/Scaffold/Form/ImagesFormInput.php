<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Db\Column\ImagesColumn;

class ImagesFormInput extends FormInput {

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_HIDDEN;
    }

    /**
     * @return \Closure
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            return function () {
                return $this->getDefaultRenderer();
            };
        } else {
            return $this->renderer;
        }
    }

    /**
     * @return InputRenderer
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ScaffoldFieldException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getDefaultRenderer() {
        $column = $this->getTableColumn();
        if (!($column instanceof ImagesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . ImagesColumn::class
            );
        }
        $configs = $column->getImagesConfigurations();
        if (empty($configs)) {
            throw new \BadMethodCallException(
                "There is no configurations for images in column '{$column->getName()}'"
            );
        }
        $renderer = new InputRenderer();
        $renderer
            ->setView('cmf::input.image_uploaders')
            ->addData('imagesConfigs', $configs);
        // todo: implement view cmf::input.image_uploaders
        return $renderer;
    }


}