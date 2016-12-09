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
        $newFiles = static::valueNormalizer($newValue, $isFromDb, $column);
        if (count($newFiles)) {
            $valueContainer->setCustomInfo(['new_files' => $newFiles]);
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
                if (
                    !is_array($value[$imageName])
                    || (
                        empty($value[$imageName]['file'])
                        && !(bool)array_get($value[$imageName], 'deleted', false)
                    )
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
        foreach ($column as $imageName => $imageConfig) {
            if (!array_key_exists($imageName, $value)) {
                continue;
            }
            // todo: implement multifile
            /** @var bool|\SplFileInfo $file */
            $file = array_get($value[$imageName], 'file', false);
            if (
                !ValidateValue::isUploadedImage($file, true)
                && !array_get($value[$imageName], 'deleted', false)
            ) {
                return [RecordValueHelpers::getErrorMessage($localizations, $column::VALUE_MUST_BE_IMAGE)];
            }
            $image = new \Imagick($file->getRealPath());
            if (!$image->valid() || ValidateValue::isCorruptedJpeg($file->getRealPath())) {
                return [RecordValueHelpers::getErrorMessage($localizations, $column::FILE_IS_NOT_A_VALID_IMAGE)];
            } else if (!in_array($image->getImageMimeType(), $imageConfig->getAllowedFileTypes(), true)) {
                return [
                    sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::IMAGE_TYPE_IS_NOT_ALLOWED),
                        implode(', ', array_keys($imageConfig->getAllowedFileTypes()))
                    )
                ];
            } else if ($file->getSize() / 1024 > $imageConfig->getMaxFileSize()) {
                return [
                    sprintf(
                        RecordValueHelpers::getErrorMessage($localizations, $column::FILE_SIZE_IS_TOO_LARGE),
                        $imageConfig->getMaxFileSize()
                    )
                ];
            }
        }
        return [];
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
                $dir = $imageConfig->getFolderAbsolutePath($pkValue);
                \File::cleanDirectory($dir);
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
                    $filePath = $fileInfo->getAbsoluteFilePath();
                    $imagick = new \Imagick($filePath);
                    if (
                        $imagick->getImageWidth() > $imageConfig->getMaxWidth()
                        && $imagick->resizeImage($imageConfig->getMaxWidth(), 0, $imagick::FILTER_LANCZOS, 0)
                    ) {
                        \File::delete($filePath);
                        $imagick->writeImage($filePath);
                    }
                    // update value
                    $value[$imageName] = $fileInfo->collectImageInfoForDb();
                }

            }
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
                \File::cleanDirectory($imageConfig->getFolderAbsolutePath($pkValue));
            }
        }
    }

    /**
     * Formats value according to required $format
     * @param RecordValue $valueContainer
     * @param string $format
     * @return mixed
     */
    static public function valueFormatter(RecordValue $valueContainer, $format) {
        // todo: implement formats: "array", "{image_name}"
    }
}