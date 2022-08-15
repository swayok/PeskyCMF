<?php

namespace PeskyCMF\Db;

use PeskyORM\ORM\Record;

abstract class CmfDbRecord extends Record {

    /**
     * @param array $values
     * @param bool|false $withLabels
     * @param string|\Closure $translationsPath - Closure: function ($value) { return 'translated value'; }
     * @param bool|false $asValueLabelPair
     * @return array
     */
    public static function toOptions(array $values, $withLabels = false, $translationsPath = '', $asValueLabelPair = false) {
        if (!$withLabels) {
            return $values;
        }
        $options = [];
        $translator = $translationsPath instanceof \Closure ? $translationsPath : function ($value) use ($translationsPath) {
            return trans($translationsPath . $value);
        };
        foreach ($values as $value) {
            $label = $translator($value);
            if ($asValueLabelPair) {
                $options[$value] = $label;
            } else {
                $options[] = ['label' => $label, 'value' => $value];
            }
        }
        return $options;
    }

}