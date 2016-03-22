<?php

namespace PeskyCMF;

use App\Db\Admin\Admin;
use Illuminate\Http\Request;

class PeskyCmfAccessManager extends BaseAccessManager {

    static public function isAuthorised(Request $request) {
        return true;
    }

    static public function getUserRole() {
        return self::getAdmin()->role;
    }

    /**
     * @return Admin
     */
    static public function getAdmin() {
        return \Auth::guard()->user();
    }
}