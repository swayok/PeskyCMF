<?php

namespace PeskyCMF\Db;

use PeskyORM\ORM\TableStructure;

abstract class CmfDbTableStructure extends TableStructure {

    protected $writableConnection = 'default';
    protected $readonlyConnection = 'default';

    public function setConnectionsNames(string $writable = 'default', string $readonly = 'default') {
        $this->writableConnection = $writable;
        $this->readonlyConnection = $readonly;
        return $this;
    }
    
    public static function getConnectionName(bool $writable): string {
        return $writable ? static::getInstance()->writableConnection : static::getInstance()->readonlyConnection;
    }
}