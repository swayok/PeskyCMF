<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Routing\Route;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbObject;
use PeskyCMF\HttpCode;

trait InjectsDbObjects {

    public function callAction($method, $parameters) {
        $this->readDbObjectForInjection($parameters);
        return parent::callAction($method, $parameters);
    }

    /**
     * @param $parameters
     */
    protected function readDbObjectForInjection($parameters) {
        /** @var Route $route */
        $route = \Request::route();
        $object = null;
        foreach ($parameters as $key => $value) {
            if ($value instanceof CmfDbObject) {
                // get only last object in params
                $object = $value;
            }
        }
        if (!empty($object)) {
            $id = $route->parameter('id', false);
            if ($id === false) {
                if (\Request::method() === 'GET') {
                    $id = \Request::query('id', false);
                } else {
                    $id = \Request::get('id', false);
                }
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
            if (!$object->exists()) {
                $this->sendRecordNotFoundResponse();
            }
        }
    }

    /**
     * Abort with HTTP code 404
     */
    protected function sendRecordNotFoundResponse() {
        abort(HttpCode::NOT_FOUND, CmfConfig::transBase('.error.db_record_not_exists'));
    }

    /**
     * @param Route $route
     * @param CmfDbObject $object
     * @param array $conditions
     */
    protected function addConditionsForDbObjectInjection(Route $route, CmfDbObject $object, array &$conditions) {

    }

    /**
     * @param Route $route
     * @param CmfDbObject $object
     * @param array $conditions
     */
    protected function addParentIdsConditionsForDbObjectInjection(Route $route, CmfDbObject $object, array &$conditions) {
        foreach ($route->parameterNames() as $name) {
            if ($object->_hasField($name)) {
                $conditions[$name] = $route->parameter($name);
            }
        }
    }
}