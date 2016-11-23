<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Routing\Route;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\HttpCode;

trait InjectsDbObjects {

    public function callAction($method, $parameters) {
        $this->readDbObjectForInjection($parameters);
        return parent::callAction($method, $parameters);
    }

    /**
     * @param $parameters
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\InvalidDataException
     */
    protected function readDbObjectForInjection($parameters) {
        /** @var Route $route */
        $route = \Request::route();
        $object = null;
        foreach ($parameters as $key => $value) {
            if ($value instanceof CmfDbRecord) {
                // get only last object in params
                $object = $value;
            }
        }
        if (!empty($object)) {
            $id = $route->parameter('id', false);
            if ($id === false && \Request::method() !== 'GET') {
                 $id = \Request::get('id', false);
            }
            if (empty($id)) {
                $this->sendRecordNotFoundResponse();
            }
            $conditions = [
                'id' => $id,
            ];
            $this->addConditionsForDbObjectInjection($route, $object, $conditions);
            $this->addParentIdsConditionsForDbObjectInjection($route, $object, $conditions);
            $object->find($conditions);
            if (!$object->existsInDb()) {
                $this->sendRecordNotFoundResponse();
            }
        }
    }

    /**
     * Abort with HTTP code 404
     */
    protected function sendRecordNotFoundResponse() {
        abort(HttpCode::NOT_FOUND, cmfTransGeneral('.error.db_record_not_exists'));
    }

    /**
     * @param Route $route
     * @param CmfDbRecord $object
     * @param array $conditions
     */
    protected function addConditionsForDbObjectInjection(Route $route, CmfDbRecord $object, array &$conditions) {

    }

    /**
     * @param Route $route
     * @param CmfDbRecord $object
     * @param array $conditions
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \UnexpectedValueException
     */
    protected function addParentIdsConditionsForDbObjectInjection(Route $route, CmfDbRecord $object, array &$conditions) {
        foreach ($route->parameterNames() as $name) {
            if ($object::hasColumn($name)) {
                $conditions[$name] = $route->parameter($name);
            }
        }
    }
}