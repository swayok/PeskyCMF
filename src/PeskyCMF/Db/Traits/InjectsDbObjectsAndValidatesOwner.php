<?php

namespace PeskyCMF\Db\Traits;

use Illuminate\Routing\Route;
use PeskyCMF\Db\CmfRecord;

trait InjectsDbObjectsAndValidatesOwner {

    use InjectsDbObjects;

    protected function addConditionsForDbObjectInjection(Route $route, CmfRecord $object, array &$conditions) {
        $conditions[$this->getOwnerIdFieldName($object)] = \Auth::guard()->user()->getAuthIdentifier();
    }

    /**
     * Get owner ID field name. Autodetects 'user_id' and 'admin_id'. In other cases - owerwrite this method
     * @param CmfRecord $object
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function getOwnerIdFieldName(CmfRecord $object) {
        if ($object::hasColumn('user_id')) {
            return 'user_id';
        } else if ($object::hasColumn('admin_id')) {
            return 'admin_id';
        } else {
            throw new \UnexpectedValueException('InjectsDbObjectsAndValidatesOwner::getOwnerIdFieldName() cannot find owner id field name');
        }
    }

}