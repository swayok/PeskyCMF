<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbTable;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Relation;
use Swayok\Utils\NormalizeValue;

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
        /** @var CmfDbTable|KeyValueTableHelpers $this */
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
     * @param string $key
     * @param mixed $value
     * @param null $foreignKeyValue
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
     * @param array $settingsAssoc - associative array of settings
     * @param null $foreignKeyValue
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
    public function selectAssoc($keysColumn = 'key', $valuesColumn = 'value', array $conditions = [], \Closure $configurator = null) {
        /** @var CmfDbTable|KeyValueTableHelpers $this */
        return static::decodeValues(parent::selectAssoc($keysColumn, $valuesColumn, $conditions, $configurator));
    }

    /**
     * Update existing value or create new one
     * @param array $record - must contain: key, foreign_key, value
     * @return Record
     * @throws \PeskyORM\Exception\RecordNotFoundException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidTableColumnConfigException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PeskyORM\Exception\DbException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function updateOrCreateRecord(array $record) {
        /** @var CmfDbTable|KeyValueTableHelpers $this */
        if (empty($record['key'])) {
            throw new \InvalidArgumentException('$record argument does not contain [key] key or its value is empty');
        } else if (!array_key_exists('value', $record)) {
            throw new \InvalidArgumentException('$record argument does not contain [value] key');
        }
        $conditions = [
            'key' => $record['key']
        ];
        $fkName = $this->getMainForeignKeyColumnName();
        if (!empty($fkName)) {
            if (empty($record[$fkName])) {
                throw new \InvalidArgumentException("\$record argument does not contain [{$fkName}] key or its value is empty");
            }
            $conditions[$fkName] = $record[$fkName];
        }
        $object = $this->newRecord()->fromDb($conditions);
        if ($object->existsInDb()) {
            return $object
                ->begin()
                ->updateValue('value', $record['value'], false)
                ->commit();
        } else {
            $object
                ->reset()
                ->updateValue('key', $record['key'], false)
                ->updateValue('value', $record['value'], false);
            if (!empty($fkName)) {
                $object->updateValue($fkName, $record[$fkName], false);
            }
            return $object->save();
        }
    }

    /**
     * Update existing values and create new
     * @param array $records
     * @return bool
     * @throws \Exception
     */
    public function updateOrCreateRecords(array $records) {
        /** @var CmfDbTable|KeyValueTableHelpers $this */
        $this::beginTransaction();
        try {
            foreach ($records as $record) {
                $success = $this->updateOrCreateRecord($record);
                if (!$success) {
                    $this::rollBackTransaction();
                    return false;
                }
            }
            $this::commitTransaction();
            return true;
        } catch (\Exception $exc) {
            if ($this->inTransaction()) {
                $this::rollBackTransaction();
            }
            throw $exc;
        }
    }

    /**
     * @param string $key
     * @param string|null $foreignKeyValue - use null if there is no main foreign key column and
     *      getMainForeignKeyColumnName() method returns null
     * @param mixed $default
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function selectOneByKeyAndForeignKeyValue($key, $foreignKeyValue = null, $default = []) {
        /** @var CmfDbTable|KeyValueTableHelpers $this */
        $conditions = [
            'key' => $key
        ];
        $fkName = $this->getMainForeignKeyColumnName();
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
        $record = $this::selectOne('*', $conditions);
        return empty($record) ? $default : static::decodeValue($record['value']);
    }
}