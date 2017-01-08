<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\KeyValueTableInterface;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Relation;
use Swayok\Utils\NormalizeValue;

/**
 * @method static KeyValueTableInterface getInstance()
 */
trait KeyValueTableHelpers {

    private $_detectedMainForeignKeyColumnName;

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
     * Make array that represents DB record and can be saved to DB
     * @param string $key
     * @param mixed $value
     * @param mixed $foreignKeyValue
     * @return array
     */
    static public function makeDataForRecord($key, $value, $foreignKeyValue = null) {
        $record = [
            'key' => $key,
            'value' => static::encodeValue($value),
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
    static public function selectAssoc($keysColumn = 'key', $valuesColumn = 'value', array $conditions = [], \Closure $configurator = null) {
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
        if (empty($data['key'])) {
            throw new \InvalidArgumentException('$record argument does not contain [key] key or its value is empty');
        } else if (!array_key_exists('value', $data)) {
            throw new \InvalidArgumentException('$record argument does not contain [value] key');
        }
        $conditions = [
            'key' => $data['key']
        ];
        $fkName = static::getInstance()->getMainForeignKeyColumnName();
        if (!empty($fkName)) {
            if (empty($data[$fkName])) {
                throw new \InvalidArgumentException("\$record argument does not contain [{$fkName}] key or its value is empty");
            }
            $conditions[$fkName] = $data[$fkName];
        }
        /** @var Record $object */
        $object = static::getInstance()->newRecord()->fromDb($conditions);
        if ($object->existsInDb()) {
            return $object
                ->begin()
                ->updateValues(array_diff_key($data, ['key' => '', $fkName => '']), false)
                ->commit();
        } else {
            $object
                ->reset()
                ->updateValues($data, false);
            return $object->save();
        }
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
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getValueByKeyAndForeignKeyValue($key, $foreignKeyValue = null, $default = null) {
        $conditions = [
            'key' => $key
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
        return empty($record) ? $default : static::decodeValue($record['value']);
    }
}