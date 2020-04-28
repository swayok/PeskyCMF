<?php

namespace PeskyCMF\Db;

use PeskyCMF\Db\Traits\CacheForDbSelects;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\RecordsArray;
use PeskyORM\ORM\Relation;
use PeskyORM\ORM\Table;

abstract class CmfDbObject extends Record {

    static protected $_baseModelClass = CmfDbModel::class;
    
    /**
     * @deprecated
     * @return CmfDbModel|CacheForDbSelects
     */
    public function _getModel() {
        return static::getTable();
    }
    
    /**
     * @return CmfDbModel|Table|CacheForDbSelects
     */
    public static function getTable() {
        /** @var CmfDbModel $baseModelClass */
        $baseModelClass = static::$_baseModelClass;
        return $baseModelClass::getModelByObjectClass(static::class);
    }

    /**
     * @param array $values
     * @param bool|false $withLabels
     * @param string|null $translationsPath - null: will use value as label
     * @param bool|false $asValueLabelPair
     * @return array
     */
    static public function toOptions(array $values, $withLabels = false, $translationsPath = '', $asValueLabelPair = false) {
        if (!$withLabels) {
            return $values;
        }
        $options = array();
        foreach ($values as $value) {
            if ($translationsPath === null) {
                $label = $value;
            } else {
                $label = trans($translationsPath . $value);
            }
            if ($asValueLabelPair) {
                $options[$value] = $label;
            } else {
                $options[] = ['label' => $label, 'value' => $value];
            }
        }
        return $options;
    }
    
    /**
     * @param null|array|string|int $dataOrPkValue - null: do nothing | int and string: is primary key (read db) | array: object data
     * @param bool $ignoreUnknownData - used only when $data not empty and is array
     *      true: filters $data that does not belong t0o this object
     *      false: $data that does not belong to this object will trigger exceptions
     * @param bool $isDbValues - true: indicates that field values passsed via $data as array are db values
     * @param null $__deprecated
     * @return $this
     */
    static public function create($dataOrPkValue = null, bool $ignoreUnknownData = false, bool $isDbValues = false, $__deprecated = null) {
        $record = new static();
        
        if (!empty($dataOrPkValue)) {
            if (is_array($dataOrPkValue)) {
                $record->fromData($dataOrPkValue, $isDbValues, !$ignoreUnknownData);
            } else {
                $record->fromPrimaryKey($dataOrPkValue);
            }
        }
        return $record;
    }
    
    /**
     * Read data from DB using Model
     * @param string|array $conditions - conditions to use
     * @param array|string $fieldNames - list of fields to get
     * @param array|null|string $relations - list of relations to read with object
     * @return $this
     */
    static public function search($conditions, $fieldNames = '*', $relations = []) {
        return self::find($conditions, $fieldNames, $relations);
    }
    
    /**
     * @param string $fieldName
     * @param mixed $newValue
     * @param bool $isDbValue
     * @return $this
     * @deprecated
     */
    public function _setFieldValue($fieldName, $newValue, $isDbValue = false) {
        return $this->updateValue($fieldName, $newValue, $isDbValue);
    }
    
    /**
     * @param array|null $fieldNames - will return only this fields (if not skipped)
     *      Note: pk field added automatically if object has it
     * @param array|string|bool $relations - array and string: relations to return | true: all relations | false: without relations
     * @param bool $forceRelationsRead - true: relations will be read before processing | false: only previously read relations will be returned
     * @return array
     * @deprecated
     * Collect available values into associative array (does not validate field values)
     * Used to just get values from object. Also can be overwritten in child classes to add/remove specific values
     */
    public function toPublicArray($fieldNames = null, $relations = false, $forceRelationsRead = true) {
        if ($relations === true) {
            $relations = ['*'];
        }
        if ($fieldNames === null) {
            $fieldNames = [];
        }
        return $this->toArray((array)$fieldNames, $relations ? (array)$relations : [], $forceRelationsRead, true);
    }
    
    /**
     * @param array|null $fieldNames - will return only this fields (if not skipped)
     *      Note: pk field added automatically if object has it
     * @param array|string|bool $relations - array and string: relations to return | true: all relations | false: without relations
     * @param bool $forceRelationsRead - true: relations will be read before processing | false: only previously read relations will be returned
     * @return array
     * @deprecated
     * Collect available values into associative array (does not validate field values)
     * Used to just get values from object. Also can be overwritten in child classes to add/remove specific values
     */
    public function toPublicArrayWithoutFiles($fieldNames = null, $relations = false, $forceRelationsRead = true) {
        if ($relations === true) {
            $relations = ['*'];
        }
        return $this->toArray((array)$fieldNames, $relations ? (array)$relations : [], $forceRelationsRead, false);
    }
    
    public function readRelatedRecord($relationName) {
        $ret = parent::readRelatedRecord($relationName);
        $relation = static::getRelation($relationName);
        if ($relation->getType() === Relation::HAS_MANY && is_array($this->relatedRecords[$relationName])) {
            // todo: remove this after DbModel::select will always return RecordsSet
            $this->relatedRecords[$relationName] = new RecordsArray(
                $relation->getForeignTable(),
                $this->relatedRecords[$relationName],
                true,
                $this->isTrustDbDataMode()
            );
        }
        return $ret;
    }
    
}