<?php

namespace PeskyCMF\Db\Column\Utils;

use PeskyCMF\Db\Column\ImagesColumn;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORM\ORM\RecordValue;
use PeskyORM\ORM\RecordValueHelpers;
use Swayok\Utils\ValidateValue;

/**
 * todo: added images saving/getting (for both DB and FS)
 */
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
            foreach ($column as $imageName => $imageConfig) {
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
        foreach ($column as $imageName => $imageConfig) {
            if (
                array_key_exists($imageName, $value)
                && !array_get($value[$imageName], 'deleted', false)
                && (
                    !array_get($value[$imageName], 'file', false)
                    || !ValidateValue::isUploadedFile($value[$imageName]['file'], true)
                )
            ) {
                return [RecordValueHelpers::getErrorMessage($localizations, $column::VALUE_MUST_BE_FILE)];
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
     */
    static public function valueSavingExtender(RecordValue $valueContainer, $isUpdate, array $savedData) {
        $newFiles = $valueContainer->getCustomInfo('new_files', []);
        if (!empty($newFiles)) {
            $value = $valueContainer->getRecord()->getValue($valueContainer->getColumn()->getName(), 'array');
            foreach ($newFiles as $uploadInfo) {
                $file = array_get($uploadInfo, 'file', false);
                $delete = array_get($uploadInfo, 'delete', false);
                if (!$file && $delete) {
                    // todo: delete file
                } else if ($file) {
                    // todo: save file to fs
                }
            }
        }
    }

    /**
     * Additional actions after record deleted from DB
     * @param RecordValue $valueContainer
     * @param bool $deleteFiles
     * @return void
     */
    static public function valueDeleteExtender(RecordValue $valueContainer, $deleteFiles) {
        // todo: delete all files
    }

    /**
     * Formats value according to required $format
     * @param RecordValue $valueContainer
     * @param string $format
     * @return mixed
     */
    static public function valueFormatter(RecordValue $valueContainer, $format) {
        // todo: implement formats: "path", "url"
    }
}