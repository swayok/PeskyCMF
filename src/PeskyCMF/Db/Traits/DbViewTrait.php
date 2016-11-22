<?php

namespace PeskyCMF\Db\Traits;

use PeskyCMF\Db\CmfDbRecord;

trait DbViewTrait {

    public function saveToDb(array $columnsToSave = []) {
        /** @var CmfDbRecord|DbViewTrait $this */
        throw new \BadMethodCallException('Saving data to a DB View is impossible');
    }

    public function delete($resetAllValuesAfterDelete = true, $deleteFiles = true) {
        /** @var CmfDbRecord|DbViewTrait $this */
        throw new \BadMethodCallException('Deleting data from a DB View is impossible');
    }

}