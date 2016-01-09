<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbColumnConfig\PasswordColumnConfig;

trait UserAuthColumns {

    private function password() {
        return PasswordColumnConfig::create()
            ->setIsNullable(false)
            ->setIsRequired(true)
            ->setIsPrivate(true)
            ->setHashFunction(function ($password) {
                return \Hash::make($password);
            });
    }

    private function remember_token() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_STRING)
            ->setIsRequired(false)
            ->setIsNullable(true)
            ->setConvertEmptyValueToNull(true)
            ->setDefaultValue(null)
            ->setIsPrivate(true);
    }

}