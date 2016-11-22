<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\ORM\Column;

trait TimestampColumns {

    private function created_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->setIsNullable(false)
            ->valueCannotBeSetOrChanged();
    }

    private function updated_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->setIsNullable(false)
            ->valueCannotBeSetOrChanged();
    }
}