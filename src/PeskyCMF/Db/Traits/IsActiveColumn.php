<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\DbColumnConfig;

trait IsActiveColumn {

    private function is_active() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_BOOL)
            ->setIsNullable(false)
            ->setIsRequired(false)
            ->setDefaultValue(true);
    }
}