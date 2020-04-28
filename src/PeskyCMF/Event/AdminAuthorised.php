<?php

namespace PeskyCMF\Event;

use App\Events\Event;
use PeskyCMF\Db\CmfDbObject;

class AdminAuthorised extends Event {

    public $user;

    public function __construct(CmfDbObject $user) {
        $this->user = $user;
    }

    public function broadcastOn() {
        return [];
    }
}