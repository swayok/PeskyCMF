<?php

namespace PeskyCMF\Db\Traits;

use App\Db\Admin\AdminModel;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

trait AdminIdColumn {

    private function admin_id() {
        return Column::create(Column::TYPE_INT)
            ->allowsNullValues()
            ->convertsEmptyStringToNull();
    }

    private function Admin() {
        return Relation::create('admin_id', Relation::BELONGS_TO, AdminModel::class, 'id')
            ->setDisplayColumnName('email');
    }

}