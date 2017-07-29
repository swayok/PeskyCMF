<?php

namespace PeskyCMF\Event;

use PeskyORM\ORM\RecordInterface;

class AdminAuthenticated {

    /** @var RecordInterface */
    public $user;

    public function __construct(RecordInterface $user) {
        $this->user = $user;
    }

    public function broadcastOn() {
        return [];
    }
}