<?php

namespace PeskyCMF\Db\Column\Utils;

use PeskyORM\ORM\Column;
use PeskyORM\ORM\ColumnClosuresInterface;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORM\ORM\RecordValue;

class ImagesUploadingColumnClosures implements ColumnClosuresInterface {

    /**
     * Set value. Should also normalize and validate value
     * @param mixed $newValue
     * @param boolean $isFromDb
     * @param RecordValue $valueContainer
     * @return RecordValue
     */
    static public function valueSetter($newValue, $isFromDb, RecordValue $valueContainer) {

    }

    /**
     * Slightly modify value before validation. Uses $column->isEmptyStringMustBeConvertedToNull(),
     * $column->isValueLowercasingRequired() and $column->isValueTrimmingRequired
     * @param Column $column
     * @param mixed $value
     * @param bool $isFromDb
     * @return mixed
     */
    static public function valuePreprocessor($value, $isFromDb, Column $column) {
        return DefaultColumnClosures::valuePreprocessor($value, $isFromDb, $column);
    }

    /**
     * Get value
     * @param RecordValue $value
     * @param null|string $format
     * @return mixed
     */
    static public function valueGetter(RecordValue $value, $format = null) {

    }

    /**
     * Tests if value is set
     * @param RecordValue $value
     * @param bool $checkDefaultValue
     * @return bool
     */
    static public function valueExistenceChecker(RecordValue $value, $checkDefaultValue = false) {

    }

    /**
     * Validates value. Uses valueValidatorExtender
     * @param RecordValue|mixed $value
     * @param bool $isFromDb
     * @param Column $column
     * @return array
     */
    static public function valueValidator($value, $isFromDb, Column $column) {

    }

    /**
     * Extends value validation in addition to valueValidator
     * @param mixed $value
     * @param bool $isFromDb
     * @param Column $column
     * @return array - list of error messages (empty list = no errors)
     */
    static public function valueValidatorExtender($value, $isFromDb, Column $column) {

    }

    /**
     * Validates if value is allowed
     * @param RecordValue|mixed $value
     * @param bool $isFromDb
     * @param Column $column
     * @return array
     */
    static public function valueIsAllowedValidator($value, $isFromDb, Column $column) {

    }

    /**
     * Normalize value to fit column's data type
     * @param mixed $value
     * @param bool $isFromDb
     * @param Column $column
     * @return mixed
     */
    static public function valueNormalizer($value, $isFromDb, Column $column) {

    }

    /**
     * Additional actions after value saving to DB (or instead of saving if column does not exist in DB)
     * @param RecordValue $valueContainer
     * @param bool $isUpdate
     * @param array $savedData
     * @return void
     */
    static public function valueSavingExtender(RecordValue $valueContainer, $isUpdate, array $savedData) {

    }

    /**
     * Additional actions after record deleted from DB
     * @param RecordValue $valueContainer
     * @param bool $deleteFiles
     * @return void
     */
    static public function valueDeleteExtender(RecordValue $valueContainer, $deleteFiles) {

    }

    /**
     * Formats value according to required $format
     * @param RecordValue $valueContainer
     * @param string $format
     * @return mixed
     */
    static public function valueFormatter(RecordValue $valueContainer, $format) {

    }
}