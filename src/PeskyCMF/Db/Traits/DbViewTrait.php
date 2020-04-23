<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbObject;

trait DbViewTrait {

    public function save($verifyDbExistance = false, $createIfNotExists = false, $saveRelations = false) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new \BadMethodCallException('Saving data to a DB View is impossible');
    }

    protected function saveToDb(array $columnsToSave = []) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new \BadMethodCallException('Saving data to a DB View is impossible');
    }

    public function delete($resetFields = true, $ignoreIfNotExists = false) {
        /** @var CmfDbObject|DbViewTrait $this */
        throw new \BadMethodCallException('Deleting data from a DB View is impossible');
    }

}