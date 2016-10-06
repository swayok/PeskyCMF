<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyORM\DbRelationConfig;
use PeskyORM\Exception\DbModelException;
use Swayok\Utils\NormalizeValue;

trait KeyValueModelHelpers {

    private $_detectedMainForeignKeyColumnName;

    /**
     * Override if you wish to provide key manually
     * @return string|null - null returned when there is no foreign key
     * @throws DbModelException
     */
    protected function getMainForeignKeyColumnName() {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        if (empty($this->_detectedMainForeignKeyColumnName)) {
            foreach ($this->getTableConfig()->getRelations() as $relationConfig) {
                if ($relationConfig->getType() === DbRelationConfig::BELONGS_TO) {
                    $this->_detectedMainForeignKeyColumnName = $relationConfig->getColumn();
                    break;
                }
            }
            if (empty($this->_detectedMainForeignKeyColumnName)) {
                throw new DbModelException($this, get_class($this) . '::' . __METHOD__ . ' - cannot find foreign key column name');
            }
        }
        return $this->_detectedMainForeignKeyColumnName;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $foreignKeyValue
     * @return array
     * @throws \PeskyORM\Exception\DbUtilsException
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
        return json_decode($encodedValue, true);
    }

    /**
     * Update: added values decoding
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param null|array $conditionsAndOptions
     * @return array
     */
    public function selectAssoc($keysColumn = 'key', $valuesColumn = 'value', $conditionsAndOptions = null) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        return static::decodeValues(parent::selectAssoc($keysColumn, $valuesColumn, $conditionsAndOptions));
    }

    /**
     * Update existing value or create new one
     * @param array $record - must contain: key, foreign_key, value
     * @return bool
     * @throws \PeskyORM\Exception\DbUtilsException
     * @throws \PeskyORM\Exception\DbObjectFieldException
     * @throws \PeskyORM\Exception\DbObjectValidationException
     * @throws \PeskyORM\Exception\DbObjectException
     * @throws DbModelException
     */
    public function updateOrCreateRecord(array $record) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        if (empty($record['key'])) {
            throw new DbModelException($this, '$record does not contain [key] key or its value is empty');
        } else if (!array_key_exists('value', $record)) {
            throw new DbModelException($this, '$record does not contain [value] key');
        }
        $conditions = [
            'key' => $record['key']
        ];
        $fkName = $this->getMainForeignKeyColumnName();
        if (!empty($fkName)) {
            if (empty($record[$fkName])) {
                throw new DbModelException($this, "\$record does not contain [{$fkName}] key or its value is empty");
            }
            $conditions[$fkName] = $record[$fkName];
        }
        $object = $this->getOwnDbObject()->find($conditions);
        if ($object->exists()) {
            return $object
                ->begin()
                ->_setFieldValue('value', $record['value'])
                ->commit();
        } else {
            return $object
                ->reset()
                ->fromData($record)
                ->save();
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
        $this->begin();
        try {
            foreach ($records as $record) {
                $success = $this->updateOrCreateRecord($record);
                if (!$success) {
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        } catch (\Exception $exc) {
            if ($this->inTransaction()) {
                $this->rollback();
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
     * @throws DbModelException
     */
    public function selectOneByKeyAndForeignKeyValue($key, $foreignKeyValue = null, $default = []) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        $conditions = [
            'key' => $key
        ];
        $fkName = $this->getMainForeignKeyColumnName();
        if ($fkName !== null) {
            if (empty($foreignKeyValue)) {
                throw new DbModelException($this, 'Foreign key value is required');
            }
            $conditions[$fkName] = $foreignKeyValue;
        } else if (!empty($foreignKeyValue)) {
            throw new DbModelException($this, 'Foreign key value provided for model that does not have main foreign key column');
        }
        /** @var array $record */
        $record = $this->selectOne('*', $conditions, false);
        return empty($record) ? $default : static::decodeValue($record['value']);
    }
}