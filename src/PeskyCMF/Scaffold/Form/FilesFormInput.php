<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\Utils\FilesGroupConfig;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use PeskyORMLaravel\Db\Column\Utils\ImagesGroupConfig;

/**
 * @method FilesColumn getTableColumn()
 */
class FilesFormInput extends FormInput {

    /** @var string */
    protected $view = 'cmf::input.files_uploaders';

    /** @var null|array|\Closure */
    protected $fileConfigsToUse;

    /**
     * @return string
     */
    public function getType() {
        return static::TYPE_HIDDEN;
    }

    /**
     * List of file names to accept.
     * Only provided files will be shown in form. Other files will be ignored (and won't be changed in any way)
     * @param array|\Closure $fileGroups - \Closure must return array
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFileConfigsToUse($fileGroups) {
        if (empty($fileGroups)) {
            throw new \InvalidArgumentException('$fileGroups argument cannot be empty');
        } else if (!is_array($fileGroups) && $fileGroups instanceof \Closure) {
            throw new \InvalidArgumentException('$fileGroups argument must be an array or \Closure');
        }
        $this->fileConfigsToUse = $fileGroups;
        return $this;
    }

    /**
     * @return \Closure
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getRenderer() {
        if (empty($this->renderer)) {
            return function () {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
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
        if (!($column instanceof FilesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . FilesColumn::class
            );
        }
        $configs = $this->getAcceptedFileConfigurations();
        if (empty($configs)) {
            throw new \BadMethodCallException(
                "There is no configurations for files in column '{$column->getName()}'"
            );
        }
        $renderer = new InputRenderer();
        $renderer
            ->setTemplate($this->view)
            ->addData('filesConfigs', $configs);
        return $renderer;
    }

    /**
     * @return FilesGroupConfig[]
     * @throws \UnexpectedValueException
     */
    protected function getAcceptedFileConfigurations() {
        $column = $this->getTableColumn();
        $configsNames = value($this->fileConfigsToUse);
        if ($configsNames === null) {
            return $column->getFilesGroupsConfigurations();
        } else if (!is_array($configsNames)) {
            throw new \UnexpectedValueException(
                static::class . '->fileConfigsToUse property must be an array or \Closure that returns an array'
            );
        } else {
            $ret = [];
            foreach ($configsNames as $name) {
                $ret[$name] = $column->getFilesGroupConfiguration($name);
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

    /**
     * @param mixed $value
     * @param string $type
     * @param array $data
     * @return array|mixed
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\OrmException
     */
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
        foreach ($fileInfoArrays as $fileName => $fileInfoArray) {
            if (empty($fileInfoArray)) {
                continue;
            }
            $ret['preview_info'][$fileName] = [];
            $ret['urls'][$fileName] = [];
            $ret['files'][$fileName] = [];
            /** @var FileInfo $fileInfo */
            foreach ($fileInfoArray as $fileInfo) {
                if ($fileInfo->exists()) {
                    $ret['urls'][$fileName][] = $fileInfo->getAbsoluteUrl();
                    $ret['files'][$fileName][] = [
                        'info' => $fileInfo->getCustomInfo(),
                        'name' => $fileInfo->getOriginalFileName() ?: $fileInfo->getFileName(),
                        'extension' => $fileInfo->getFileExtension(),
                        'uuid' => $fileInfo->getUuid(),
                    ];

                    $ret['preview_info'][$fileName][] = [
                        'caption' => $fileInfo->getOriginalFileNameWithExtension(),
                        'size' => $fileInfo->getSize(),
                        'downloadUrl' => $fileInfo->getAbsoluteUrl(),
                        'filetype' => $fileInfo->getMimeType(),
                        'type' => static::getUploaderPreviewTypeFromFileInfo($fileInfo),
                        'key' => 1
                    ];
                }
            }
        }
        return $ret;
    }

    /**
     * @param FileInfo $fileInfo
     * @return string
     */
    static protected function getUploaderPreviewTypeFromFileInfo(FileInfo $fileInfo) {
        $type = $fileInfo->getFileType();
        switch ($type) {
            case FilesGroupConfig::TYPE_IMAGE:
            case FilesGroupConfig::TYPE_AUDIO:
            case FilesGroupConfig::TYPE_VIDEO:
            case FilesGroupConfig::TYPE_TEXT:
            case FilesGroupConfig::TYPE_OFFICE:
                return $type;
            default:
                return 'object';
        }
    }

    public function getValidators($isCreation) {
        $validators = [];
        $configs = $this->getAcceptedFileConfigurations();
        foreach ($configs as $fileConfig) {
            $baseName = $this->getName() . '.' . $fileConfig->getName();
            $isRequired = $fileConfig->getMinFilesCount() > 0 ? 'required|' : '';
            $validators[$baseName] = $isRequired . 'array|max:' . $fileConfig->getMaxFilesCount();
            $commonValidators = 'nullable|' . ($fileConfig instanceof ImagesGroupConfig ? 'image' : 'file') . '|max:' . $fileConfig->getMaxFileSize()
                . '|mimetypes:' . implode(',', $fileConfig->getAllowedMimeTypes());
            for ($i = 0; $i < $fileConfig->getMaxFilesCount(); $i++) {
                if ($fileConfig->getMinFilesCount() > $i) {
                    $validators["{$baseName}.{$i}.file"] = "required_without:{$baseName}.{$i}.uuid|required_if:{$baseName}.{$i}.deleted,1|{$commonValidators}";
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

    /**
     * @return string|void
     * @throws \BadMethodCallException
     */
    public function getFormattedTooltip() {
        throw new \BadMethodCallException(
            'Tooltip for ' . get_class($this) .  ' is not allowed. There can only be tooltips for file/image configs.'
        );
    }

    /**
     * @param string $configName
     * @return string
     */
    public function getFormattedTooltipForFileConfig($configName) {
        $tooltips = $this->getTooltip();
        if (!is_array($tooltips) || empty($tooltips[$configName])) {
            return '';
        }
        return $this->buildTooltip($tooltips[$configName]);
    }


}