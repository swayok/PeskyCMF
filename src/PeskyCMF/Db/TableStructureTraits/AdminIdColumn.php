<?php

namespace PeskyCMF\Db\TableStructureTraits;

use PeskyCMF\Config\CmfConfig;
use PeskyORM\ORM\Column;
use PeskyORM\ORM\Relation;

trait AdminIdColumn {

    private function admin_id() {
        return Column::create(Column::TYPE_INT)
            ->convertsEmptyStringToNull();
    }

    private function Admin() {
        return Relation::create('admin_id', Relation::BELONGS_TO, CmfConfig::getDefault()->users_table(), 'id')
            ->setDisplayColumnName('email');
    }

}