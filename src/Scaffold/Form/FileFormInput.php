<?php

namespace PeskyCMF\Scaffold\Form;

use PeskyORMLaravel\Db\Column\FileColumn;
use PeskyORMLaravel\Db\Column\ImageColumn;
use PeskyORMLaravel\Db\Column\Utils\FileConfig;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use PeskyORMLaravel\Db\Column\Utils\ImageConfig;

/**
 * @method FileColumn|ImageColumn getTableColumn()
 */
class FileFormInput extends FormInput {

    /** @var string */
    protected $view = 'cmf::input.file_uploader';

    protected $jsPluginOptions = [];

    /**
     * Disable uploading preview for this input
     * @return $this
     */
    public function disablePreview() {
        $this->jsPluginOptions['showPreview'] = false;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setJsPluginOptions(array $options) {
        $this->jsPluginOptions = $options;
        return $this;
    }

    public function getJsPluginOptions(): array {
        return $this->jsPluginOptions;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::TYPE_HIDDEN;
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
        if (!($column instanceof FileColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . FileColumn::class
            );
        }
        $renderer = new InputRenderer();
        $renderer
            ->setTemplate($this->view)
            ->addData('fileConfig', $column->getConfiguration());
        return $renderer;
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

        /** @var FileInfo $fileInfo */
        $fileInfo = $record->getValue($this->getTableColumn()->getName(), 'file_info');
        if (!empty($fileInfo) && $fileInfo->exists()) {
            $ret['preview_info']['default'] = [];
            $ret['urls']['default'] = [];
            $ret['files']['default'] = [];
            /** @var FileInfo $fileInfo */
            $ret['urls']['default'] = [$fileInfo->getAbsoluteUrl()];
            $ret['files']['default'] = [
                [
                    'info' => $fileInfo->getCustomInfo(),
                    'name' => $fileInfo->getOriginalFileName() ?: $fileInfo->getFileName(),
                    'extension' => $fileInfo->getFileExtension(),
                    'uuid' => $fileInfo->getUuid(),
                ]
            ];
            $ret['preview_info']['default'] = [
                [
                    'caption' => $fileInfo->getOriginalFileNameWithExtension(),
                    'size' => $fileInfo->getSize(),
                    'downloadUrl' => $fileInfo->getAbsoluteUrl(),
                    'filetype' => $fileInfo->getMimeType(),
                    'type' => static::getUploaderPreviewTypeFromFileInfo($fileInfo),
                    'key' => 1
                ]
            ];
        }
        return $ret;
    }

    /**
     * @param FileInfo $fileInfo
     * @return string
     */
    protected static function getUploaderPreviewTypeFromFileInfo(FileInfo $fileInfo) {
        $type = $fileInfo->getFileType();
        switch ($type) {
            case FileConfig::TYPE_IMAGE:
            case FileConfig::TYPE_AUDIO:
            case FileConfig::TYPE_VIDEO:
            case FileConfig::TYPE_TEXT:
            case FileConfig::TYPE_OFFICE:
                return $type;
            default:
                return 'object';
        }
    }

    public function getValidators($isCreation) {
        $validators = [];
        $fileConfig = $this->getTableColumn()->getConfiguration();
        $baseName = $this->getName();
        $isRequired = $fileConfig->getMinFilesCount() > 0 ? 'required|' : '';
        $validators[$baseName] = $isRequired . 'array';
        $commonValidators = 'nullable|' . ($fileConfig instanceof ImageConfig ? 'image' : 'file') . '|max:' . $fileConfig->getMaxFileSize()
            . '|mimetypes:' . implode(',', $fileConfig->getAllowedMimeTypes());
        if ($fileConfig->getMinFilesCount() > 0) {
            $validators["{$baseName}.file"] = "required_without:{$baseName}.uuid|required_if:{$baseName}.deleted,1|{$commonValidators}";
            $validators["{$baseName}.uuid"] = "required_without:{$baseName}.file|nullable|string";
        } else {
            $validators["{$baseName}.file"] = "nullable|{$commonValidators}";
            $validators["{$baseName}.uuid"] = 'nullable|string';
        }
        return $validators;
    }

}