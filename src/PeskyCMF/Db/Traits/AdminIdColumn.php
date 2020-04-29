<?php

namespace PeskyCMF\Db\Traits;

use App\Db\Admin\AdminTable;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

trait AdminIdColumn {

    private function admin_id() {
        return Column::create(Column::TYPE_INT)
            ->allowsNullValues()
            ->convertsEmptyStringToNull();
    }

    private function Admin() {
        return Relation::create('admin_id', Relation::BELONGS_TO, AdminTable::class, 'id')
            ->setDisplayColumnName('email');
    }

}