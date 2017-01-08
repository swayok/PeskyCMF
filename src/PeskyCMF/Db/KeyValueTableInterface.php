<?php

namespace PeskyCMF\Db;

use PeskyORM\ORM\Record;
use PeskyORM\ORM\TableInterface;

interface KeyValueTableInterface extends TableInterface {

    /**
     * @return string|null
     */
    public function getMainForeignKeyColumnName();

    /**
     * Make array that represents DB record and can be saved to DB
     * @param string $key
     * @param mixed $value
     * @param mixed $foreignKeyValue
     * @return array
     */
    static public function makeDataForRecord($key, $value, $foreignKeyValue = null);

    /**
     * Convert associative array to arrays that represent DB record and are ready for saving to DB
     * @param array $settingsAssoc - associative array of settings
     * @param mixed $foreignKeyValue
     * @param array $additionalConstantValues - contains constant values for all records (for example: admin id)
     * @return array
     */
    static public function convertToDataForRecords(array $settingsAssoc, $foreignKeyValue = null, $additionalConstantValues = []);

    /**
     * Update existing value or create new one
     * @param array $data - must contain: key, foreign_key, value
     * @return Record
     */
    static public function updateOrCreateRecord(array $data);

    /**
     * Update existing values and create new
     * @param array $records
     * @return bool
     */
    static public function updateOrCreateRecords(array $records);

    /**
     * @param string $key
     * @param mixed $foreignKeyValue - use null if there is no main foreign key column and
     *      getMainForeignKeyColumnName() method returns null
     * @param mixed $default
     * @return array
     */
    static public function getValueByKeyAndForeignKeyValue($key, $foreignKeyValue = null, $default = null);

}