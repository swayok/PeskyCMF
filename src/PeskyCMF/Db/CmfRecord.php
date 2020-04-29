<?php

namespace PeskyCMF\Db;

use PeskyCMF\Db\Traits\CacheForDbSelects;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\Table;

abstract class CmfRecord extends Record {

    /**
     * @return CmfTable|Table|CacheForDbSelects
     */
    public static function getTable() {
        /** @var CmfTable $baseModelClass */
        $baseModelClass = config('peskyorm.base_table_class');
        return $baseModelClass::getModel(class_basename(static::class));
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
                $record->fetchByPrimaryKey($dataOrPkValue);
            }
        }
        return $record;
    }
    
}