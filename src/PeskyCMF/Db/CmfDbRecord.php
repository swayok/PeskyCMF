<?php

namespace PeskyCMF\Db;

use PeskyORM\ORM\Record;

abstract class CmfDbRecord extends Record {

    /**
     * @param array $values
     * @param bool|false $withLabels
     * @param string $translationsPath
     * @param bool|false $asValueLabelPair
     * @return array
     */
    static public function toOptions(array $values, $withLabels = false, $translationsPath = '', $asValueLabelPair = false) {
        if (!$withLabels) {
            return $values;
        }
        $options = array();
        foreach ($values as $value) {
            $label = trans($translationsPath . $value);
            if ($asValueLabelPair) {
                $options[$value] = $label;
            } else {
                $options[] = ['label' => $label, 'value' => $value];
            }
        }
        return $options;
    }

}