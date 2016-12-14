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
            foreach ($normaizledValue as $imageName => $imageInfo) {
                if (empty($imageInfo['file'])) {
                    $infoArrays[$imageName] = $imageInfo;
                } else {
                    $newFiles[$imageName] = $imageInfo;
                }
            }
            $valueContainer->setIsFromDb(false);
            if (!empty($newFiles)) {
                $valueContainer->setCustomInfo(['new_files' => $newFiles]);
            }
            if (!empty($infoArrays)) {
                if ($valueContainer->hasValue()) {
                    $oldValue = json_decode($valueContainer->getValue(), true);
                    if (is_array($oldValue)) {
                        $infoArrays = array_merge($oldValue, $infoArrays);
                    }
                }
                $json = json_encode($infoArrays, JSON_UNESCAPED_UNICODE);
                $valueContainer->setRawValue($infoArrays, $json, false)->setValidValue($json, $infoArrays);
            }
        }
        return $valueContainer;
    }

    static public function valueNormalizer($value, $isFromDb, Column $column) {
        if ($isFromDb) {
            return parent::valueNormalizer($value, $isFromDb, $column);
        } else if (is_array($value)) {
            $imagesNames = [];
            /** @var ImagesColumn $column */
            foreach ($column as $imageName => $imageConfig) {
                // todo: implement multifile
                $imagesNames[] = $imageName;
                if (empty($value[$imageName])) {
                    unset($value[$imageName]);
                    continue;
                }
                if ($value[$imageName] instanceof \SplFileInfo) {
                    $value[$imageName] = ['file' => $value[$imageName]];
                }
                if (!is_array($value[$imageName])) {
                    unset($value[$imageName]);
                } else if (static::isFileInfoArray($value[$imageName])) {
                    // not an upload but file info
                    continue;
                } else if (
                    empty($value[$imageName]['file'])
                    && !(bool)array_get($value[$imageName], 'deleted', false)
                ) {
                    unset($value[$imageName]);
                } else {
                    $value[$imageName]['deleted'] = (bool)array_get($value[$imageName], 'deleted', false);
                }
            }
            return array_intersect_key($value, array_flip($imagesNames));
        }
        return $value;
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
            // todo: implement multifile
            if (static::isFileInfoArray($value[$imageName])) {
                continue;
            }
            /** @var bool|\SplFileInfo $file */
            $file = array_get($value[$imageName], 'file', false);
            if (
                !ValidateValue::isUploadedImage($file, true)
                && !array_get($value[$imageName], 'deleted', false)
            ) {
                $errors[] = sprintf(
                    RecordValueHelpers::getErrorMessage($localizations, $column::VALUE_MUST_BE_IMAGE),
                    $imageName
                );
            }
            $image = new \Imagick($file->getRealPath());
            if (!$image->valid() || ValidateValue::isCorruptedJpeg($file->getRealPath())) {
                $errors[] = sprintf(
                    RecordValueHelpers::getErrorMessage($localizations, $column::FILE_IS_NOT_A_VALID_IMAGE),
                    $imageName
                );
            } else if (!in_array($image->getImageMimeType(), $imageConfig->getAllowedFileTypes(), true)) {
                $errors[] = sprintf(
                    RecordValueHelpers::getErrorMessage($localizations, $column::IMAGE_TYPE_IS_NOT_ALLOWED),
                    $imageName,
                    implode(', ', array_keys($imageConfig->getAllowedFileTypes()))
                );
            } else if ($file->getSize() / 1024 > $imageConfig->getMaxFileSize()) {
                $errors[] = sprintf(
                    RecordValueHelpers::getErrorMessage($localizations, $column::FILE_SIZE_IS_TOO_LARGE),
                    $imageName,
                    $imageConfig->getMaxFileSize()
                );
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
        $newFiles = $valueContainer->getCustomInfo('new_files', []);
        /** @var ImagesColumn $column */
        $column = $valueContainer->getColumn();
        $pkValue = $valueContainer->getRecord()->getPrimaryKeyValue();
        if (!empty($newFiles)) {
            $value = $valueContainer->getRecord()->getValue($valueContainer->getColumn()->getName(), 'array');
            foreach ($newFiles as $imageName => $uploadInfo) {
                // todo: implement multifile
                $imageConfig = $column->getImageConfiguration($imageName);
                $dir = $imageConfig->getAbsolutePathToFileFolder($pkValue);
                \File::cleanDirectory($dir); //< todo: for multiple files - delete file and subdirectory with same name as file without extension
                $file = array_get($uploadInfo, 'file', false);
                if ($file) {
                    $fileInfo = FileInfo::fromSplFileInfo($file, $imageConfig, $pkValue);
                    // save not modified file to $dir
                    if ($file instanceof UploadedFile) {
                        $file->move($dir, $fileInfo->getFileNameWithExtension());
                    } else {
                        /** @var \SplFileInfo $file */
                        \File::copy($file->getRealPath(), $dir . $fileInfo->getFileNameWithExtension());
                    }
                    // modify image size if needed
                    /*$filePath = $fileInfo->getAbsoluteFilePath();
                    $imagick = new \Imagick($filePath);
                    if (
                        $imagick->getImageWidth() > $imageConfig->getMaxWidth()
                        && $imagick->resizeImage($imageConfig->getMaxWidth(), 0, $imagick::FILTER_LANCZOS, 0)
                    ) {
                        \File::delete($filePath);
                        $imagick->writeImage($filePath);
                    }*/
                    // update value
                    $value[$imageName] = $fileInfo->collectImageInfoForDb();
                }
            }
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
                    if ($imageConfig->getMaxFilesCount() > 1) {
                        $ret = [];
                        if (!empty($value[$format]) && is_array($value[$format])) {
                            foreach ($value[$format] as $imageInfoArray) {
                                if (static::isFileInfoArray($imageInfoArray)) {
                                    $imageInfo = FileInfo::fromArray($imageInfoArray, $imageConfig, $pkValue);
                                    $ret[$imageInfo->getFileNumber()] = $imageInfo;
                                }
                            }
                        }
                        return $ret;
                    } else {
                        $fileInfoArray = static::isFileInfoArray($value[$format]) ? $value[$format] : [];
                        return FileInfo::fromArray($fileInfoArray, $imageConfig, $pkValue);
                    }
                },
                true
            );
        } else if ($format === 'urls' || $format === 'paths') {
            return $valueContainer->getCustomInfo(
                'format:' . $format,
                function () use ($valueContainer, $format, $column) {
                    $value = parent::valueFormatter($valueContainer, 'array');
                    $pkValue = $valueContainer->getRecord()->getPrimaryKeyValue();
                    $ret = [];
                    foreach ($value as $imageName => $imageInfo) {
                        if (is_array($imageInfo) && static::isFileInfoArray($imageInfo)) {
                            $imageConfig = $column->getImageConfiguration($imageName);
                            if ($imageConfig->getMaxFilesCount() === 1) {
                                $fileInfo = FileInfo::fromArray($imageInfo, $imageConfig, $pkValue);
                                if ($format === 'urls') {
                                    $ret[$imageName] = $fileInfo->getAbsoluteUrl();
                                } else {
                                    $ret[$imageName] = $fileInfo->getAbsoluteFilePath();
                                }
                            } else {
                                $ret[$imageName] = [];
                                foreach ($imageInfo as $index => $realImageInfo) {
                                    $fileInfo = FileInfo::fromArray($realImageInfo, $imageConfig, $pkValue);
                                    if ($format === 'urls') {
                                        $ret[$imageName][$fileInfo->getFileNumber()] = $fileInfo->getAbsoluteUrl();
                                    } else {
                                        $ret[$imageName][$fileInfo->getFileNumber()] = $fileInfo->getAbsoluteFilePath();
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