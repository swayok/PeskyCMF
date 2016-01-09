<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\DbColumnConfig;

trait TimestampColumns {

    private function created_at() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_TIMESTAMP)
            ->setIsRequired(false)
            ->setIsNullable(false)
            ->setIsExcluded(true);
    }

    private function updated_at() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_TIMESTAMP)
            ->setIsRequired(false)
            ->setIsNullable(false)
            ->setIsExcluded(true);
    }
}