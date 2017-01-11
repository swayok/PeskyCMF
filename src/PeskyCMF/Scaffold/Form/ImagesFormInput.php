<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyCMF\Db\Column\ImagesColumn;
use PeskyCMF\Db\Column\Utils\FileInfo;

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

    public function doDefaultValueConversionByType($value, $type, array $data) {
        $ret = [
            'urls' => [],
            'preview_info' => [],
            'files' => []
        ];
        if (!is_array($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value) || empty($value)) {
            return $ret;
        }
        $record = $this->getScaffoldSectionConfig()->getTable()->newRecord();
        $pkValue = array_get($data, $record::getPrimaryKeyColumnName());
        $record->fromData($data, !empty($pkValue) || is_numeric($pkValue), false);

        $fileInfoArrays = $record->getValue($this->getTableColumn()->getName(), 'file_info_arrays');
        foreach ($fileInfoArrays as $imageName => $fileInfoArray) {
            if (empty($fileInfoArray)) {
                continue;
            }
            $ret['preview_info'][$imageName] = [];
            $ret['urls'][$imageName] = [];
            $ret['files'][$imageName] = [];
            /** @var FileInfo $fileInfo */
            foreach ($fileInfoArray as $fileInfo) {
                $ret['urls'][$imageName][] = $fileInfo->getAbsoluteUrl();
                $ret['files'][$imageName][] = [
                    'info' => $fileInfo->getCustomInfo(),
                    'name' => $fileInfo->getFileName(),
                    'extension' => $fileInfo->getFileExtension(),
                ];
                $ret['preview_info'][$imageName][] = [
                    'caption' => $fileInfo->getFileNameWithExtension(),
                    'size' => filesize($fileInfo->getAbsoluteFilePath()),
                    'key' => 1
                ];
            }
        }
        return $ret;
    }

    public function getValidators() {
        /** @var ImagesColumn $column */
        $column = $this->getTableColumn();
        $validators = [];
        foreach ($column as $imageConfig) {
            $baseName = $this->getName() . '.' . $imageConfig->getName();
            $isRequired = $imageConfig->getMinFilesCount() > 0 ? 'required|' : '';
            $validators[$baseName] = $isRequired . 'array|max:' . $imageConfig->getMaxFilesCount();
            $commonValidators = 'image|max:' . $imageConfig->getMaxFileSize()
                . '|mimetypes:' . implode(',', $imageConfig->getAllowedFileTypes());
            for ($i = 0; $i < $imageConfig->getMaxFilesCount(); $i++) {
                if ($imageConfig->getMinFilesCount() > $i) {
                    $validators["{$baseName}.{$i}.file"] = "required_without:{$baseName}.{$i}.old_file|{$commonValidators}";
                    $validators["{$baseName}.{$i}.old_file"] = "required_without:{$baseName}.{$i}.file|{$commonValidators}";
                }
            }
            $validators["{$baseName}.*.file"] = $commonValidators;
        }
        return $validators;
    }


}