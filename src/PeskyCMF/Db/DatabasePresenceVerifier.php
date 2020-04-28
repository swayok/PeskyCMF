<?php

namespace PeskyCMF\Db;

use Illuminate\Validation\PresenceVerifierInterface;
use PeskyORM\ORM\Table;

class DatabasePresenceVerifier implements PresenceVerifierInterface {

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
     */
    public function getCount($tableName, $column, $value, $excludeId = null, $idColumn = null, array $extra = []) {
        $conditions = [$column => $value];
        if ($excludeId !== null && $excludeId !== 'NULL') {
            $conditions[($idColumn ?: 'id') . ' !='] = $excludeId;
        }
        foreach ($extra as $key => $extraValue) {
            $this->addWhere($conditions, $key, $extraValue);
        }
        return $this->getModel($tableName)->count($conditions);
    }

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param  string $tableName
     * @param  string $column
     * @param  array $values
     * @param  array $extra
     * @return int
     */
    public function getMultiCount($tableName, $column, array $values, array $extra = []) {
        $conditions = [$column => $values];

        foreach ($extra as $key => $extraValue) {
            $this->addWhere($conditions, $key, $extraValue);
        }

        return $this->getModel($tableName)->count($conditions);
    }

    /**
     * @param $tableName
     * @return CmfDbModel
     */
    private function getModel($tableName) {
        /** @var CmfDbModel $baseClass */
        $baseClass = app()->make(Table::class);
        return $baseClass::getModelByClassName($baseClass::getFullModelClassByTableName($tableName));
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