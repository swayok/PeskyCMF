<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Routing\Route;
use PeskyCMF\Db\CmfDbRecord;

trait InjectsDbObjectsAndValidatesOwner {

    use InjectsDbObjects;

    protected function addConditionsForDbObjectInjection(Route $route, CmfDbRecord $object, array &$conditions) {
        $conditions[$this->getOwnerIdFieldName($object)] = \Auth::guard()->user()->getAuthIdentifier();
    }

    /**
     * Get owner ID field name. Autodetects 'user_id' and 'admin_id'. In other cases - owerwrite this method
     * @param CmfDbRecord $object
     * @return string
     * @throws \Exception
     */
    protected function getOwnerIdFieldName(CmfDbRecord $object) {
        if ($object::hasColumn('user_id')) {
            return 'user_id';
        } else if ($object::hasColumn('admin_id')) {
            return 'admin_id';
        } else {
            throw new \UnexpectedValueException(
                'InjectsDbObjectsAndValidatesOwner::getOwnerIdFieldName() cannot find owner id field name'
            );
        }
    }

}