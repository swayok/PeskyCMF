<?php

namespace PeskyCMF\Db\Traits;

use PeskyORM\ORM\Column;

trait IdColumn {

    private function id() {
        return Column::create(Column::TYPE_INT)
            ->primaryKey()
            ->disallowsNullValues()
            ->convertsEmptyStringToNull();
    }
}