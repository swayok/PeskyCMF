<?php

namespace PeskyCMF\Db;

use Illuminate\Validation\PresenceVerifierInterface;
use PeskyCMF\Config\CmfConfig;
use PeskyORM\Core\DbConnectionsManager;
use PeskyORM\ORM\FakeTable;
use PeskyORM\ORM\TableInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;

class DatabasePresenceVerifier implements PresenceVerifierInterface {

    protected $tables = [];

    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param  string $tableName
     * @param  string $column
     * @param  string $value
     * @param  int $excludeId
     * @param  string $idColumn
     * @param  array $extra
     * @return int
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getCount($tableName, $column, $value, $excludeId = null, $idColumn = null, array $extra = []) {
        $conditions = [$column => $value];
        if ($excludeId !== null && $excludeId !== 'NULL') {
            $conditions[($idColumn ?: 'id') . ' !='] = $excludeId;
        }
        foreach ($extra as $key => $extraValue) {
            $this->addWhere($conditions, $key, $extraValue);
        }
        return $this->getTable($tableName)->count($conditions);
    }

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param  string $tableName
     * @param  string $column
     * @param  array $values
     * @param  array $extra
     * @return int
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function getMultiCount($tableName, $column, array $values, array $extra = []) {
        $conditions = [$column => $values];

        foreach ($extra as $key => $extraValue) {
            $this->addWhere($conditions, $key, $extraValue);
        }

        return $this->getTable($tableName)->count($conditions);
    }

    /**
     * @param $tableName
     * @return TableInterface|CmfDbTable
     * @throws \ReflectionException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    private function getTable($tableName) {
        if (!array_key_exists($tableName, $this->tables)) {
            try {
                $this->tables[$tableName] = CmfConfig::getPrimary()->getTableByUnderscoredName($tableName);
            } catch (ClassNotFoundException $exc) {
                $this->tables[$tableName] = FakeTable::makeNewFakeTable(
                    $tableName,
                    null,
                    DbConnectionsManager::getConnection('default')
                );
            }
        }
        return $this->tables[$tableName];
    }

    /**
     * Add a "where" clause to the given query.
     *
     * @param  array $conditions
     * @param  string $key
     * @param  string $extraValue
     * @return void
     */
    protected function addWhere(&$conditions, $key, $extraValue) {
        if ($extraValue === 'NULL') {
            $conditions[$key] = null;
        } elseif ($extraValue === 'NOT_NULL') {
            $conditions[$key . '!='] = null;
        } else {
            $conditions[$key] = $extraValue;
        }
    }

    public function setConnection($connection) {
        // don't need this but may come
    }

}