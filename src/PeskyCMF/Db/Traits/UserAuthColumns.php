<?php

namespace PeskyCMF\Db\Traits;

use PeskyORM\ORM\Column;
use PeskyORM\ORM\DefaultColumnClosures;

trait UserAuthColumns {

    private function password() {
        return Column::create(Column::TYPE_PASSWORD)
            ->disallowsNullValues()
            ->setValuePreprocessor(function ($value, $isDbValue, Column $column) {
                $value = DefaultColumnClosures::valuePreprocessor($value, $isDbValue, $column);
                if ($isDbValue) {
                    return $value;
                } else {
                    if (!empty($value)) {
                        return \Hash::make($value);
                    }
                    return $value;
                }
            })
            ->privateValue();
    }

    private function remember_token() {
        return Column::create(Column::TYPE_STRING)
            ->allowsNullValues()
            ->convertsEmptyStringToNull()
            ->privateValue();
    }

}