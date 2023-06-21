<?php

declare(strict_types=1);

namespace PeskyCMF\Utils;

abstract class SelectOptionsHelper
{
    /**
     * Make options list for <select> input from plain list of values.
     * @param array $values Plain list of values.
     * @param string|\Closure $translationsPath Base path to translations for trans() or translator closure.
     * Closure: function ($value) { return 'translated value'; }
     * @param bool $asValueLabelPair true: ['value' => 'label', ...]; false: ['label' => string, 'value' => string]
     * @return array
     */
    public static function arrayToOptions(
        array $values,
        string|\Closure $translationsPath,
        bool $asValueLabelPair = false
    ): array {
        $options = [];
        if (!($translationsPath instanceof \Closure)) {
            $translator = static function ($value) use ($translationsPath) {
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
