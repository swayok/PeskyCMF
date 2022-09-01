<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORMColumns\Column\Files\MetadataFilesColumn;
use PeskyORMColumns\Column\Files\Utils\DbFileInfo;
use PeskyORMColumns\Column\Files\Utils\MimeTypesHelper;
use PeskyORMLaravel\Db\LaravelUploadedTempFileInfo;

/**
 * @method MetadataFilesColumn getTableColumn()
 * todo: upgrade this to be able to use MetadataFileColumn
 */
class AsyncFilesFormInput extends FormInput
{
    
    protected string $view = 'cmf::input.async_files_uploader';
    
    protected array $jsPluginOptions = [];
    
    protected \Closure|array $fileConfigsToUse = [];
    
    /**
     * @param array $options
     * @return static
     */
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
        $this->fileConfigsToUse = $fileGroups;
        return $this;
    }
    
    /**
     * @throws \BadMethodCallException
     */
    protected function getDefaultRenderer(): \Closure
    {
        return function () {
            $this->validateLinkedColumnClass();
            $configs = $this->getAcceptedFileConfigurations();
            if (empty($configs)) {
                throw new \BadMethodCallException(
                    "There is no configurations for files in column '{$this->getTableColumn()->getName()}'"
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
     * @throws \BadMethodCallException
     */
    protected function validateLinkedColumnClass(): void
    {
        if (!($this->getTableColumn() instanceof MetadataFilesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . MetadataFilesColumn::class
            );
        }
    }
    
    /**
     * @throws \UnexpectedValueException
     */
    protected function getAcceptedFileConfigurations(): array
    {
        /*$column = $this->getTableColumn();
        $configsNames = value($this->fileConfigsToUse);
        if ($configsNames === null) {
            return $column->getMetadataGroupName();
        } elseif (!is_array($configsNames)) {
            throw new \UnexpectedValueException(
                static::class . '->fileConfigsToUse property must be an array or \Closure that returns an array'
            );
        } else {
            $ret = [];
            foreach ($configsNames as $name) {
                $ret[$name] = $column->getFilesGroupConfiguration($name);
            }
            return $ret;
        }*/
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
        $ret = [];
        $recordObject = $this->getScaffoldSectionConfig()->getTable()->newRecord();
        $recordObject->enableTrustModeForDbData()->enableReadOnlyMode();
        $recordObject->fromData($record, true, false);
        // todo: upgrade this
        $fileInfoArrays = $recordObject->getValue($this->getTableColumn()->getName(), 'file_info_arrays');
        /** @var DbFileInfo[] $fileInfoArray */
        foreach ($fileInfoArrays as $groupName => $fileInfoArray) {
            if (empty($fileInfoArray)) {
                continue;
            }
            foreach ($fileInfoArray as $fileInfo) {
                $info = [
                    'is_image' => $fileInfo->getMimeType() === MimeTypesHelper::TYPE_IMAGE,
                    'name' => $fileInfo->getOriginalFileNameWithExtension(),
                    'size' => $fileInfo->getFileSize(),
                    'url' => $fileInfo->getAbsoluteFileUrl(),
                    'type' => $fileInfo->getMimeType(),
                    'uuid' => $fileInfo->getUuid(),
                    'uploaded_file_info' => 'uuid:' . $fileInfo->getUuid(),
                ];
                if ($info['is_image']) {
                    [$info['width'], $info['height']] = getimagesize($fileInfo->getFilePath());
                }
                $ret[$groupName][] = $info;
            }
        }
        return $ret;
    }
    
    public function getValidators(bool $isCreation): array
    {
        $validators = [];
        $configs = $this->getAcceptedFileConfigurations();
        foreach ($configs as $fileConfig) {
            $baseName = $this->getName() . '.' . $fileConfig->getName();
            $minFilesCount = $fileConfig->getMinFilesCount();
            $isRequired = $minFilesCount > 0 ? 'required|' : '';
            $validators[$baseName] = $isRequired . 'array|max:' . $fileConfig->getMaxFilesCount() . '|min:' . $minFilesCount;
            for ($i = 0; $i < $fileConfig->getMaxFilesCount(); $i++) {
                if ($i < $minFilesCount) {
                    $validators["{$baseName}.{$i}"] = 'required|string';
                } else {
                    $validators["{$baseName}.{$i}"] = 'nullable|string';
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
    
    public function uploadTempFile(Request $request): Response
    {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        $configs = $this->getAcceptedFileConfigurations();
        $data = $scaffoldConfig->validate($request, [
            'group' => 'required|string|in:' . implode(',', array_keys($configs)),
            'id' => 'sometimes',
        ]);
        // todo: attach file to record if record ID is provided
        $groupConfig = $configs[$data['group']];
        $scaffoldConfig->validate($request, [
            'file' => 'required|file|mimetypes:' . implode(',', $groupConfig->getAllowedMimeTypes()) . '|max:' . $groupConfig->getMaxFileSize(),
        ]);
        $tempFile = new LaravelUploadedTempFileInfo($request->file('file'), true);
        
        return response($tempFile->encode());
    }
    
    public function deleteTempFile(Request $request): Response|CmfJsonResponse
    {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        $scaffoldConfig->validate($request, [
            'info' => 'required|string',
        ]);
        $pkValue = $request->input('id');
        if ($pkValue) {
            // todo: delete real file and save record
            return cmfJsonResponse(HttpCode::SERVER_ERROR);
        } else {
            $tempFile = new LaravelUploadedTempFileInfo($request->input('info'));
            if (!$tempFile->isValid()) {
                return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                    ->setMessage($scaffoldConfig->translateGeneral('input.async_files_uploads.invalid_encoded_info'));
            }
            $tempFile->delete();
            return response('ok');
        }
    }
    
    public function getConfigsArrayForJs(string $filesGroupName): array
    {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        return [
            'upload_url' => $scaffoldConfig->getUrlForTempFileUpload($this->getName(false)),
            'delete_url' => $scaffoldConfig->getUrlForTempFileDelete($this->getName(false)),
        ];
    }
    
}
