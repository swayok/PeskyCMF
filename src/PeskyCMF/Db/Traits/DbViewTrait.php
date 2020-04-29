<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfRecord;

trait DbViewTrait {

    public function save($verifyDbExistance = false, $createIfNotExists = false, $saveRelations = false) {
        /** @var CmfRecord|DbViewTrait $this */
        throw new \BadMethodCallException('Saving data to a DB View is impossible');
    }

    protected function saveToDb(array $columnsToSave = []) {
        /** @var CmfRecord|DbViewTrait $this */
        throw new \BadMethodCallException('Saving data to a DB View is impossible');
    }

    public function delete($resetFields = true, $ignoreIfNotExists = false) {
        /** @var CmfRecord|DbViewTrait $this */
        throw new \BadMethodCallException('Deleting data from a DB View is impossible');
    }

}