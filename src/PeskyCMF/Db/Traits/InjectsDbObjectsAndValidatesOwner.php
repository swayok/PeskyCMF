<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Routing\Route;
use PeskyCMF\Db\CmfDbObject;

trait InjectsDbObjectsAndValidatesOwner {

    use InjectsDbObjects;

    protected function addConditionsForDbObjectInjection(Route $route, CmfDbObject $object, array &$conditions) {
        $conditions[$this->getOwnerIdFieldName($object)] = \Auth::guard()->user()->getAuthIdentifier();
    }

    /**
     * Get owner ID field name. Autodetects 'user_id' and 'admin_id'. In other cases - owerwrite this method
     * @param CmfDbObject $object
     * @return string
     * @throws \Exception
     */
    protected function getOwnerIdFieldName(CmfDbObject $object) {
        if ($object->_hasField('user_id')) {
            return 'user_id';
        } else if ($object->_hasField('admin_id')) {
            return 'admin_id';
        } else {
            throw new \Exception('InjectsDbObjectsAndValidatesOwner::getOwnerIdFieldName() cannot find owner id field name');
        }
    }

}