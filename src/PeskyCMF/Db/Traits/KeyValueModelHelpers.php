<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyORM\ORM\Relation;
use Swayok\Utils\NormalizeValue;

trait KeyValueModelHelpers {

    private $_detectedMainForeignKeyColumnName;

    /**
     * Override if you wish to provide key manually
     * @return string|null - null returned when there is no foreign key
     */
    protected function getMainForeignKeyColumnName() {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        if (empty($this->_detectedMainForeignKeyColumnName)) {
            foreach ($this->getTableStructure()->getRelations() as $relationConfig) {
                if ($relationConfig->getType() === Relation::BELONGS_TO) {
                    $this->_detectedMainForeignKeyColumnName = $relationConfig->getColumn();
                    break;
                }
            }
            if (empty($this->_detectedMainForeignKeyColumnName)) {
                throw new \BadMethodCallException(get_class($this) . '::' . __METHOD__ . ' - cannot find foreign key column name');
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
    static public function makeRecord($key, $value, $foreignKeyValue = null) {
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
     * @return array
     */
    static public function makeRecords(array $settingsAssoc, $foreignKeyValue = null) {
        $records = [];
        foreach ($settingsAssoc as $key => $value) {
            $records[] = static::makeRecord($key, $value, $foreignKeyValue);
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
        return $encodedValue === '""' ? '' : json_decode($encodedValue, true);
    }
    
    /**
     * Update: added values decoding
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param null|array $conditions
     * @param \Closure|null $configurator
     * @return array
     */
    static public function selectAssoc(string $keysColumn, string $valuesColumn, array $conditions = [], ?\Closure $configurator = null): array {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        return static::decodeValues(parent::selectAssoc($keysColumn, $valuesColumn, $conditions));
    }

    /**
     * Update existing value or create new one
     * @param array $record - must contain: key, foreign_key, value
     * @return bool
     */
    public function updateOrCreateRecord(array $record) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        if (empty($record['key'])) {
            throw new \InvalidArgumentException('$record does not contain [key] key or its value is empty');
        } else if (!array_key_exists('value', $record)) {
            throw new \InvalidArgumentException('$record does not contain [value] key');
        }
        $conditions = [
            'key' => $record['key']
        ];
        $fkName = $this->getMainForeignKeyColumnName();
        if (!empty($fkName)) {
            if (empty($record[$fkName])) {
                throw new \InvalidArgumentException("\$record does not contain [{$fkName}] key or its value is empty");
            }
            $conditions[$fkName] = $record[$fkName];
        }
        $object = $this->newRecord()->fromDb($conditions);
        if ($object->exists()) {
            return $object
                ->begin()
                ->_setFieldValue('value', $record['value'])
                ->commit();
        } else {
            $object
                ->reset()
                ->_setFieldValue('key', $record['key'])
                ->_setFieldValue('value', $record['value']);
            if (!empty($fkName)) {
                $object->_setFieldValue($fkName, $record[$fkName]);
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
        /** @var CmfDbModel|KeyValueModelHelpers $this */
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
            $this::rollBackTransactionIfExists();
            throw $exc;
        }
    }

    /**
     * @param string $key
     * @param string|null $foreignKeyValue - use null if there is no main foreign key column and
     *      getMainForeignKeyColumnName() method returns null
     * @param mixed $default
     * @return array
     */
    public function selectOneByKeyAndForeignKeyValue($key, $foreignKeyValue = null, $default = []) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
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
            throw new \InvalidArgumentException('$foreignKeyValue argument provided for model that does not have main foreign key column');
        }
        /** @var array $record */
        $record = static::selectOne('*', $conditions);
        return empty($record) ? $default : static::decodeValue($record['value']);
    }
}