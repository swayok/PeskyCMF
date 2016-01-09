<?php

namespace PeskyCMF\Db\Traits;
use PeskyORM\DbColumnConfig\PkColumnConfig;

trait IdColumn {

    private function id() {
        return PkColumnConfig::create();
    }
}