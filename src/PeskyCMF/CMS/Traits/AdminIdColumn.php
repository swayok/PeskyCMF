<?php

namespace PeskyCMF\CMS\Traits;

use PeskyCMF\CMS\Admins\CmsAdminsTable;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

trait AdminIdColumn {

    private function admin_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function Admin() {
        return Relation::create('admin_id', Relation::BELONGS_TO, app(CmsAdminsTable::class), 'id')
            ->setDisplayColumnName('email');
    }

}