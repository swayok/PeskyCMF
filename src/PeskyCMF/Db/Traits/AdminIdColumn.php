<?php

namespace PeskyCMF\Db\Traits;

use App\Db\Admin\AdminModel;
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
        return DbRelationConfig::create('admin_id', DbRelationConfig::BELONGS_TO, AdminModel::class, 'id')
            ->setDisplayField('email');
    }

}