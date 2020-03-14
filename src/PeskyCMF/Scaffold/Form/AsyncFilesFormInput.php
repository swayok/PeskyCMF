<?php

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyORMLaravel\Db\Column\AsyncFilesColumn;
use PeskyORMLaravel\Db\Column\FilesColumn;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use PeskyORMLaravel\Db\Column\Utils\FilesGroupConfig;
use PeskyORMLaravel\Db\Column\Utils\MimeTypesHelper;

/**
 * @method FilesColumn getTableColumn()
 */
class AsyncFilesFormInput extends FormInput {

    /** @var string */
    protected $view = 'cmf::input.async_files_uploader';

    /** @var null|array|\Closure */
    protected $fileConfigsToUse;

    protected $jsPluginOptions = [];

    protected $linkedColumnClass = AsyncFilesColumn::class;

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
    public function setFilesGroupsToUse($fileGroups) {
        if (empty($fileGroups)) {
            throw new \InvalidArgumentException('$fileGroups argument cannot be empty');
        } else if (!is_array($fileGroups) && !($fileGroups instanceof \Closure)) {
            throw new \InvalidArgumentException('$fileGroups argument must be an array or \Closure');
        }
        $this->fileConfigsToUse = $fileGroups;
        return $this;
    }

    /**
     * @return \Closure
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
     * @throws \BadMethodCallException
     */
    protected function getDefaultRenderer() {
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
    }

    /**
     * @throws \BadMethodCallException
     */
    protected function validateLinkedColumnClass() {
        if (!($this->getTableColumn() instanceof AsyncFilesColumn)) {
            throw new \BadMethodCallException(
                "Linked column for form field '{$this->getName()}' must be an instance of " . AsyncFilesColumn::class
            );
        }
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
     */
    public function doDefaultValueConversionByType($value, $type, array $data) {
        $ret = [];
        $record = $this->getScaffoldSectionConfig()->getTable()->newRecord();
        $pkValue = array_get($data, $record::getPrimaryKeyColumnName());
        $record->fromData($data, !empty($pkValue) || is_numeric($pkValue), false);
        $fileInfoArrays = $record->getValue($this->getTableColumn()->getName(), 'file_info_arrays');
        /** @var FileInfo[] $fileInfoArray */
        foreach ($fileInfoArrays as $groupName => $fileInfoArray) {
            if (empty($fileInfoArray)) {
                continue;
            }
            foreach ($fileInfoArray as $fileInfo) {
                $info = [
                    'is_image' => $fileInfo->getFileType() === MimeTypesHelper::TYPE_IMAGE,
                    'name' => $fileInfo->getOriginalFileNameWithExtension(),
                    'size' => $fileInfo->getSize(),
                    'url' => $fileInfo->getAbsoluteUrl(),
                    'type' => $fileInfo->getMimeType(),
                    'uuid' => $fileInfo->getUuid(),
                    'uploaded_file_info' => 'uuid:' . $fileInfo->getUuid(),
                ];
                if ($info['is_image']) {
                    list($info['width'], $info['height']) = getimagesize($fileInfo->getAbsoluteFilePath());
                }
                $ret[$groupName][] = $info;
            }
        }
        return $ret;
    }

    public function getValidators($isCreation) {
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

    /**
     * @param Request $request
     * @return Response
     */
    public function uploadTempFile($request) {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        $configs = $this->getAcceptedFileConfigurations();
        $data = $scaffoldConfig->validate($request, [
            'group' => 'required|string|in:' . implode(',', array_keys($configs)),
            'id' => 'sometimes'
        ]);
        // todo: attach file to record if record ID is provided
        $groupConfig = $configs[$data['group']];
        $scaffoldConfig->validate($request, [
            'file' => 'required|file|mimetypes:' . implode(',', $groupConfig->getAllowedMimeTypes()) . '|max:' . $groupConfig->getMaxFileSize()
        ]);
        $tempFile = new UploadedTempFileInfo($request->file('file'), true);

        return response($tempFile->encode());
    }

    /**
     * @param Request $request
     * @return Response|CmfJsonResponse
     */
    public function deleteTempFile($request) {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        $scaffoldConfig->validate($request, [
            'info' => 'required|string'
        ]);
        $pkValue = $request->input('id');
        if ($pkValue) {
            // todo: delete real file and save record
            return cmfJsonResponse(HttpCode::SERVER_ERROR);
        } else {
            $tempFile = new UploadedTempFileInfo($request->input('info'));
            if (!$tempFile->isValid()) {
                return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                    ->setMessage($scaffoldConfig->translateGeneral('input.async_files_uploads.invalid_encoded_info'));
            }
            $tempFile->delete();
            return response('ok');
        }
    }

    public function getConfigsArrayForJs(string $filesGroupName): array {
        $scaffoldConfig = $this->getScaffoldSectionConfig()->getScaffoldConfig();
        return [
            'upload_url' => $scaffoldConfig::getUrlForTempFileUpload($this->getName(false)),
            'delete_url' => $scaffoldConfig::getUrlForTempFileUpload($this->getName(false)),
        ];
    }

}
