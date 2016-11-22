<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\ORM\Column;

trait IsActiveColumn {

    private function is_active() {
        return Column::create(Column::TYPE_BOOL)
            ->setIsNullable(false)
            ->setDefaultValue(true);
    }
}