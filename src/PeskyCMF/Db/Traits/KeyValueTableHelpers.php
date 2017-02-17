<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\KeyValueTableInterface;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\TableInterface;
use PeskyORM\ORM\TableStructureInterface;
use Swayok\Utils\NormalizeValue;

/**
 * @method static KeyValueTableInterface|TableInterface getInstance()
 * @method static TableStructureInterface getTableStructure()
 */
trait KeyValueTableHelpers {

    private $_detectedMainForeignKeyColumnName;

    static public function getKeysColumnName() {
        return 'key';
    }

    static public function getValuesColumnName() {
        return 'value';
    }

    /**
     * Override if you wish to provide key manually
     * @return string|null - null returned when there is no foreign key
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getMainForeignKeyColumnName() {
        /** @var KeyValueTableInterface $this */
        if (empty($this->_detectedMainForeignKeyColumnName)) {
            foreach ($this->getTableStructure()->getRelations() as $relationConfig) {
                if ($relationConfig->getType() === Relation::BELONGS_TO) {
                    $this->_detectedMainForeignKeyColumnName = $relationConfig->getLocalColumnName();
                    break;
                }
            }
            if ($this->_detectedMainForeignKeyColumnName === null) {
                throw new \BadMethodCallException(
                    get_called_class() . '::' . __METHOD__ . ' - cannot find foreign key column name'
                );
            }
        }
        return $this->_detectedMainForeignKeyColumnName;
    }

    /**
     * @param null $foreignKeyValue
     * @return null|string
     */
    static public function getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue = null) {
        return null;
    }

    /**
     * @return int - minutes
     */
    static public function getCacheDurationForAllValues() {
        return 10;
    }

    /**
     * Make array that represents DB record and can be saved to DB
     * @param string $key
     * @param mixed $value
     * @param mixed $foreignKeyValue
     * @return array
     */
    static public function makeDataForRecord($key, $value, $foreignKeyValue = null) {
        $record = [
            static::getKeysColumnName() => $key,
            static::getValuesColumnName() => static::encodeValue($value),
        ];
        if ($foreignKeyValue !== null && ($foreignKeyColumn = static::getInstance()->getMainForeignKeyColumnName())) {
            $record[$foreignKeyColumn] = $foreignKeyValue;
        }
        return $record;
    }

    /**
     * @param int|float|string|array $value
     * @return string
     */
    static public function encodeValue($value) {
        return NormalizeValue::normalizeJson($value);
    }

    /**
     * Convert associative array to arrays that represent DB record and are ready for saving to DB
     * @param array $settingsAssoc - associative array of settings
     * @param mixed $foreignKeyValue
     * @param array $additionalConstantValues - contains constant values for all records (for example: admin id)
     * @return array
     */
    static public function convertToDataForRecords(array $settingsAssoc, $foreignKeyValue = null, $additionalConstantValues = []) {
        $records = [];
        foreach ($settingsAssoc as $key => $value) {
            $records[] = array_merge(
                $additionalConstantValues,
                static::makeDataForRecord($key, $value, $foreignKeyValue)
            );
        }
        return $records;
    }

    /**
     * Decode values for passed settings associative array
     * @param array $settingsAssoc
     * @return mixed
     */
    static public function decodeValues(array $settingsAssoc) {
        foreach ($settingsAssoc as $key => &$value) {
            $value = static::decodeValue($value);
        }
        return $settingsAssoc;
    }

    /**
     * @param string $encodedValue
     * @return mixed
     */
    static public function decodeValue($encodedValue) {
        return json_decode($encodedValue, true);
    }

    /**
     * Update: added values decoding
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param array $conditions
     * @param \Closure $configurator
     * @return array
     */
    static public function selectAssoc($keysColumn = null, $valuesColumn = null, array $conditions = [], \Closure $configurator = null) {
        if ($keysColumn === null) {
            $keysColumn = static::getKeysColumnName();
        }
        if ($valuesColumn === null) {
            $valuesColumn = static::getValuesColumnName();
        }
        return static::decodeValues(parent::selectAssoc($keysColumn, $valuesColumn, $conditions, $configurator));
    }

    /**
     * Update existing value or create new one
     * @param array $data - must contain: key, foreign_key, value
     * @return Record
     * @throws \PeskyORM\Exception\RecordNotFoundException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function updateOrCreateRecord(array $data) {
        if (empty($data[static::getKeysColumnName()])) {
            throw new \InvalidArgumentException(
                '$record argument does not contain value for key \'' . static::getKeysColumnName() . '\' or its value is empty'
            );
        } else if (!array_key_exists(static::getValuesColumnName(), $data)) {
            throw new \InvalidArgumentException(
                '$record argument does not contain value for key \'' . static::getValuesColumnName() . '\' or its value is empty'
            );
        }
        $conditions = [
            static::getKeysColumnName() => $data[static::getKeysColumnName()]
        ];
        $fkName = static::getInstance()->getMainForeignKeyColumnName();
        if (!empty($fkName)) {
            if (empty($data[$fkName])) {
                throw new \InvalidArgumentException("\$record argument does not contain value for key '{$fkName}' or its value is empty");
            }
            $conditions[$fkName] = $data[$fkName];
        }
        /** @var Record $object */
        $object = static::getInstance()->newRecord()->fromDb($conditions);
        if ($object->existsInDb()) {
            $success = $object
                ->begin()
                ->updateValues(array_diff_key($data, [static::getKeysColumnName() => '', $fkName => '']), false)
                ->commit();
        } else {
            $object
                ->reset()
                ->updateValues($data, false);
            $success = $object->save();
        }
        static::cleanCachedValues(empty($fkName) ? null : $data[$fkName]);
        return $success;
    }

    /**
     * Update existing values and create new
     * @param array $records
     * @return bool
     */
    static public function updateOrCreateRecords(array $records) {
        $table = static::getInstance();
        $alreadyInTransaction = $table::inTransaction();
        if (!$alreadyInTransaction) {
            $table::beginTransaction();
        }
        try {
            foreach ($records as $record) {
                $success = $table::updateOrCreateRecord($record);
                if (!$success) {
                    if (!$alreadyInTransaction) {
                        $table::rollBackTransaction();
                    }
                    return false;
                }
            }
            if (!$alreadyInTransaction) {
                $table::commitTransaction();
            }
            return true;
        } catch (\Exception $exc) {
            if (!$alreadyInTransaction && $table->inTransaction()) {
                $table::rollBackTransaction();
            }
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw $exc;
        }
    }

    /**
     * @param string $key
     * @param mixed $foreignKeyValue - use null if there is no main foreign key column and
     *      getMainForeignKeyColumnName() method returns null
     * @param mixed $default
     * @return array
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getValue($key, $foreignKeyValue = null, $default = null) {
        $cacheKey = static::getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue);
        if (!empty($cacheKey)) {
            return array_get(self::getValuesForForeignKey($foreignKeyValue), $key, $default);
        }
        $conditions = [
            static::getKeysColumnName() => $key
        ];
        $fkName = static::getInstance()->getMainForeignKeyColumnName();
        if ($fkName !== null) {
            if (empty($foreignKeyValue)) {
                throw new \InvalidArgumentException('$foreignKeyValue argument is required');
            }
            $conditions[$fkName] = $foreignKeyValue;
        } else if (!empty($foreignKeyValue)) {
            throw new \InvalidArgumentException(
                '$foreignKeyValue must be null when model does not have main foreign key column'
            );
        }
        /** @var array $record */
        $record = static::selectOne('*', $conditions);
        if (static::getTableStructure()->hasColumn($key)) {
            // modify value so that it is processed by custom column defined in table structure
            // if $record is empty it uses default value provided by $column prior to $default
            $column = static::getTableStructure()->getColumn($key);
            if (!$column->isItExistsInDb()) {
                $recordObj = static::getInstance()->newRecord();
                if (empty($record)) {
                    return $recordObj->hasValue($column, true) ? $recordObj->getValue($column) : $default;
                } else {
                    return $recordObj
                        ->updateValue($column, static::decodeValue($record[static::getValuesColumnName()]), false)
                        ->getValue($column);
                }
            }
        }
        return empty($record) ? $default : static::decodeValue($record[static::getValuesColumnName()]);

    }

    /**
     * @param mixed $foreignKeyValue
     * @param bool $ignoreCache
     * @return array
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getValuesForForeignKey($foreignKeyValue = null, $ignoreCache = false) {
        if (!$ignoreCache) {
            $cacheKey = static::getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue);
            if (!empty($cacheKey)) {
                $data = \Cache::get($cacheKey, null);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        $conditions = [];
        $table = static::getInstance();
        $fkName = $table->getMainForeignKeyColumnName();
        if ($fkName !== null) {
            if (empty($foreignKeyValue)) {
                throw new \InvalidArgumentException('$foreignKeyValue argument is required');
            }
            $conditions[$fkName] = $foreignKeyValue;
        } else if (!empty($foreignKeyValue)) {
            throw new \InvalidArgumentException(
                '$foreignKeyValue must be null when model does not have main foreign key column'
            );
        }
        $data = static::selectAssoc(static::getKeysColumnName(), static::getValuesColumnName(), $conditions);
        if (!empty($data)) {
            // modify values so that they are processed by custom columns defined in table structure + set defaults
            $columns = $table->getTableStructure()->getColumns();
            $record = $table->newRecord()->updateValues($data, false, false);
            /** @var Column $column */
            foreach ($columns as $columnName => $column) {
                if (!$column->isItExistsInDb()) {
                    $isJson = in_array($column->getType(), [$column::TYPE_JSON, $column::TYPE_JSONB], true);
                    if (array_key_exists($columnName, $data) && $record->hasValue($column)) {
                        // processed value
                        $data[$columnName] = $record->getValue($column, $isJson ? 'array' : null);
                    } else if ($record->hasValue($column, true)) {
                        // default value
                        $data[$columnName] = $record->getValue($column, $isJson ? 'array' : null);
                    }
                }
            }
        }
        if (!empty($cacheKey)) {
            \Cache::put($cacheKey, $data, static::getCacheDurationForAllValues());
        }
        return $data;
    }

    static public function cleanCachedValues($foreignKeyValue = null) {
        $cacheKey = static::getCacheKeyToStoreAllValuesForAForeignKey($foreignKeyValue);
        if ($cacheKey) {
            \Cache::forget($cacheKey);
        }
    }


}