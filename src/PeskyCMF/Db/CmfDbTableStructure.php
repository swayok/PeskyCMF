<?php

namespace PeskyCMF\Db;

use PeskyORM\ORM\TableStructure;

abstract class CmfDbTableStructure extends TableStructure {

    protected $writableConnection = 'default';
    protected $readonlyConnection = 'default';

    public function setConnectionsNames($writable = 'default', $readonly = 'default') {
        $this->writableConnection = $writable;
        $this->readonlyConnection = $readonly;
        return $this;
    }

    static public function getConnectionName($writable) {
        return $writable ? static::getInstance()->writableConnection : static::getInstance()->readonlyConnection;
    }
}