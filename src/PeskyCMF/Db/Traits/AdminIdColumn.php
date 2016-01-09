<?php

namespace PeskyCMF\Db\Traits;

use App\Db\Admin\AdminTableConfig;
use PeskyORM\DbColumnConfig;
use PeskyORM\DbRelationConfig;

trait AdminIdColumn {

    private function admin_id() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_INT)
            ->setIsRequired(false)
            ->setIsNullable(true)
            ->setConvertEmptyValueToNull(true);
    }

    private function Admin() {
        return DbRelationConfig::create($this, 'admin_id', DbRelationConfig::BELONGS_TO, AdminTableConfig::TABLE_NAME, 'id')
            ->setDisplayField('email');
    }

}