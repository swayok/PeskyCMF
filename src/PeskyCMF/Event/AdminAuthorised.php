<?php

namespace PeskyCMF\Event;

use App\Events\Event;
use PeskyORM\DbObject;

class AdminAuthorised extends Event {

    public $user;

    public function __construct(DbObject $user) {
        $this->user = $user;
    }

    public function broadcastOn() {
        return [];
    }
}