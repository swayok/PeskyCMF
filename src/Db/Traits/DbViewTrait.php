<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;
use PeskyORM\Exception\DbObjectException;

trait DbViewTrait {

    public function save($verifyDbExistance = false, $createIfNotExists = false, $saveRelations = false) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new DbObjectException($this, 'Saving data to a DB View is impossible');
    }

    protected function saveFiles($fieldNames = null) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new DbObjectException($this, 'Saving data to a DB View is impossible');
    }

    public function saveUpdates($fieldNames = null) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new DbObjectException($this, 'Saving data to a DB View is impossible');
    }

    public function delete($resetFields = true, $ignoreIfNotExists = false) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new DbObjectException($this, 'Deleting data from a DB View is impossible');
    }

}