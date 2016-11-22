<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\ORM\Column;

trait TimestampColumns {

    private function created_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->disallowsNullValues()
            ->valueCannotBeSetOrChanged();
    }

    private function updated_at() {
        return Column::create(Column::TYPE_TIMESTAMP)
            ->disallowsNullValues()
            ->valueCannotBeSetOrChanged();
    }
}