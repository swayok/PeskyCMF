<?php

namespace PeskyCMF\Db\Column\Utils;

use Illuminate\Http\UploadedFile;
use PeskyCMF\Db\Column\ImagesColumn;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORM\ORM\RecordValue;
use PeskyORM\ORM\RecordValueHelpers;
use Swayok\Utils\ValidateValue;

class ImagesUploadingColumnClosures extends DefaultColumnClosures{

    /**
     * Set value. Should also normalize and validate value
     * @param mixed $newValue
     * @param boolean $isFromDb
     * @param RecordValue $valueContainer
     * @return RecordValue
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    static public function valueSetter($newValue, $isFromDb, RecordValue $valueContainer) {
        if ($isFromDb || empty($newValue)) {
            return parent::valueSetter($newValue, $isFromDb, $valueContainer);
        }
        /** @var ImagesColumn $column */
        $column = $valueContainer->getColumn();
        $errors = $column->validateValue($newValue, $isFromDb);
        if (count($errors) > 0) {
            return $valueContainer->setValidationErrors($errors);
        }
        /** @var array $newValue */
        $normaizledValue = static::valueNormalizer($newValue, $isFromDb, $column);
        if (count($normaizledValue)) {
            $newFiles = [];
            $infoArrays = [];
            foreach ($normaizledValue as $imageName => $images) {
                foreach ($images as $idx => $imageInfo) {
                    if (array_key_exists('file', $imageInfo)) {
                        if (!array_key_exists($imageName, $newFiles)) {
                            $newFiles[$imageName] = [];
                        }
                        $newFiles[$imageName][$idx] = $imageInfo;
                    } else {
                        if (!array_key_exists($imageName, $infoArrays)) {
                            $infoArrays[$imageName] = [];
                        }
                        $infoArrays[$imageName][$idx] = $imageInfo;
                    }
                }
            }
            $valueContainer->setIsFromDb(false);
            if (!empty($infoArrays)) {
                if ($valueContainer->hasValue()) {
                    $oldValue = json_decode($valueContainer->getValue(), true);
                    if (is_array($oldValue)) {
                        $infoArrays = array_merge(static::valueNormalizer($oldValue, false, $column), $infoArrays);
                    }
                }
                $json = json_encode($infoArrays, JSON_UNESCAPED_UNICODE);
                $valueContainer->setRawValue($infoArrays, $json, false)->setValidValue($json, $infoArrays);
            }
            if (!empty($newFiles)) {
                $valueContainer->setDataForSavingExtender($newFiles);
            }
        }
        return $valueContainer;
    }

    /**
     * @param mixed $value
     * @param bool $isFromDb
     * @param Column|ImagesColumn $column
     * @return array
     */
    static public function valueNormalizer($value, $isFromDb, Column $column) {
        if ($isFromDb && !is_array($value)) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            return [];
        }
        $imagesNames = [];
        /** @var ImagesColumn $column */
        /** @var ImageConfig $imageConfig */
        foreach ($column as $imageName => $imageConfig) {
            $imagesNames[] = $imageName;
            if (empty($value[$imageName])) {
                unset($value[$imageName]);
                continue;
            }
            if ($value[$imageName] instanceof \SplFileInfo) {
                $value[$imageName] = [['file' => $value[$imageName]]];
            }
            if (!is_array($value[$imageName])) {
                unset($value[$imageName]);
            }
            if (static::isFileInfoArray($value[$imageName])) {
                // not an upload but file info
                $value[$imageName] = [$value[$imageName]];
                continue;
            } else {
                if (array_has($value[$imageName], 'file') || array_has($value[$imageName], 'deleted')) {
                    // normalize uploaded file info to be indexed array with file uploads inside
                    $value[$imageName] = [$value[$imageName]];
                }
                $normailzedData = [];
                foreach ($value[$imageName] as $idx => $fileUploadInfo) {
                    if (
                        !is_int($idx)
                        || (
                            empty($fileUploadInfo['file'])
                            && !(bool)array_get($fileUploadInfo, 'deleted', false)
                        )
                    ) {
                        continue;
                    }
                    $fileUploadInfo['deleted'] = (bool)array_get($fileUploadInfo, 'deleted', false);
                    $normailzedData[$idx] = $fileUploadInfo;
                }
                $value[$imageName] = $normailzedData;
            }
        }
        return array_intersect_key($value, array_flip($imagesNames));
    }


    /**
     * Validates value. Uses valueValidatorExtender
     * @param RecordValue|mixed $value
     * @param bool $isFromDb
     * @param Column|ImagesColumn $column
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function valueValidator($value, $isFromDb, Column $column) {
        if ($isFromDb || is_string($value)) {
            return parent::valueValidator($value, $isFromDb, $column);
        }
        $localizations = $column::getValidationErrorsLocalization();
        if (!is_array($value)) {
            return [RecordValueHelpers::getErrorMessage($localizations, $column::VALUE_MUST_BE_ARRAY)];
        }
        $value = static::valueNormalizer($value, $isFromDb, $column);
        /** @var ImagesColumn $column */
        $errors = [];
        foreach ($column as $imageName => $imageConfig) {
            if (!array_key_exists($imageName, $value)) {
                continue;
            }
            foreach ($value[$imageName] as $idx => $fileUploadOrFileInfo) {
                if (static::isFileInfoArray($fileUploadOrFileInfo)) {
                    continue;
                }
                /** @var bool|\SplFileInfo $file */
                $file = array_get($fileUploadOrFileInfo, 'file', false);
                $isUploadedImage = ValidateValue::isUploadedImage($file, true);
                if (
                    !$isUploadedImage
                    && !array_get($fileUploadOrFileInfo, 'deleted', false)
                ) {
                    $errors[$idx] = sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::VALUE_MUST_BE_IMAGE),
                        $imageName
                    );
                }
                if (!$isUploadedImage) {
                    // only file deletion requested
                    continue;
                }
                $image = new \Imagick($file->getRealPath());
                if (!$image->valid() || ($image->getImageMimeType() === 'image/jpeg' && ValidateValue::isCorruptedJpeg($file->getRealPath()))) {
                    $errors[$idx] = sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::FILE_IS_NOT_A_VALID_IMAGE),
                        $imageName
                    );
                } else if (!in_array($image->getImageMimeType(), $imageConfig->getAllowedFileTypes(), true)) {
                    $errors[$idx] = sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::IMAGE_TYPE_IS_NOT_ALLOWED),
                        $image->getImageMimeType(),
                        $imageName,
                        implode(', ', $imageConfig->getAllowedFileTypes())
                    );
                } else if ($file->getSize() / 1024 > $imageConfig->getMaxFileSize()) {
                    $errors[$idx] = sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::FILE_SIZE_IS_TOO_LARGE),
                        $imageName,
                        $imageConfig->getMaxFileSize()
                    );
                }
            }
        }
        return $errors;
    }

    /**
     * @param array $value
     * @return bool
     */
    static protected function isFileInfoArray(array $value) {
        return !empty($value['name']) && !empty($value['extension']);
    }

    /**
     * Additional actions after value saving to DB (or instead of saving if column does not exist in DB)
     * @param RecordValue $valueContainer
     * @param bool $isUpdate
     * @param array $savedData
     * @return void
     * @throws \PeskyORM\Exception\RecordNotFoundException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    static public function valueSavingExtender(RecordValue $valueContainer, $isUpdate, array $savedData) {
        /** @var array $newFiles */
        $newFiles = $valueContainer->pullDataForSavingExtender();
        /** @var ImagesColumn $column */
        $column = $valueContainer->getColumn();
        $pkValue = $valueContainer->getRecord()->getPrimaryKeyValue();
        $baseSuffix = time();
        if (!empty($newFiles)) {
            $value = $valueContainer->getRecord()->getValue($valueContainer->getColumn()->getName(), 'array');
            foreach ($newFiles as $imageName => $fileUploads) {
                $imageConfig = $column->getImageConfiguration($imageName);
                $dir = $imageConfig->getAbsolutePathToFileFolder($pkValue);
                if ($imageConfig->getMaxFilesCount() === 1) {
                    \File::cleanDirectory($dir);
                }
                $filesSaved = 0;
                foreach ($fileUploads as $idx => $uploadInfo) {
                    if (
                        $imageConfig->getMaxFilesCount() > 1
                        && array_has($uploadInfo, 'old_file')
                        && static::isFileInfoArray($uploadInfo['old_file'])
                    ) {
                        $existingFileInfo = FileInfo::fromArray($uploadInfo['old_file'], $imageConfig, $pkValue);
                        \File::delete($existingFileInfo->getAbsoluteFilePath());
                        \File::cleanDirectory($existingFileInfo->getAbsolutePathToModifiedImagesFolder());
                    }
                    $file = array_get($uploadInfo, 'file', false);
                    if ($file) {
                        $fileInfo = FileInfo::fromSplFileInfo($file, $imageConfig, $pkValue, $baseSuffix + $filesSaved);
                        $filesSaved++;
                        // save not modified file to $dir
                        if ($file instanceof UploadedFile) {
                            $file->move($dir, $fileInfo->getFileNameWithExtension());
                        } else {
                            /** @var \SplFileInfo $file */
                            \File::copy($file->getRealPath(), $dir . $fileInfo->getFileNameWithExtension());
                        }
                        // modify image size if needed
                        $filePath = $fileInfo->getAbsoluteFilePath();
                        $imagick = new \Imagick($filePath);
                        if (
                            $imagick->getImageWidth() > $imageConfig->getMaxWidth()
                            && $imagick->resizeImage($imageConfig->getMaxWidth(), 0, $imagick::FILTER_LANCZOS, 1)
                        ) {
                            $imagick->writeImage();
                        }
                        // update value
                        $fileInfo->setCustomInfo(array_get($uploadInfo, 'info', []));
                        $value[$imageName][$idx] = $fileInfo->collectImageInfoForDb();
                    } else {
                        $value[$imageName][$idx] = '';
                    }
                }
            }
            //throw new \Exception('terminate');
            $valueContainer->removeCustomInfo('new_files');
            $valueContainer->getRecord()
                ->begin()
                ->updateValue($valueContainer->getColumn(), $value, false)
                ->commit();
        }
    }

    /**
     * Additional actions after record deleted from DB
     * @param RecordValue $valueContainer
     * @param bool $deleteFiles
     * @return void
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function valueDeleteExtender(RecordValue $valueContainer, $deleteFiles) {
        if ($deleteFiles) {
            /** @var ImagesColumn $column */
            $column = $valueContainer->getColumn();
            $pkValue = $valueContainer->getRecord()->getPrimaryKeyValue();
            foreach ($column as $imageName => $imageConfig) {
                \File::cleanDirectory($imageConfig->getAbsolutePathToFileFolder($pkValue));
                if ($pkValue) {
                    \File::cleanDirectory($imageConfig->getAbsolutePathToPublicRootFolder($pkValue));
                }
            }
        }
    }

    /**
     * Formats value according to required $format
     * @param RecordValue $valueContainer
     * @param string $format
     * @return mixed
     * @throws \PeskyORM\Exception\OrmException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    static public function valueFormatter(RecordValue $valueContainer, $format) {
        /** @var ImagesColumn $column */
        $column = $valueContainer->getColumn();
        if (isset($column[$format])) {
            return $valueContainer->getCustomInfo(
                'file_info:' . $format,
                function () use ($valueContainer, $format, $column) {
                    // return FileInfo object or array of FileInfo objects by image config name provided via $format
                    $record = $valueContainer->getRecord();
                    $value = $record->getValue($column->getName(), 'array');
                    $pkValue = $record->getPrimaryKeyValue();
                    $imageConfig = $column->getImageConfiguration($format);
                    $ret = [];
                    if (!empty($value[$format]) && is_array($value[$format])) {
                        foreach ($value[$format] as $imageInfoArray) {
                            if (static::isFileInfoArray($imageInfoArray)) {
                                $imageInfo = FileInfo::fromArray($imageInfoArray, $imageConfig, $pkValue);
                                if ($imageInfo->exists()) {
                                    $ret[] = $imageInfo;
                                }
                            }
                        }
                    }
                    return $ret;
                },
                true
            );
        } else if ($format === 'file_info_arrays') {
            return $valueContainer->getCustomInfo(
                'file_info:all',
                function () use ($valueContainer, $column) {
                    $ret = [];
                    foreach ($column as $imageConfig) {
                        $ret[$imageConfig->getName()] = static::valueFormatter($valueContainer, $imageConfig->getName());
                    }
                    return $ret;
                },
                true
            );
        } else if (in_array($format, ['urls', 'urls_with_timestamp', 'paths'], true)) {
            return $valueContainer->getCustomInfo(
                'format:' . $format,
                function () use ($valueContainer, $format, $column) {
                    $value = parent::valueFormatter($valueContainer, 'array');
                    $pkValue = $valueContainer->getRecord()->getPrimaryKeyValue();
                    $ret = [];
                    foreach ($value as $imageName => $imageInfo) {
                        if (is_array($imageInfo)) {
                            $imageConfig = $column->getImageConfiguration($imageName);
                            $ret[$imageName] = [];
                            foreach ($imageInfo as $realImageInfo) {
                                if (static::isFileInfoArray($realImageInfo)) {
                                    $fileInfo = FileInfo::fromArray($realImageInfo, $imageConfig, $pkValue);
                                    if (!$fileInfo->exists()) {
                                        continue;
                                    }
                                    if ($format === 'paths') {
                                        $ret[$imageName][$fileInfo->getFileSuffix()] = $fileInfo->getAbsoluteFilePath();
                                    } else {
                                        $ret[$imageName][$fileInfo->getFileSuffix()] = $fileInfo->getAbsoluteUrl();
                                        if ($format === 'urls_with_timestamp') {
                                            $ret[$imageName] .= '?_' . time();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    return $ret;
                },
                true
            );
        } else {
            return parent::valueFormatter($valueContainer, $format);
        }
    }
}