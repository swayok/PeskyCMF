<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;
use PeskyORM\ORM\RecordValue;

trait PasswordColumn {

    private function password() {
        return Column::create(Column::TYPE_PASSWORD)
            ->disallowsNullValues()
            ->convertsEmptyStringToNull()
            ->setValuePreprocessor(function ($value, $isDbValue, Column $column) {
                $value = DefaultColumnClosures::valuePreprocessor($value, $isDbValue, $column);
                if ($isDbValue) {
                    return $value;
                } else {
                    if (!empty($value)) {
                        return static::hashPassword($value);
                    }
                    return $value;
                }
            })
            ->setValueSetter(function ($newValue, $isFromDb, RecordValue $valueContainer, $trustDataReceivedFromDb) {
                if (!$isFromDb && ($newValue === null || (is_string($newValue) && trim($newValue) === ''))) {
                    return;
                }
                DefaultColumnClosures::valueSetter($newValue, $isFromDb, $valueContainer, $trustDataReceivedFromDb);
            })
            ->privateValue();
    }

    /**
     * @param string $plainPassword
     * @return string
     */
    static protected function hashPassword($plainPassword) {
        return \Hash::make($plainPassword);
    }

}