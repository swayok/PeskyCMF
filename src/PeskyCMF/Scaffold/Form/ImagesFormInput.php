<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Db\Column\ImagesColumn;
use PeskyORM\ORM\RecordValue;
use Symfony\Component\HttpFoundation\File\File;

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
        $paths = $record->getValue($this->getTableColumn()->getName(), 'paths');
        $info = [];
        foreach ($paths as $imageName => $path) {
            if (is_array($path)) {
                $info[$imageName] = [];
                $key = 1;
                foreach ($path as $filePath) {
                    $file = new File($filePath);
                    $info[$imageName][] = [
                        'caption' => $file->getBasename(),
                        'size' => $file->getSize(),
                        'key' => $key
                    ];
                    $key++;
                }
            } else {
                $file = new File($path);
                $info[$imageName] = [
                    'caption' => $file->getBasename(),
                    'size' => $file->getSize(),
                    'key' => 1
                ];
            }
        }
        $urls = $record->getValue($this->getTableColumn()->getName(), 'urls_with_timestamp');
        return [
            'urls' => $urls,
            'info' => $info
        ];
    }


}