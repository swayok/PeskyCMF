<?php

namespace PeskyCMF\Event;

use App\Events\Event;
use PeskyCMF\Db\CmfRecord;

class AdminAuthorised extends Event {

    public $user;

    public function __construct(CmfRecord $user) {
        $this->user = $user;
    }

    public function broadcastOn() {
        return [];
    }
}