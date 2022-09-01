<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\MetadataFilesColumn;
use PeskyORMColumns\Column\Files\MetadataImagesColumn;
use PeskyORMColumns\Column\Files\Utils\DbFileInfo;
use PeskyORMColumns\Column\Files\Utils\MimeTypesHelper;

/**
 * @method MetadataFilesColumn getTableColumn()
 */
class FileFormInput extends FormInput
{
    
    protected string $view = 'cmf::input.files_uploader';
    
    protected array $jsPluginOptions = [];
    
    protected ?FilesUploaderConfig $filesUploaderConfig = null;
    
    protected bool $isRequired = false;
    
    public function __construct()
    {
        $this->filesUploaderConfig = new FilesUploaderConfig();
    }
    
    public function getFilesUploaderConfig(): FilesUploaderConfig
    {
        if (!$this->filesUploaderConfig) {
            $this->filesUploaderConfig = new FilesUploaderConfig();
            $column = $this->getTableColumn();
            $this->filesUploaderConfig->minFilesCount = $column->isValueCanBeNull() ? 0 : 1;
            $this->filesUploaderConfig->setAllowedFilesExtensions($column->getAllowedFileExtensions());
        } else {
            $this->filesUploaderConfig->maxFilesCount = 1;
            $this->filesUploaderConfig->minFilesCount = min(1, $this->filesUploaderConfig->minFilesCount);
        }
        return $this->filesUploaderConfig;
    }
    
    public function setFilesUploaderConfig(FilesUploaderConfig $config): static
    {
        $this->filesUploaderConfig = $config;
        return $this;
    }
    
    public function setIsRequired(bool $required): static
    {
        $this->isRequired = $required;
        return $this;
    }
    
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
            $renderer = new InputRenderer();
            $renderer
                ->setTemplate($this->view)
                ->addData('fileConfig', $this->getFilesUploaderConfig());
            return $renderer;
        };
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
        
        /** @var DbFileInfo $fileInfo */
        $fileInfo = $recordObject->getValue($this->getTableColumn()->getName());
        if ($fileInfo instanceof DbFileInfo && $fileInfo->isFileExists()) {
            $ret['urls']['default'] = [$fileInfo->getAbsoluteFileUrl()];
            $ret['files']['default'] = [
                [
                    'name' => $fileInfo->getOriginalFileNameWithExtension() ?: $fileInfo->getFileNameWithExtension(),
                    'extension' => $fileInfo->getFileExtension(),
                    'uuid' => $fileInfo->getUuid(),
                ],
            ];
            $ret['preview_info']['default'] = [
                [
                    'caption' => $fileInfo->getOriginalFileNameWithExtension(),
                    'size' => $fileInfo->getFileSize(),
                    'downloadUrl' => $fileInfo->getAbsoluteFileUrl(),
                    'filetype' => $fileInfo->getMimeType(),
                    'type' => static::getUploaderPreviewTypeFromFileInfo($fileInfo),
                    'key' => 1,
                ],
            ];
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
        $column = $this->getTableColumn();
        $baseName = $this->getName();
        $isRequired = $column->allowsNullValues() ? '' : 'required|';
        $validators[$baseName] = $isRequired . 'array';
        $commonValidators = 'nullable|' . ($column instanceof MetadataImagesColumn ? 'image' : 'file') . '|max:' . $column->getMaxFileSize()
            . '|mimetypes:' . implode(',', $column->getAllowedMimeTypes());
        if ($column->getMinFilesCount() > 0) {
            $validators["{$baseName}.file"] = "required_without:{$baseName}.uuid|required_if:{$baseName}.deleted,1|{$commonValidators}";
            $validators["{$baseName}.uuid"] = "required_without:{$baseName}.file|nullable|string";
        } else {
            $validators["{$baseName}.file"] = "nullable|{$commonValidators}";
            $validators["{$baseName}.uuid"] = 'nullable|string';
        }
        return $validators;
    }
    
}