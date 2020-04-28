<?php

namespace PeskyCMF\Db\Traits;

use App\Db\Admin\AdminModel;
use App\Db\DbColumnConfig;
use PeskyORM\ORM\Relation;

trait AdminIdColumn {

    private function admin_id() {
        return DbColumnConfig::create(DbColumnConfig::TYPE_INT)
            ->setIsRequired(false)
            ->setIsNullable(true)
            ->setConvertEmptyValueToNull(true);
    }

    private function Admin() {
        return Relation::create('admin_id', Relation::BELONGS_TO, AdminModel::class, 'id')
            ->setDisplayColumnName('email');
    }

}