<?php


namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbModel;
use PeskyORM\DbRelationConfig;
use PeskyORM\Exception\DbModelException;

trait KeyValueModelHelpers {

    private $_detectedMainForeignKeyColumnName;

    /**
     * Override if you wish to provide key manually
     * @return string
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
     */
    static public function makeRecord($key, $value, $foreignKeyValue = null) {
        $record = [
            'key' => $key,
            'value' => is_numeric($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        ];
        if ($foreignKeyValue !== null && ($foreignKeyColumn = self::getInstance()->getMainForeignKeyColumnName())) {
            $record[$foreignKeyColumn] = $foreignKeyValue;
        }
        return $record;
    }

    /**
     * @param array $settingsAssoc - associative array of settings
     * @param null $foreignKeyValue
     * @return array
     */
    static public function makeRecords(array $settingsAssoc, $foreignKeyValue = null) {
        $records = [];
        foreach ($settingsAssoc as $key => $value) {
            $records[] = self::makeRecord($key, $value, $foreignKeyValue);
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
            $value = json_decode($value, true);
        }
        return $settingsAssoc;
    }

    /**
     * Update: added values decoding
     * @param string $keysColumn
     * @param string $valuesColumn
     * @param null|array $conditionsAndOptions
     * @return array
     */
    public function selectAssoc($keysColumn, $valuesColumn, $conditionsAndOptions = null) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        return self::decodeValues(parent::selectAssoc($keysColumn, $valuesColumn, $conditionsAndOptions));
    }

    /**
     * Update existing value or create ne one
     * @param array $record - must contain: key, foreign_key, value
     * @return bool
     * @throws DbModelException
     */
    public function updateOrCreateRecord(array $record) {
        /** @var CmfDbModel|KeyValueModelHelpers $this */
        $fkName = $this->getMainForeignKeyColumnName();
        if (empty($record[$fkName])) {
            throw new DbModelException($this, "\$record does not contain [{$fkName}] key or its value is empty");
        } else if (empty($record['key'])) {
            throw new DbModelException($this, '$record does not contain [key] key or its value is empty');
        } else if (!array_key_exists('value', $record)) {
            throw new DbModelException($this, '$record does not contain [value] key');
        }
        $object = $this->getOwnDbObject()->find([
            $fkName => $record[$fkName],
            'key' => $record['key']
        ]);
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
}