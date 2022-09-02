<?php

declare(strict_types=1);

namespace PeskyCMF\Db;

use PeskyORM\ORM\Record;

abstract class CmfDbRecord extends Record
{
    
    /**
     * @param array $values
     * @param bool|false $withLabels
     * @param string|\Closure $translationsPath - Closure: function ($value) { return 'translated value'; }
     * @param bool $asValueLabelPair
     * @return array
     */
    public static function toOptions(
        array $values,
        bool $withLabels = false,
        string|\Closure $translationsPath = '',
        bool $asValueLabelPair = false
    ): array {
        if (!$withLabels) {
            return $values;
        }
        $options = [];
        if (!($translationsPath instanceof \Closure)) {
            $translator = function ($value) use ($translationsPath) {
                return trans($translationsPath . $value);
            };
        } else {
            $translator = $translationsPath;
        }
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