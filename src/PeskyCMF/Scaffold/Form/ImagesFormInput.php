<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Db\Column\ImagesColumn;
use PeskyORM\ORM\RecordValue;

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
     * @throws \PeskyCMF\Scaffold\ValueViewerException
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
     * @throws \PeskyCMF\Scaffold\ValueViewerException
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
            ->setTemplate('cmf::input.image_uploaders')
            ->addData('imagesConfigs', $configs);
        return $renderer;
    }

    public function hasLabel() {
        return true;
    }

    public function getLabel($default = '', InputRenderer $renderer = null) {
        return '';
    }

    public function doDefaultValueConversionByType($value, $type, array $record) {
        $value = json_decode($value, true);
        if (!is_array($value)) {
            return [];
        }
        $record = $this->getScaffoldSectionConfig()->getTable()->newRecord()->fromDbData($record);
        return $record->getValue($this->getTableColumn()->getName(), 'urls');
    }


}