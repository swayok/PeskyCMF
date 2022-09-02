<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\MetadataFilesColumn;
use PeskyORMColumns\Column\Files\Utils\DbFileInfo;
use PeskyORMColumns\Column\Files\Utils\MimeTypesHelper;

/**
 * @method MetadataFilesColumn getTableColumn()
 * todo: upgrade this to be able to use MetadataFileColumn
 */
class FilesFormInput extends FormInput
{
    
    /** @var string */
    protected string $view = 'cmf::input.files_uploader';
    
    protected array $jsPluginOptions = [];
    
    /**
     * Disable uploading preview for this input
     */
    public function disablePreview(): static
    {
        $this->jsPluginOptions['showPreview'] = false;
        return $this;
    }
    
    public function setJsPluginOptions(array $options): static
    {
        $this->jsPluginOptions = $options;
        return $this;
    }
    
    public function getJsPluginOptions(): array
    {
        return $this->jsPluginOptions;
    }
    
    public function getType(): string
    {
        return static::TYPE_HIDDEN;
    }
    
    /**
     * List of file names to accept.
     * Only provided files will be shown in form.
     * Other files will be ignored (and won't be changed in any way).
     * $fileGroups as \Closure must return array.
     * @throws \InvalidArgumentException
     */
    public function setFilesGroupsToUse(array|\Closure $fileGroups): static
    {
        if (empty($fileGroups)) {
            throw new \InvalidArgumentException('$fileGroups argument cannot be empty');
        }
//        $this->fileConfigsToUse = $fileGroups;
        return $this;
    }
    
    /**
     * @throws \BadMethodCallException
     */
    protected function getDefaultRenderer(): \Closure
    {
        return function () {
            $column = $this->getTableColumn();
            if (!($column instanceof MetadataFilesColumn)) {
                throw new \BadMethodCallException(
                    "Linked column for form field '{$this->getName()}' must be an instance of " . MetadataFilesColumn::class
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
        };
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    protected function getAcceptedFileConfigurations(): array
    {
//        $column = $this->getTableColumn();
//        $configsNames = value($this->fileConfigsToUse);
//        if ($configsNames === null) {
//            return $column->getFilesGroupsConfigurations();
//        } elseif (!is_array($configsNames)) {
//            throw new \UnexpectedValueException(
//                static::class . '->fileConfigsToUse property must be an array or \Closure that returns an array'
//            );
//        } else {
//            $ret = [];
//            foreach ($configsNames as $name) {
//                $ret[$name] = $column->getFilesGroupConfiguration($name);
//            }
//            return $ret;
//        }
        // todo: upgrade this
        return [];
    }
    
    public function hasLabel(): bool
    {
        return true;
    }
    
    public function getLabel(?InputRenderer $renderer = null): string
    {
        return '';
    }
    
    public function doDefaultValueConversionByType(mixed $value, string $type, array $record): array
    {
        $ret = [
            'urls' => [],
            'preview_info' => [],
            'files' => [],
        ];
        if (!is_array($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value) || empty($value)) {
            return $ret;
        }
        $recordObject = $this->makeRecordObjectFromArray($record);
        
        $fileInfoArrays = $recordObject->getValue($this->getTableColumn()->getName(), 'file_info_arrays');
        foreach ($fileInfoArrays as $fileName => $fileInfoArray) {
            if (empty($fileInfoArray)) {
                continue;
            }
            $ret['preview_info'][$fileName] = [];
            $ret['urls'][$fileName] = [];
            $ret['files'][$fileName] = [];
            /** @var DbFileInfo $fileInfo */
            foreach ($fileInfoArray as $fileInfo) {
                if ($fileInfo->isFileExists()) {
                    $ret['urls'][$fileName][] = $fileInfo->getAbsoluteFileUrl();
                    $ret['files'][$fileName][] = [
                        'name' => $fileInfo->getOriginalFileNameWithExtension() ?: $fileInfo->getFileNameWithExtension(),
                        'extension' => $fileInfo->getFileExtension(),
                        'uuid' => $fileInfo->getUuid(),
                    ];
                    
                    $ret['preview_info'][$fileName][] = [
                        'caption' => $fileInfo->getOriginalFileNameWithExtension(),
                        'size' => $fileInfo->getFileSize(),
                        'downloadUrl' => $fileInfo->getAbsoluteFileUrl(),
                        'filetype' => $fileInfo->getMimeType(),
                        'type' => static::getUploaderPreviewTypeFromFileInfo($fileInfo),
                        'key' => 1,
                    ];
                }
            }
        }
        return $ret;
    }
    
    protected static function getUploaderPreviewTypeFromFileInfo(DbFileInfo $fileInfo): string
    {
        $type = $fileInfo->getMimeType();
        return match ($type) {
            MimeTypesHelper::TYPE_IMAGE,
            MimeTypesHelper::TYPE_AUDIO,
            MimeTypesHelper::TYPE_VIDEO,
            MimeTypesHelper::TYPE_TEXT,
            MimeTypesHelper::TYPE_OFFICE => $type,
            
            default => 'object',
        };
    }
    
    public function getValidators(bool $isCreation): array
    {
        $validators = [];
        $configs = $this->getAcceptedFileConfigurations();
        foreach ($configs as $fileConfig) {
            $baseName = $this->getName() . '.' . $fileConfig->getName();
            $isRequired = $fileConfig->getMinFilesCount() > 0 ? 'required|' : '';
            $validators[$baseName] = $isRequired . 'array|max:' . $fileConfig->getMaxFilesCount();
            $commonValidators = 'nullable|' . ($this->getTableColumn()->isItAnImage() ? 'image' : 'file') . '|max:' . $fileConfig->getMaxFileSize()
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
    
    public function hasTooltip(): bool
    {
        return false; //< there can't be own tooltip for input. only image/file configs can have tooltips
    }
    
    /**
     * @throws \BadMethodCallException
     */
    public function getFormattedTooltip(): string
    {
        throw new \BadMethodCallException(
            'Tooltip for ' . get_class($this) . ' is not allowed. There can only be tooltips for file/image configs.'
        );
    }
    
    public function getFormattedTooltipForFileConfig(string $configName): string
    {
        $tooltips = $this->getTooltip();
        if (!is_array($tooltips) || empty($tooltips[$configName])) {
            return '';
        }
        return $this->buildTooltip($tooltips[$configName]);
    }
    
    
}