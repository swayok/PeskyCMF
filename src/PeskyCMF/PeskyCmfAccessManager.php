<?php

namespace PeskyCMF;

use Illuminate\Http\Request;
use PeskyORM\ORM\Record;

class PeskyCmfAccessManager extends BaseAccessManager {

    static public function isAuthorised(Request $request) {
        return true;
    }

    static public function getUserRole() {
        return self::getAdmin()->getValue('role');
    }

    /**
     * @return Record
     */
    static public function getAdmin() {
        return \Auth::guard()->user();
    }
}