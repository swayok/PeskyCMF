<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\ImagesColumn;
use PeskyORMLaravel\Db\Column\Utils\FileConfig;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use PeskyORMLaravel\Db\Column\Utils\ImageConfig;

/**
 * @method ImagesColumn|FilesColumn getTableColumn()
 */
class ImagesFormInput extends FormInput {

    /** @var null|array */
    protected $fileConfigsToUse;

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_HIDDEN;
    }

    /**
     * List of image names to accept.
     * Only provided images will be shown in form. Other images will be ignored (and won't be changed in any way)
     * @param array $imageNames
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setImageConfigsToUse(...$imageNames) {
        if (empty($imageNames)) {
            throw new \InvalidArgumentException('$imageNames argument cannot be empty');
        }
        if (count($imageNames) === 1 && isset($imageNames[0]) && is_array($imageNames[0])) {
            $imageNames = $imageNames[0];
        }
        $this->fileConfigsToUse = $imageNames;
        return $this;
    }

    /**
     * @return \Closure
     * @throws \PeskyCMF\Scaffold\ScaffoldException
     * @throws \UnexpectedValueException
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
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
     * @throws \PeskyCMF\Scaffold\ValueViewerConfigException
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
        $configs = $this->getAcceptedFileConfigurations();
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

    /**
     * @return ImageConfig[]|FileConfig[]
     * @throws \UnexpectedValueException
     */
    protected function getAcceptedFileConfigurations() {
        $column = $this->getTableColumn();
        if (empty($this->fileConfigsToUse)) {
            return $column->getImagesConfigurations();
        } else {
            $ret = [];
            foreach ($this->fileConfigsToUse as $name) {
                $ret[$name] = $column->getFileConfiguration($name);
            }
            return $ret;
        }
    }

    public function hasLabel() {
        return true;
    }

    public function getLabel(InputRenderer $renderer = null) {
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
                if ($fileInfo->exists()) {
                    $ret['urls'][$imageName][] = $fileInfo->getAbsoluteUrl();
                    $ret['files'][$imageName][] = [
                        'info' => $fileInfo->getCustomInfo(),
                        'name' => $fileInfo->getOriginalFileName() ?: $fileInfo->getFileName(),
                        'extension' => $fileInfo->getFileExtension(),
                        'uuid' => $fileInfo->getUuid(),
                    ];
                    $ret['preview_info'][$imageName][] = [
                        'caption' => $fileInfo->getOriginalFileNameWithExtension(),
                        'size' => filesize($fileInfo->getAbsoluteFilePath()),
                        'downloadUrl' => $fileInfo->getAbsoluteUrl(),
                        'key' => 1
                    ];
                }
            }
        }
        return $ret;
    }

    public function getValidators($isCreation) {
        $validators = [];
        $configs = $this->getAcceptedFileConfigurations();
        foreach ($configs as $imageConfig) {
            $baseName = $this->getName() . '.' . $imageConfig->getName();
            $isRequired = $imageConfig->getMinFilesCount() > 0 ? 'required|' : '';
            $validators[$baseName] = $isRequired . 'array|max:' . $imageConfig->getMaxFilesCount();
            $commonValidators = 'nullable|image|max:' . $imageConfig->getMaxFileSize()
                . '|mimetypes:' . implode(',', $imageConfig->getAllowedFileTypes());
            for ($i = 0; $i < $imageConfig->getMaxFilesCount(); $i++) {
                if ($imageConfig->getMinFilesCount() > $i) {
                    $validators["{$baseName}.{$i}.file"] = "required_without:{$baseName}.{$i}.uuid|{$commonValidators}";
                    $validators["{$baseName}.{$i}.uuid"] = "required_without:{$baseName}.{$i}.file|nullable|string";
                } else {
                    $validators["{$baseName}.{$i}.file"] = "nullable|{$commonValidators}";
                    $validators["{$baseName}.{$i}.uuid"] = 'nullable|string';
                }
            }
        }
        return $validators;
    }


    public function hasTooltip() {
        return false; //< there can't be own tooltip for input. only image/file configs can have tooltips
    }

    public function getFormattedTooltip() {
        throw new \BadMethodCallException(
            'Tooltip for ' . get_class($this) .  ' is not allowed. There can only be tooltips for file/image configs.'
        );
    }

    /**
     * @param string $configName
     * @return string
     */
    public function getFormattedTooltipForImageConfig($configName) {
        $tooltips = $this->getTooltip();
        if (!is_array($tooltips) || empty($tooltips[$configName])) {
            return '';
        }
        return $this->buildTooltip($tooltips[$configName]);
    }


}